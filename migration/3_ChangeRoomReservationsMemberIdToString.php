<?php
use SLiMS\DB;
use SLiMS\Migration\Migration;

class ChangeRoomReservationsMemberIdToString extends Migration
{
    public function up()
    {
        DB::getInstance()->query("ALTER TABLE `room_reservations` MODIFY `member_id` varchar(20) DEFAULT NULL");
    }

    public function down()
    {
        DB::getInstance()->query("ALTER TABLE `room_reservations` MODIFY `member_id` int(11) DEFAULT NULL");
    }
}
