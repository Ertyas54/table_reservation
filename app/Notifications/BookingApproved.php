<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingApproved extends Notification
{
    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'group_id'   => $this->booking->group_id,
            'restaurant' => $this->booking->restaurant->name,
            'date'       => $this->booking->booking_date->format('d.m.Y'),
            'time'       => substr($this->booking->slot->start_time, 0, 5)
                . '–' . substr($this->booking->slot->end_time, 0, 5),
            'tables'     => $this->booking->table->table_number ?? '—',
            'message'    => 'Ваша заявка одобрена — ждём вас',
        ];
    }
}
