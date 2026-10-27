# Reservasi Ruang Diskusi

### Cara Install Plugin
1. Klik Code > Download Zip.
2. Ekstrak file .zip nya ke folder `plugins/` yang ada di folder utama SLiMS Anda. Pastikan nama folder atau _directory_-nya adalah `discussion_room_reservation` (tanpa _postfix_ `-master`).
3. Masuk modul **System** masuk menu **Plugins**. Cari plugin dengan nama **Reservasi Ruang Diskusi** kemudian aktifkan pluginnya.

Jika memperbarui plugin yang sudah terpasang, nonaktifkan lalu aktifkan kembali pluginnya agar migrasi _database_ terbaru dijalankan.

Plugin ini tidak memerlukan Composer untuk digunakan, semua pustaka yang dibutuhkan sudah disertakan di folder `lib/`. Plugin sudah diuji pada SLiMS 9.6.1 dan 9.8.0, dan memerlukan PHP 8.1 atau lebih baru.

### Pengaturan
Menu **Pengaturan** di halaman admin plugin digunakan untuk mengatur jam buka, jam tutup, interval slot, dan hari buka. Nilai awalnya adalah 08:00 sampai 16:00, interval 15 menit, Senin sampai Jumat.

### Menjalankan test
Composer hanya diperlukan untuk menjalankan test:
```
composer install
php lib/vendor/bin/phpunit
```

### File structure
![File Structure](docs/file_structure.png)

### Admin Area
Setelah plugin diaktifkan, menu reservasi dapat dilihat di modul **Membership** di bagian **Plugins > Reservasi Ruang Diskusi**.
![Admin Area](docs/admin_area.png)

### Member Area
Halaman member pada SLiMS diganti dengan salinan yang sudah memuat fitur reservasi, karena SLiMS belum menyediakan _hook_ untuk menambah menu pada halaman member. Plugin memilih salinan yang sesuai dengan versi SLiMS secara otomatis:

- `app/member_page/legacy.php`: salinan `lib/contents/member.inc.php` SLiMS 9.6.1, untuk SLiMS 9.6 ke bawah.
- `app/member_page/current.php`: salinan `lib/contents/member.inc.php` SLiMS 9.8.0, untuk SLiMS 9.7 ke atas.

Kode-kode yang berhubungan dengan fitur reservasi ruang diskusi diapit oleh komentar:
```
// MARK: - Discussion Room Reservasion
{kode}
// END: - Discussion Room Reservasion
```

![Member Area](docs/member_area.png)

Jika sudah disesuaikan, maka fitur reservasi sudah dapat muncul di halaman member.

### Opac Area
Setelah plugin diaktifkan, jadwal reservasi ditampilkan di halaman Opac. Untuk mengaksesnya dapat menggunakan link `https://{slims_base}/index.php?p=jadwal_ruang_diskusi`.
![Opac Area](docs/opac_area.png)
