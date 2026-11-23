<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingRejected extends Notification
{
    public function __construct(public Booking $booking, public string $reason) {}

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
            'reason'     => $this->reason,
            'message'    => 'Ваша заявка отклонена',
        ];
    }
}
