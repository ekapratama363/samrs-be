<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $reservation;
    public $approval;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $reservation, $approval)
    {
        $this->user = $user;
        $this->reservation = $reservation;
        $this->approval = $approval;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.reservation.approval')
        ->with([
            'user' => $this->user,
            'reservation' => $this->reservation,
            'approval' => $this->approval
        ])->to(
            $this->user->email
        )->subject('Approval for reservation '.$this->reservation->reservation_code.'');
    }
}
