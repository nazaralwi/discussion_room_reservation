<?php

use benhall14\phpCalendar\Calendar as Calendar;

$calendar = new Calendar();
$reservationEvents = Reservation::getAllEvents();
$events = [];

function displayCalendarForDate($calendar, $date)
{
    $calendar->display($date, 'grey');
}

function decrementEndTimeByOneMinute($endTime) 
{
    $newTime = date('H:i', strtotime($endTime . ' - 1 minute'));
    return $newTime;
}

foreach ($reservationEvents as $event) {
    $events[] = array(
        'start' => $event->reservedDate . ' ' . getMinutesAndSecond($event->startTime),
        'end' => $event->reservedDate . ' ' . getMinutesAndSecond(decrementEndTimeByOneMinute($event->endTime)),
        'summary' => $event->memberId,
        'mask' => false,
    );
}

$settings = ReservationSettings::get();

if (!in_array(6, $settings['open_days'], true)) {
    $calendar->hideSaturdays();
}

if (!in_array(0, $settings['open_days'], true)) {
    $calendar->hideSundays();
}

$calendar
    ->setTimeFormat($settings['open_time'], decrementEndTimeByOneMinute($settings['close_time']), $settings['slot_step'])
    ->useWeekView()
    ->addEvents($events);