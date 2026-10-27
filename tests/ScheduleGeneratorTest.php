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
}
