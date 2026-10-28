<?php
require_once DRRB . DS . 'app/models/Reservation.php';
use PHPUnit\Framework\TestCase;

class ReservationMappingTest extends TestCase
{
    public function testFromRowMapsColumnsToCamelCaseProperties()
    {
        $reservation = Reservation::fromRow([
            'reservation_id' => 7,
            'reservation_date' => '2026-11-01 10:00:00',
            'name' => 'Member A',
            'member_id' => 1001,
            'major' => 'S1 Teknik Informatika',
            'whatsapp_number' => '6281234567890',
            'reserved_date' => '2026-11-02',
            'duration' => '60',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'reservation_document_id' => 3,
            'visitor_number' => 5,
            'activity' => 'Diskusi',
            'status' => 'ongoing',
            'last_update' => '2026-11-01 10:00:00',
        ]);

        $this->assertSame(7, $reservation->reservationId);
        $this->assertSame(1001, $reservation->memberId);
        $this->assertSame('6281234567890', $reservation->whatsAppNumber);
        $this->assertSame('2026-11-02', $reservation->reservedDate);
        $this->assertSame('09:00:00', $reservation->startTime);
        $this->assertSame('10:00:00', $reservation->endTime);
        $this->assertSame(3, $reservation->reservationDocumentId);
        $this->assertSame(5, $reservation->visitorNumber);
        $this->assertSame('ongoing', $reservation->status);
        $this->assertSame('2026-11-01 10:00:00', $reservation->reservationLastUpdate);
    }
}
