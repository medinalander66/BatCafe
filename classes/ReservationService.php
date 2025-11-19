<?php
// src/ReservationService.php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
class ReservationService
{
    private PDO $pdo;
    private stdClass $config;
    private EquipmentRepository $equipmentRepo;

    public function __construct(PDO $pdo, stdClass $config, EquipmentRepository $equipmentRepo)
    {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->equipmentRepo = $equipmentRepo;
    }

    /**
     * Calculate estimate using current equipment rates.
     * @param array $data keys: hours (float), equipment_codes (array of codes)
     * @return array
     */
    public function calculateEstimate(array $data): array
    {
        $hours = max(0.0, (float) ($data['hours'] ?? 0.0));
        $equipment_codes = (array) ($data['equipment_codes'] ?? []);

        $base_rate = (float) $this->config->rates->base_hourly_rate;
        $minimum_fee = (float) $this->config->rates->minimum_fee;

        $room_fee = $hours * $base_rate;

        $equipment_fee = 0.0;
        foreach ($equipment_codes as $code) {
            $code = (string) $code;
            $equip = $this->equipmentRepo->findByCode($code);
            if ($equip) {
                $equipment_fee += ((float)$equip['rate_per_hour'] * $hours);
            } else {
                if (isset($this->config->rates->equipment[$code])) {
                    $equipment_fee += ((float)$this->config->rates->equipment[$code] * $hours);
                }
            }
        }

        $raw_total = $room_fee + $equipment_fee;
        $total = ($hours < 2.0) ? max($minimum_fee, $raw_total) : $raw_total;

        return [
            'hours' => round($hours, 2),
            'room_fee' => round($room_fee, 2),
            'equipment_fee' => round($equipment_fee, 2),
            'raw_total' => round($raw_total, 2),
            'total' => round($total, 2),
            'currency' => 'PHP'
        ];
    }

