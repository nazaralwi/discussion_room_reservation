<?php
use SLiMS\DB;
use SLiMS\Migration\Migration;

class MakeRoomReservationsTransactional extends Migration
{
    public function up()
    {
        DB::getInstance()->query("ALTER TABLE `room_reservations` ENGINE=InnoDB");
        DB::getInstance()->query("ALTER TABLE `room_reservations` ADD INDEX `idx_room_reservations_reserved_date` (`reserved_date`)");
    }

    public function down()
    {
        DB::getInstance()->query("ALTER TABLE `room_reservations` DROP INDEX `idx_room_reservations_reserved_date`");
        DB::getInstance()->query("ALTER TABLE `room_reservations` ENGINE=MyISAM");
    }
}
