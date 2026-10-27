<?php

class ReservationSettings
{
    const SETTING_NAME = 'room_reservation_settings';
    const ALLOWED_STEPS = [5, 10, 15, 20, 30, 60];

    private static $cache = null;

    public static function defaults()
    {
        return [
            'open_time' => '08:00',
            'close_time' => '16:00',
            'slot_step' => 15,
            'open_days' => [1, 2, 3, 4, 5],
        ];
    }

    public static function sanitize(array $input)
    {
        $errors = [];
        $defaults = self::defaults();
        $timePattern = '/^([01]\d|2[0-3]):[0-5]\d$/';

        $openTime = trim((string) ($input['open_time'] ?? ''));
        $closeTime = trim((string) ($input['close_time'] ?? ''));
        $rawStep = (string) ($input['slot_step'] ?? '');
        $step = preg_match('/^\d+$/', $rawStep) ? (int) $rawStep : 0;

        $days = [];
        foreach ((array) ($input['open_days'] ?? []) as $day) {
            if (!preg_match('/^[0-6]$/', (string) $day)) {
                $days = [];
                break;
            }

            $days[] = (int) $day;
        }
        $days = array_values(array_unique($days));
        sort($days);

        if (!preg_match($timePattern, $openTime) || !preg_match($timePattern, $closeTime)) {
            $errors[] = 'Jam buka dan jam tutup harus berformat HH:MM';
        } elseif (strtotime($openTime) >= strtotime($closeTime)) {
            $errors[] = 'Jam tutup harus lebih besar dari jam buka';
        }

        if (!in_array($step, self::ALLOWED_STEPS, true)) {
            $errors[] = 'Interval slot harus salah satu dari ' . implode(', ', self::ALLOWED_STEPS) . ' menit';
        }

        if ($days === []) {
            $errors[] = 'Pilih minimal satu hari buka';
        }

        if ($errors !== []) {
            return ['settings' => $defaults, 'errors' => $errors];
        }

        return [
            'settings' => [
                'open_time' => $openTime,
                'close_time' => $closeTime,
                'slot_step' => $step,
                'open_days' => $days,
            ],
            'errors' => [],
        ];
    }

    public static function get()
    {
        global $dbs;

        if (self::$cache !== null) {
            return self::$cache;
        }

        $settings = self::defaults();

        if (isset($dbs) && $dbs instanceof mysqli) {
            $stmt = $dbs->prepare("SELECT setting_value FROM setting WHERE setting_name = ?");
            $name = self::SETTING_NAME;
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stored = $row ? json_decode($row['setting_value'], true) : null;

            if (is_array($stored)) {
                $checked = self::sanitize($stored);

                if ($checked['errors'] === []) {
                    $settings = $checked['settings'];
                }
            }
        }

        self::$cache = $settings;

        return $settings;
    }

    public static function save(array $settings)
    {
        global $dbs;

        $name = self::SETTING_NAME;
        $value = json_encode($settings);

        $stmt = $dbs->prepare("INSERT INTO setting (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param("ss", $name, $value);
        $saved = $stmt->execute();

        self::$cache = null;

        return $saved;
    }

    public static function openMinutes(array $settings)
    {
        return (strtotime($settings['close_time']) - strtotime($settings['open_time'])) / 60;
    }
}
