<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubmitReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $reservation)
    {
        $this->user = $user;
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.reservation.submit')
        ->with([
            'user' => $this->user,
            'reservation' => $this->reservation
        ])->to(
            $this->user->email
        )->subject('Reservation '.$this->reservation->reservation_code.' submited');
    }
}
