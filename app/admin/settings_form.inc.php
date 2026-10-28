<?php
$settings = ReservationSettings::get();

$form = new simbio_form_table_AJAX('reservationSettingsForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
$form->submit_button_attr = 'name="saveSettings" value="' . __('Save') . '" class="s-btn btn btn-primary"';

$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$form->addAnything('Jam Buka', '<input type="time" name="open_time" class="form-control col-3" value="' . $settings['open_time'] . '" required/>');
$form->addAnything('Jam Tutup', '<input type="time" name="close_time" class="form-control col-3" value="' . $settings['close_time'] . '" required/>');

$stepOptions = [];
foreach (ReservationSettings::ALLOWED_STEPS as $step) {
    $stepOptions[] = [$step, $step . ' menit'];
}
$form->addSelectList('slot_step', 'Interval Slot', $stepOptions, $settings['slot_step'], 'class="form-control col-3" required', 'Slot Step');

$dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
$days = '';
foreach ($dayNames as $number => $name) {
    $checked = in_array($number, $settings['open_days'], true) ? ' checked' : '';
    $days .= '<label class="mr-3"><input type="checkbox" name="open_days[]" value="' . $number . '"' . $checked . '> ' . $name . '</label>';
}
$form->addAnything('Hari Buka', $days);

echo $form->printOut();
