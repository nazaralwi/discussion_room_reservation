<?php
use PHPUnit\Framework\TestCase;

class ReservationSettingsTest extends TestCase
{
    public function testDefaultsMatchOriginalHardcodedValues()
    {
        $this->assertSame([
            'open_time' => '08:00',
            'close_time' => '16:00',
            'slot_step' => 15,
            'open_days' => [1, 2, 3, 4, 5],
        ], ReservationSettings::defaults());
    }

    public function testSanitizeAcceptsValidInput()
    {
        $result = ReservationSettings::sanitize([
            'open_time' => '09:00',
            'close_time' => '17:30',
            'slot_step' => '30',
            'open_days' => ['6', '1', '1', '3'],
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame([
            'open_time' => '09:00',
            'close_time' => '17:30',
            'slot_step' => 30,
            'open_days' => [1, 3, 6],
        ], $result['settings']);
    }

    public function testSanitizeRejectsBadTimes()
    {
        $badTimes = [
            ['open_time' => '9:00', 'close_time' => '16:00'],
            ['open_time' => '08:00', 'close_time' => '24:00'],
            ['open_time' => '16:00', 'close_time' => '08:00'],
            ['open_time' => '08:00', 'close_time' => '08:00'],
            ['open_time' => '', 'close_time' => ''],
        ];

        foreach ($badTimes as $times) {
            $result = ReservationSettings::sanitize($times + ['slot_step' => 15, 'open_days' => [1]]);

            $this->assertNotSame([], $result['errors'], json_encode($times));
            $this->assertSame(ReservationSettings::defaults(), $result['settings']);
        }
    }

    public function testSanitizeRejectsUnsupportedStep()
    {
        foreach ([0, 7, 45, 120, -15, 'abc', '15abc', ''] as $step) {
            $result = ReservationSettings::sanitize(['open_time' => '08:00', 'close_time' => '16:00', 'slot_step' => $step, 'open_days' => [1]]);

            $this->assertNotSame([], $result['errors'], var_export($step, true));
        }
    }

    public function testSanitizeRejectsBadDays()
    {
        foreach ([[], [7], [-1], ['x'], [1, 'x'], ['1.5']] as $days) {
            $result = ReservationSettings::sanitize(['open_time' => '08:00', 'close_time' => '16:00', 'slot_step' => 15, 'open_days' => $days]);

            $this->assertNotSame([], $result['errors'], json_encode($days));
        }
    }

    public function testGetFallsBackToDefaultsWithoutDatabase()
    {
        $this->assertSame(ReservationSettings::defaults(), ReservationSettings::get());
    }
}
