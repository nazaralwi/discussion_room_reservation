<?php

function generateAvailableSchedules($startDate, $durationInMinutes, $bookedSchedules, $now = null, $settings = null)
{
    $settings = $settings ?? ReservationSettings::get();
    $step = $settings['slot_step'];

    $currentTime = $now ?? time(); // Current date and time 2023-12-26 13:45

    // Set the start date to the current hour if it's in the past
    if ($startDate < $currentTime) {
        $totalMinutes = (int) date('G', $currentTime) * 60 + (int) date('i', $currentTime);
        $nextSlot = (intdiv($totalMinutes, $step) + 1) * $step;

        $startDate = mktime(intdiv($nextSlot, 60), $nextSlot % 60, 0, date('m', $currentTime), date('d', $currentTime), date('Y', $currentTime));
    }

    // Check if the provided start date falls on a weekend (Saturday or Sunday)
    $dayOfWeek = (int) date('w', $startDate);
    if (!in_array($dayOfWeek, $settings['open_days'], true)) {
        return []; // Return an empty array for weekends
    }

    $endDate = strtotime(date('Y-m-d', $startDate) . ' ' . $settings['close_time']); // End date for the given day

    $currentTime = $startDate;
    $availableSchedules = [];

    // Loop through the dates and times to generate available schedules
    while ($currentTime + $durationInMinutes * 60 <= $endDate) {
        // Calculate the end time for the current schedule
        $endTimeForSchedule = $currentTime + $durationInMinutes * 60;

        // Check if the current slot is available
        $slotIsAvailable = true;
        foreach ($bookedSchedules as $bookedSlot) {
            $bookedStartTime = strtotime($bookedSlot['start_date'] . ' ' . $bookedSlot['start_time']);
            $bookedEndTime = strtotime($bookedSlot['end_date'] . ' ' . $bookedSlot['end_time']);

            // Check for overlap with booked slots
            if (!($endTimeForSchedule <= $bookedStartTime || $currentTime >= $bookedEndTime)) {
                $slotIsAvailable = false;
                break;
            }
        }

        // If the slot is available, add it to the list of available schedules
        if ($slotIsAvailable) {
            $formattedStartDate = date('H:i', $currentTime);
            $formattedEndDate = date('H:i', $endTimeForSchedule);
            $availableSchedules[] = "{$formattedStartDate} - {$formattedEndDate}";
        }

        // Move to the next time slot (increment by 30 minutes)
        $currentTime = strtotime('+' . $step . ' minutes', $currentTime);
        // If the current time exceeds the end time of the day, exit the loop
        if ($currentTime >= strtotime(date('Y-m-d', $startDate + 86400) . ' ' . $settings['open_time'])) {
            break;
        }
    }

    return $availableSchedules;
}

function generateLongestAvailableSchedules($startDate, $durationOptions, $bookedSchedules, $now = null, $settings = null)
{
    $availableSchedules = [];

    rsort($durationOptions); // Sort durations in descending order

    foreach ($durationOptions as $duration) {
        $available = generateAvailableSchedules($startDate, $duration, $bookedSchedules, $now, $settings);
        if (!empty($available)) {
            $availableSchedules[] = $available;
        }
    }

    $timeSlots = array_reduce($availableSchedules, 'array_merge', []);
    usort($timeSlots, function ($a, $b) {
        // Split the time slots into start and end times
        list($startA, $endA) = explode('-', $a);
        list($startB, $endB) = explode('-', $b);

        // Compare start times first
        if ($startA !== $startB) {
            return strcmp($startA, $startB); // Sort alphabetically
        }

        // If start times are equal, compare end times
        return strcmp($endB, $endA); // Sort alphabetically
    });
    return $timeSlots;
}

function populateSchedule($startDateParam, $durationInMinutes, $bookedSchedules = [], $now = null, $settings = null)
{
    $settings = $settings ?? ReservationSettings::get();
    $startDate = strtotime($startDateParam . ' ' . $settings['open_time']); // Provide the desired date here

    // Check if the provided start date is not in the past for today's schedule before generating schedules
    if ($startDate >= strtotime('today ' . $settings['open_time'], $now ?? time())) {
        if (is_array($durationInMinutes)) {
            return generateLongestAvailableSchedules($startDate, $durationInMinutes, $bookedSchedules, $now, $settings);
        } else {
            return generateAvailableSchedules($startDate, $durationInMinutes, $bookedSchedules, $now, $settings);
        }
    } else {
        return ['Jadwal tidak tersedia']; // Return an array with an unavailable message if it's in the past
    }
}

function longDurationOptions($settings = null)
{
    $settings = $settings ?? ReservationSettings::get();
    $longest = intdiv((int) ReservationSettings::openMinutes($settings), 30) * 30;

    return $longest >= 150 ? range($longest, 150, 30) : [];
}

function isWithinOpeningHours($date, array $times, $settings = null)
{
    $settings = $settings ?? ReservationSettings::get();

    if (!in_array((int) date('w', strtotime($date)), $settings['open_days'], true)) {
        return false;
    }

    return strtotime($times[0]) >= strtotime($settings['open_time'])
        && strtotime($times[1]) <= strtotime($settings['close_time']);
}
