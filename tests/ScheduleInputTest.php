<?php
require_once DRRB . DS . 'app/helper/reservation_utils.php';
use PHPUnit\Framework\TestCase;

class ScheduleInputTest extends TestCase
{
    public function testParsesValidRange()
    {
        $this->assertSame(['09:00', '10:30'], parseScheduleRange('09:00 - 10:30'));
    }

    public function testParsesRangeWithSeconds()
    {
        $this->assertSame(['09:00:00', '10:00:00'], parseScheduleRange('09:00:00 - 10:00:00'));
    }

    public function testRejectsInvalidRange()
    {
        $invalidRanges = [
            '',
            null,
            'garbage',
            '09:00',
            '09:00-10:00',
            '09:00 - 10:00 - 11:00',
            '10:00 - 09:00',
            '09:00 - 09:00',
            '25:00 - 26:00',
            '09:60 - 10:00',
        ];

        foreach ($invalidRanges as $range) {
            $this->assertNull(parseScheduleRange($range), var_export($range, true));
        }
    }

    public function testAcceptsRealDate()
    {
        $this->assertTrue(isValidReservationDate('2026-11-02'));
    }

    public function testRejectsInvalidDate()
    {
        $invalidDates = ['', null, '2026-02-31', '02-11-2026', 'tomorrow'];

        foreach ($invalidDates as $date) {
            $this->assertFalse(isValidReservationDate($date), var_export($date, true));
        }
    }
}
