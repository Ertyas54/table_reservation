<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class NewBookingRequest extends Notification
{
    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $first = $this->booking;

        return [
            'group_id'     => $first->group_id,
            'restaurant'   => $first->restaurant->name,
            'date'         => $first->booking_date->format('d.m.Y'),
            'time'         => substr($first->slot->start_time, 0, 5)
                . '–' . substr($first->slot->end_time, 0, 5),
            'tables'       => $first->table->table_number ?? '—',
            'guest'        => trim($first->user->first_name . ' ' . $first->user->last_name),
            'message'      => 'Новая заявка на бронирование',
        ];
    }
}
