<?php

require_once __DIR__ . '/schedule_generator.php';

date_default_timezone_set('Asia/Jakarta'); // Set the timezone to Jakarta/Western Indonesia Time (WIB)

$bookedSchedules = Reservation::getBookedSchedules();

// Check if the selectedDate is received from the POST request
if (isset($_POST['selectedDate'])) {
    error_log('Selected Date:' . $_POST['selectedDate']);
    $selectedDate = $_POST['selectedDate'];
    $schedules = [
        '30' => populateSchedule($selectedDate, 30, $bookedSchedules),
        '60' => populateSchedule($selectedDate, 60, $bookedSchedules),
        '90' => populateSchedule($selectedDate, 90, $bookedSchedules),
        '120' => populateSchedule($selectedDate, 120, $bookedSchedules),
        '>120' => populateSchedule($selectedDate, longDurationOptions(), $bookedSchedules),
    ];
    echo json_encode($schedules);
} else {
    // Handle the case when no date is provided
    echo json_encode(['error' => 'No date provided']);
}

exit;