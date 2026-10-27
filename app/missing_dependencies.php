<?php
defined('INDEX_AUTH') or die('Direct access not allowed!');

echo '<div class="errorBox">';
echo 'Plugin Reservasi Ruang Diskusi belum dapat digunakan karena dependensinya belum terpasang. ';
echo 'Jalankan perintah <code>composer install</code> pada folder <code>plugins/' . basename(dirname(__DIR__)) . '</code>, lalu muat ulang halaman ini.';
echo '</div>';