    /**
     * Create reservation (PENDING status). Returns reservation id + snapshot amounts.
     * Does NOT check for conflicts — admin approval will perform that check.
     *
     * Expected $input keys:
     * type, persons, start_time, year, month, day, hours, name, student_id, email, phone, equipment (array)
     *
     * @throws ValidationException
     * @return array
     */
    public function createReservation(array $input): array
    {
        // server-side validation
        $errors = [];
        $input['type'] = strtoupper(trim((string) ($input['type'] ?? '')));
        if (!in_array($input['type'], ['STUDY', 'GATHERING', 'EVENT'], true)) {
            $errors['type'] = 'Invalid room type.';
        }
        if (empty($input['name'])) $errors['name'] = 'Name is required';
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
        if (empty($input['phone'])) $errors['phone'] = 'Phone is required';
        if (empty($input['start_time'])) $errors['start_time'] = 'Start time is required';
        if (empty($input['year']) || empty($input['month']) || empty($input['day'])) $errors['reservation_date'] = 'Complete reservation date is required';
        if (empty($input['hours']) || (float)$input['hours'] <= 0) $errors['hours'] = 'Duration must be > 0';
        if (empty($input['persons']) || (int)$input['persons'] <= 0) $errors['persons'] = 'Persons must be > 0';
        if ($input['type'] === 'STUDY' && empty($input['student_id'])) $errors['student_id'] = 'Student ID required for Study room type';

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // build reservation_date
        $year = (int)$input['year'];
        $month = (int)$input['month'];
        $day = (int)$input['day'];
        $reservation_date = sprintf('%04d-%02d-%02d', $year, $month, $day);

        // compute estimated fees (snapshot)
        $estimate = $this->calculateEstimate([
            'hours' => (float)$input['hours'],
            'equipment_codes' => (array)$input['equipment']
        ]);

        // prepare DB transaction: insert or reuse user, insert reservation, reservation_equipment, reservation_actions
        try {
            $this->pdo->beginTransaction();

            // find or create user by email
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $input['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $user_id = (int)$user['id'];
                $up = $this->pdo->prepare("UPDATE users SET full_name = :name, phone = :phone, student_id = :sid WHERE id = :id");
                $up->execute(['name' => $input['name'], 'phone' => $input['phone'], 'sid' => $input['student_id'], 'id' => $user_id]);
            } else {
                $ins = $this->pdo->prepare("INSERT INTO users (full_name, student_id, email, phone) VALUES (:name, :sid, :email, :phone)");
                $ins->execute(['name' => $input['name'], 'sid' => $input['student_id'], 'email' => $input['email'], 'phone' => $input['phone']]);
                $user_id = (int)$this->pdo->lastInsertId();
            }

            // insert reservation (status -> PENDING)
            $base_rate = (float) $this->config->rates->base_hourly_rate;
            $minimum_fee = (float) $this->config->rates->minimum_fee;

            // Validate start_time server-side
            if (!empty($input['start_time'])) {
                [$hours, $minutes] = explode(':', $input['start_time']);
                $hours = (int)$hours;
                $minutes = (int)$minutes;

                // Convert to minutes
                $totalMinutes = $hours * 60 + $minutes;

                // Allowed range: 13:00 (1 PM) → 01:00 (next day)
                $startAllowed = 13 * 60;   // 13:00
                $endAllowed   = 25 * 60;   // 01:00 next day = 25:00 in minutes

                // Add 24h if before 13:00 so that 00:00–01:00 is considered next day
                if ($totalMinutes < $startAllowed) {
                    $totalMinutes += 24 * 60;
                }

                if ($totalMinutes < $startAllowed || $totalMinutes > $endAllowed) {
                    $errors['start_time'] = 'Start time must be between 1:00 PM and 1:00 AM.';
                }
            } else {
                $errors['start_time'] = 'Start time is required';
            }


            // Throw validation exception if any errors
            if (!empty($errors)) throw new ValidationException($errors);

            
            $insRes = $this->pdo->prepare(
                "INSERT INTO reservations
                  (user_id, room_type, reservation_date, start_time, hours, persons, student_id,
                   contact_email, contact_phone, base_hourly_rate, minimum_fee,
                   estimated_equipment_fee, estimated_total_fee, status, created_at, updated_at)
                 VALUES
                  (:user_id, :room_type, :reservation_date, :start_time, :hours, :persons, :student_id,
                   :email, :phone, :base_rate, :minimum_fee,
                   :est_eq_fee, :est_total, 'PENDING', NOW(), NOW())"
            );
            
            $insRes->execute([
                'user_id' => $user_id,
                'room_type' => $input['type'],
                'reservation_date' => $reservation_date,
                'start_time' => $input['start_time'],
                'hours' => $input['hours'],
                'persons' => $input['persons'],
                'student_id' => $input['student_id'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'base_rate' => $base_rate,
                'minimum_fee' => $minimum_fee,
                'est_eq_fee' => $estimate['equipment_fee'],
                'est_total' => $estimate['total'],
            ]);

            $reservation_id = (int)$this->pdo->lastInsertId();

            // insert reservation_equipment snapshot rows
            foreach ((array)$input['equipment'] as $code) {
                $equip = $this->equipmentRepo->findByCode($code);
                if (!$equip) {
                    if (!isset($this->config->rates->equipment[$code])) continue;
                    // in case not in DB skip (or you could insert to master)
                    continue;
                }
                $equipment_id = (int)$equip['id'];
                $rate = (float)$equip['rate_per_hour'];
                $quantity = 1;

                $insEq = $this->pdo->prepare("INSERT INTO reservation_equipment (reservation_id, equipment_id, quantity, equipment_rate_per_hour) VALUES (:rid, :eid, :qty, :rate)");
                $insEq->execute(['rid' => $reservation_id, 'eid' => $equipment_id, 'qty' => $quantity, 'rate' => $rate]);
            }

            // audit action
            $insAct = $this->pdo->prepare("INSERT INTO reservation_actions (reservation_id, admin_id, action_type, action_reason, created_at) VALUES (:rid, NULL, 'SUBMITTED', :reason, NOW())");
            $insAct->execute(['rid' => $reservation_id, 'reason' => 'Submitted via website']);

            $this->pdo->commit();

            return [
                'reservation_id' => $reservation_id,
                'estimated_total' => $estimate['total'],
                'estimated_equipment_fee' => $estimate['equipment_fee'],
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
