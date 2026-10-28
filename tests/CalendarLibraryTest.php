<?php
require_once DRRB . DS . 'lib/php-calendar/Calendar.php';
use PHPUnit\Framework\TestCase;

class CalendarLibraryTest extends TestCase
{
    public function testBundledCalendarRendersAWeekWithEvents()
    {
        $calendar = new benhall14\phpCalendar\Calendar();
        $calendar
            ->hideSundays()
            ->setTimeFormat('08:00', '15:59', 15)
            ->useWeekView()
            ->addEvents([['start' => '2026-11-02 09:00', 'end' => '2026-11-02 09:59', 'summary' => '1001', 'mask' => false]]);

        ob_start();
        $calendar->display('2026-11-02', 'grey');
        $html = ob_get_clean();

        $this->assertStringContainsString('weekly-calendar', $html);
        $this->assertStringContainsString('1001', $html);
    }

    public function testPluginDoesNotNeedComposerAutoloaderToLoadCalendar()
    {
        $init = file_get_contents(DRRB . DS . 'init.plugin.php');

        $this->assertStringNotContainsString('vendor/autoload.php', $init);
        $this->assertFileExists(DRRB . DS . 'lib/php-calendar/Calendar.php');
    }
}
