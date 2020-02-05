<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalPurchaseOrder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $po;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $po)
    {
        $this->user = $user;
        $this->po = $po;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.po.approval')
        ->with([
            'user' => $this->user,
            'po' => $this->po
        ])->to(
            $this->user->email
        )->subject('Purchase Order '.$this->po->po_doc_no.' submited');
    }
}
