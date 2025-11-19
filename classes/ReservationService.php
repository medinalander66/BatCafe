<?php

declare(strict_types=1);

class ReservationService
{
    private PDO $pdo;
    private EquipmentRepository $equipmentRepo;

    public function __construct(PDO $pdo, EquipmentRepository $equipmentRepo)
    {
        $this->pdo = $pdo;
        $this->equipmentRepo = $equipmentRepo;
    }

    /**
     * Fetch room_type row by ID
     */
    private function getRoomType(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM room_types WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            throw new Exception("Invalid room type ID: {$id}");
        }

        return $room;
    }


    /**
     * Create a reservation
     */
    public function calculateEstimate(array $payload): array
    {
        $hours = (float)($payload['hours'] ?? 1);
        $persons = (int)($payload['persons'] ?? 1);
        $room_type_id = (int)($payload['room_type_id'] ?? 0);
        $equipment_codes = $payload['equipment_codes'] ?? [];

        $room = $this->getRoomType($room_type_id);

        $rate_per_hour   = (float)$room['rate_per_hour'];
        $rate_per_person = (float)$room['rate_per_person'];
        $minimum_fee     = (float)$room['minimum_fee'];

        // Calculate fees
        $hourly_fee = $rate_per_hour * $hours;
        $person_fee = $rate_per_person * max(1, $persons);

        $equipment_fee = 0;
        foreach ($equipment_codes as $code) {
            $equip = $this->equipmentRepo->findByCode($code);
            if ($equip) {
                $equipment_fee += ((float)$equip['rate_per_hour']) * $hours;
            }
        }

        $raw_total = $hourly_fee + $person_fee + $equipment_fee;
        $total_fee = max($raw_total, $minimum_fee);

        return [
            'hourly_fee' => $hourly_fee,
            'person_fee' => $person_fee,
            'equipment_fee' => $equipment_fee,
            'minimum_fee' => $minimum_fee,
            'total_fee' => $total_fee
        ];
    }

    public function createReservation(array $input): array
    {
        $errors = [];

        $room_type_id = (int)($input['room_type_id'] ?? 0);
        if ($room_type_id <= 0) $errors['room_type_id'] = 'Invalid room type.';

        if (empty($input['name'])) $errors['name'] = 'Name required';
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required';
        if (empty($input['phone'])) $errors['phone'] = 'Phone required';
        if (empty($input['start_time'])) $errors['start_time'] = 'Start time required';
        if (empty($input['hours']) || (float)$input['hours'] <= 0) $errors['hours'] = 'Duration must be > 0';
        if (empty($input['persons']) || (int)$input['persons'] <= 0) $errors['persons'] = 'Persons must be > 0';

        $room = $this->getRoomType($room_type_id);
        if ($room['code'] === 'STUDY' && empty($input['student_id'])) {
            $errors['student_id'] = 'Student ID required for Study room type.';
        }
        if (!empty($errors)) throw new ValidationException($errors);

        $reservation_date = sprintf('%04d-%02d-%02d', (int)$input['year'], (int)$input['month'], (int)$input['day']);
        $estimate = $this->calculateEstimate([
            'hours' => $input['hours'],
            'persons' => $input['persons'],
            'room_type_id' => $room_type_id,
            'equipment_codes' => (array)($input['equipment'] ?? [])
        ]);

        try {
            $this->pdo->beginTransaction();

            // Find or create user
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email=:email LIMIT 1");
            $stmt->execute(['email' => $input['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $user_id = (int)$user['id'];
                $this->pdo->prepare("UPDATE users SET full_name=:name, phone=:phone, student_id=:sid WHERE id=:id")
                    ->execute(['name' => $input['name'], 'phone' => $input['phone'], 'sid' => $input['student_id'], 'id' => $user_id]);
            } else {
                $this->pdo->prepare("INSERT INTO users (full_name, student_id, email, phone) VALUES (:name,:sid,:email,:phone)")
                    ->execute(['name' => $input['name'], 'sid' => $input['student_id'], 'email' => $input['email'], 'phone' => $input['phone']]);
                $user_id = (int)$this->pdo->lastInsertId();
            }

            // Insert reservation
            $hours_sec = (float)$input['hours'] * 3600;
            $insRes = $this->pdo->prepare(
                "INSERT INTO reservations 
        (user_id, room_type_id, reservation_date, start_time, hours, end_time, persons, student_id)
        VALUES (:user_id, :room_type_id, :reservation_date, :start_time, :hours, ADDTIME(:start_time, SEC_TO_TIME(:hours_sec)), :persons, :student_id)"
            );
            $insRes->execute([
                'user_id' => $user_id,
                'room_type_id' => $room_type_id,
                'reservation_date' => $reservation_date,
                'start_time' => $input['start_time'],
                'hours' => $input['hours'],
                'hours_sec' => $hours_sec,
                'persons' => $input['persons'],
                'student_id' => $input['student_id']
            ]);
            $reservation_id = (int)$this->pdo->lastInsertId();

            // Insert fees into reservation_fees
            $this->pdo->prepare(
                "INSERT INTO reservation_fees (reservation_id, hourly_fee, person_fee, equipment_fee, minimum_fee, total_fee)
         VALUES (:reservation_id, :hourly_fee, :person_fee, :equipment_fee, :minimum_fee, :total_fee)"
            )->execute([
                'reservation_id' => $reservation_id,
                'hourly_fee' => $estimate['hourly_fee'],
                'person_fee' => $estimate['person_fee'],
                'equipment_fee' => $estimate['equipment_fee'],
                'minimum_fee' => $estimate['minimum_fee'],
                'total_fee' => $estimate['total_fee']
            ]);

            // Insert reservation equipment
            foreach ((array)($input['equipment'] ?? []) as $code) {
                $equip = $this->equipmentRepo->findByCode($code);
                if (!$equip) continue;
                $this->pdo->prepare(
                    "INSERT INTO reservation_equipment (reservation_id, equipment_id, quantity, equipment_rate_per_hour)
             VALUES (:rid,:eid,:qty,:rate)"
                )->execute([
                    'rid' => $reservation_id,
                    'eid' => $equip['id'],
                    'qty' => 1,
                    'rate' => $equip['rate_per_hour']
                ]);
            }

            $this->pdo->commit();

            return [
                'reservation_id' => $reservation_id,
                'estimated_total' => $estimate['total_fee'],
                'status' => 'PENDING'
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }







    /**
     * Helper: check for approved reservation conflicts for a date/time window.
     * Returns true if conflict exists (i.e., an APPROVED reservation overlaps).
     */
    public function hasApprovedConflict(string $reservation_date, string $start_time, string $end_time): bool
    {
        $sql = "SELECT id FROM reservations
                WHERE reservation_date = :reservation_date
                  AND status = 'APPROVED'
                  AND (start_time < :end_time)
                  AND (ADDTIME(start_time, SEC_TO_TIME(ROUND(hours * 3600))) > :start_time)
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'reservation_date' => $reservation_date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a new reservation can be approved
     * 
     * Rules:
     * - Only one room type per time slot (different types block each other)
     * - Same room type allowed multiple reservations if total persons <= room capacity
     *
     * @param string $reservation_date
     * @param string $start_time
     * @param float  $hours
     * @param string $room_type
     * @param int    $persons
     * @param int    $room_capacity
     * @return void
     * @throws RuntimeException if reservation cannot be approved
     */
    private function assertReservationCanBeApproved(
        string $reservation_date,
        string $start_time,
        float $hours,
        string $room_type,
        int $persons,
        int $room_capacity = 20
    ): void {
        //  Check for different room type conflict
        $sql = "SELECT id
            FROM reservations
            WHERE reservation_date = :reservation_date
              AND start_time = :start_time
              AND status = 'APPROVED'
              AND type != :type
            LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'reservation_date' => $reservation_date,
            'start_time' => $start_time,
            'type' => $room_type
        ]);
        if ($stmt->fetch()) {
            throw new RuntimeException("Cannot approve: room already booked for another type at this time.");
        }

        //  Check total persons for same room type
        $sql = "SELECT SUM(persons) as total_persons
            FROM reservations
            WHERE reservation_date = :reservation_date
              AND start_time = :start_time
              AND status = 'APPROVED'
              AND type = :type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'reservation_date' => $reservation_date,
            'start_time' => $start_time,
            'type' => $room_type
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPersons = (int)($row['total_persons'] ?? 0);

        if (($totalPersons + $persons) > $room_capacity) {
            throw new RuntimeException("Cannot approve: room capacity exceeded for this time slot.");
        }
    }
}
