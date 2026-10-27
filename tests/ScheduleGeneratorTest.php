<?php
require_once DRRB . DS . 'app/reservation_logic/schedule_generator.php';
use PHPUnit\Framework\TestCase;

class ScheduleGeneratorTest extends TestCase
{
    private function now($datetime)
    {
        return strtotime($datetime);
    }

    public function testFullDayHasSlotEveryFifteenMinutes()
    {
        $result = populateSchedule('2026-11-02', 60, [], $this->now('2026-11-01 12:00'));

        $this->assertCount(29, $result);
        $this->assertSame('08:00 - 09:00', $result[0]);
        $this->assertSame('15:00 - 16:00', end($result));
    }

    public function testBookedSlotRemovesOverlappingSlotsOnly()
    {
        $booked = [
            ['start_date' => '2026-11-02', 'end_date' => '2026-11-02', 'start_time' => '09:00', 'end_time' => '10:00'],
        ];

        $result = populateSchedule('2026-11-02', 60, $booked, $this->now('2026-11-01 12:00'));

        $this->assertContains('08:00 - 09:00', $result);
        $this->assertContains('10:00 - 11:00', $result);
        $this->assertNotContains('08:15 - 09:15', $result);
        $this->assertNotContains('09:00 - 10:00', $result);
        $this->assertNotContains('09:45 - 10:45', $result);
    }

    public function testBookingOnAnotherDateDoesNotBlockSlots()
    {
        $booked = [
            ['start_date' => '2026-11-03', 'end_date' => '2026-11-03', 'start_time' => '09:00', 'end_time' => '10:00'],
        ];

        $result = populateSchedule('2026-11-02', 60, $booked, $this->now('2026-11-01 12:00'));

        $this->assertCount(29, $result);
    }

    public function testWeekendHasNoSlots()
    {
        $this->assertSame([], populateSchedule('2026-11-07', 60, [], $this->now('2026-11-01 12:00')));
        $this->assertSame([], populateSchedule('2026-11-08', 60, [], $this->now('2026-11-01 12:00')));
    }

    public function testTodayStartsAtNextQuarterHour()
    {
        $result = populateSchedule('2026-11-02', 60, [], $this->now('2026-11-02 10:07'));

        $this->assertSame('10:15 - 11:15', $result[0]);
    }

    public function testTodayRollsOverToNextHourAfterQuarterToo()
    {
        $result = populateSchedule('2026-11-02', 60, [], $this->now('2026-11-02 10:50'));

        $this->assertSame('11:00 - 12:00', $result[0]);
    }

    public function testNoSlotFitsAfterClosingTime()
    {
        $result = populateSchedule('2026-11-02', 60, [], $this->now('2026-11-02 15:20'));

        $this->assertSame([], $result);
    }

    public function testPastDateIsUnavailable()
    {
        $result = populateSchedule('2026-11-01', 60, [], $this->now('2026-11-02 10:00'));

        $this->assertSame(['Jadwal tidak tersedia'], $result);
    }

    public function testDurationLongerThanOpeningHoursHasNoSlots()
    {
        $this->assertSame([], populateSchedule('2026-11-02', 540, [], $this->now('2026-11-01 12:00')));
    }

    public function testLongDurationsAreSortedByStartThenLongestFirst()
    {
        $result = populateSchedule('2026-11-02', range(480, 150, 30), [], $this->now('2026-11-01 12:00'));

        $this->assertSame('08:00 - 16:00', $result[0]);
        $this->assertSame('08:00 - 15:30', $result[1]);
    }

    public function testCustomHoursAndStepChangeTheSlots()
    {
        $settings = ['open_time' => '09:00', 'close_time' => '12:00', 'slot_step' => 30, 'open_days' => [1, 2, 3, 4, 5]];

        $result = populateSchedule('2026-11-02', 60, [], $this->now('2026-11-01 12:00'), $settings);

        $this->assertSame(['09:00 - 10:00', '09:30 - 10:30', '10:00 - 11:00', '10:30 - 11:30', '11:00 - 12:00'], $result);
    }

    public function testSaturdayIsOpenWhenConfigured()
    {
        $settings = ['open_time' => '08:00', 'close_time' => '16:00', 'slot_step' => 15, 'open_days' => [6]];

        $this->assertNotSame([], populateSchedule('2026-11-07', 60, [], $this->now('2026-11-01 12:00'), $settings));
        $this->assertSame([], populateSchedule('2026-11-02', 60, [], $this->now('2026-11-01 12:00'), $settings));
    }

    public function testTodayRoundsUpToConfiguredStep()
    {
        $settings = ['open_time' => '08:00', 'close_time' => '16:00', 'slot_step' => 30, 'open_days' => [1, 2, 3, 4, 5]];

        $result = populateSchedule('2026-11-02', 60, [], $this->now('2026-11-02 10:07'), $settings);

        $this->assertSame('10:30 - 11:30', $result[0]);
    }

    public function testLongDurationOptionsFollowOpeningHours()
    {
        $defaults = ReservationSettings::defaults();

        $this->assertSame(range(480, 150, 30), longDurationOptions($defaults));
        $this->assertSame(range(240, 150, 30), longDurationOptions(['open_time' => '09:00', 'close_time' => '13:00'] + $defaults));
        $this->assertSame([], longDurationOptions(['open_time' => '09:00', 'close_time' => '11:00'] + $defaults));
    }

    public function testOpeningHoursCheck()
    {
        $this->assertTrue(isWithinOpeningHours('2026-11-02', ['08:00', '16:00']));
        $this->assertTrue(isWithinOpeningHours('2026-11-02', ['09:00:00', '10:00:00']));
        $this->assertFalse(isWithinOpeningHours('2026-11-02', ['07:45', '08:45']));
        $this->assertFalse(isWithinOpeningHours('2026-11-02', ['15:30', '16:30']));
        $this->assertFalse(isWithinOpeningHours('2026-11-07', ['09:00', '10:00']));
    }
}
