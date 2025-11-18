<?php
// src/ReservationService.php
declare(strict_types=1);

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
     * @return array {room_fee, equipment_fee, total, breakdown...}
     */
    public function calculateEstimate(array $data): array
    {
        $hours = max(0.0, (float) ($data['hours'] ?? 0.0));
        $equipment_codes = $data['equipment_codes'] ?? [];

        $base_rate = (float) $this->config->rates->base_hourly_rate;
        $minimum_fee = (float) $this->config->rates->minimum_fee;

        $room_fee = $hours * $base_rate;

        $equipment_fee = 0.0;
        foreach ($equipment_codes as $code) {
            $code = (string) $code;
            $equip = $this->equipmentRepo->findByCode($code);
            if ($equip) {
                $equipment_fee += ($equip['rate_per_hour'] * $hours);
            } else {
                // if not found in DB, fallback to config rates if exist
                if (isset($this->config->rates->equipment[$code])) {
                    $equipment_fee += ($this->config->rates->equipment[$code] * $hours);
                }
            }
        }

        $raw_total = $room_fee + $equipment_fee;
        $total = ($hours < 2) ? max($minimum_fee, $raw_total) : $raw_total;

        return [
            'hours' => $hours,
            'room_fee' => round($room_fee, 2),
            'equipment_fee' => round($equipment_fee, 2),
            'raw_total' => round($raw_total, 2),
            'total' => round($total, 2),
            'currency' => 'PHP'
        ];
    }

    /**
     * Create reservation (transactional). Returns reservation id + snapshot amounts.
     * $input expected keys from form.
     */
    public function createReservation(array $input): array
    {
        // basic server-side validation
        $errors = [];
        if (empty($input['name'])) $errors['name'] = 'Name is required';
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
        if (empty($input['phone'])) $errors['phone'] = 'Phone is required';
        if (empty($input['start_time'])) $errors['start_time'] = 'Start time is required';
        if (empty($input['year']) || empty($input['month']) || empty($input['day'])) $errors['reservation_date'] = 'Complete reservation date is required';
        if (empty($input['hours']) || (float)$input['hours'] <= 0) $errors['hours'] = 'Duration must be > 0';
        if (empty($input['persons']) || (int)$input['persons'] <= 0) $errors['persons'] = 'Persons must be > 0';
        if (strtolower($input['type']) === 'study' && empty($input['student_id'])) $errors['student_id'] = 'Student ID required for Study room type';
        
        if (!empty($errors)) throw new ValidationException($errors);

        // build reservation_date
        $year = (int)$input['year'];
        $month = (int)$input['month'];
        $day = (int)$input['day'];
        $reservation_date = sprintf('%04d-%02d-%02d', $year, $month, $day);

        // compute estimated fees (snapshot) using calculateEstimate
        $estimate = $this->calculateEstimate([
            'hours' => (float)$input['hours'],
            'equipment_codes' => (array)$input['equipment']
        ]);

        // Transaction: insert user (or reuse existing user by email), reservation, reservation_equipment, reservation_actions
        try {
            $this->pdo->beginTransaction();

            // find or create user (by email)
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $input['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $user_id = (int)$user['id'];
                // optionally update name/phone/student_id
                $up = $this->pdo->prepare("UPDATE users SET full_name = :name, phone = :phone, student_id = :sid WHERE id = :id");
                $up->execute(['name' => $input['name'], 'phone' => $input['phone'], 'sid' => $input['student_id'], 'id' => $user_id]);
            } else {
                $ins = $this->pdo->prepare("INSERT INTO users (full_name, student_id, email, phone) VALUES (:name, :sid, :email, :phone)");
                $ins->execute(['name' => $input['name'], 'sid' => $input['student_id'], 'email' => $input['email'], 'phone' => $input['phone']]);
                $user_id = (int)$this->pdo->lastInsertId();
            }

            // Validate start_time server-side
            if (!empty($input['start_time'])) {
                $startTime = $input['start_time']; // format HH:MM

                // Split into hours and minutes
                [$hours, $minutes] = explode(':', $startTime);
                $hours = (int)$hours;
                $minutes = (int)$minutes;

                // Convert to total minutes since 00:00
                $totalMinutes = $hours * 60 + $minutes;

                // Define allowed range in minutes: 13:00 → 01:00 next day
                $minMinutes = 13 * 60;  // 13:00 => 780 minutes
                $maxMinutes = 25 * 60;  // 01:00 next day => 25:00 => 1500 minutes

                // Adjust times after midnight by adding 24 hours (if needed)
                if ($totalMinutes < $minMinutes) {
                    $totalMinutes += 24 * 60; // add 1440 minutes
                }

                if ($totalMinutes < $minMinutes || $totalMinutes > $maxMinutes) {
                    $errors['start_time'] = 'Start time must be between 13:00 and 01:00.';
                }
            } else {
                $errors['start_time'] = 'Start time is required';
            }

            // Throw validation exception if any errors
            if (!empty($errors)) throw new ValidationException($errors);


            // insert reservation
            $base_rate = (float) $this->config->rates->base_hourly_rate;
            $minimum_fee = (float) $this->config->rates->minimum_fee;
            $insertRes = $this->pdo->prepare(
                "INSERT INTO reservations
                  (user_id, reservation_date, start_time, hours, persons, student_id,
                   contact_email, contact_phone, base_hourly_rate, minimum_fee,
                   estimated_equipment_fee, estimated_total_fee, status, created_at, updated_at)
                 VALUES
                  (:user_id, :reservation_date, :start_time, :hours, :persons, :student_id,
                   :email, :phone, :base_rate, :minimum_fee, :est_eq_fee, :est_total, 'PENDING', NOW(), NOW())"
            );
            $insertRes->execute([
                'user_id' => $user_id,
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

            // insert reservation_equipment rows for each equipment selected
            foreach ((array)$input['equipment'] as $code) {
                $equip = $this->equipmentRepo->findByCode($code);
                if (!$equip) {
                    // fallback to config table
                    if (!isset($this->config->rates->equipment[$code])) continue;
                    $rate = (float)$this->config->rates->equipment[$code];
                    // attempt to map to an equipment_id if exists in DB by code - already null, skip id
                    $equipment_id = null;
                    $quantity = 1;
                } else {
                    $equipment_id = (int)$equip['id'];
                    $rate = (float)$equip['rate_per_hour'];
                    $quantity = 1;
                }
                if ($equipment_id) {
                    $insEq = $this->pdo->prepare("INSERT INTO reservation_equipment (reservation_id, equipment_id, quantity, equipment_rate_per_hour) VALUES (:rid, :eid, :qty, :rate)");
                    $insEq->execute(['rid' => $reservation_id, 'eid' => $equipment_id, 'qty' => $quantity, 'rate' => $rate]);
                }
            }

            // insert action (audit): SUBMITTED
            $insAction = $this->pdo->prepare("INSERT INTO reservation_actions (reservation_id, admin_id, action_type, action_reason, created_at) VALUES (:rid, NULL, 'SUBMITTED', :reason, NOW())");
            $insAction->execute(['rid' => $reservation_id, 'reason' => 'User submitted reservation via website.']);

            $this->pdo->commit();

            return [
                'reservation_id' => $reservation_id,
                'estimated_total' => $estimate['total'],
                'estimated_equipment_fee' => $estimate['equipment_fee']
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
