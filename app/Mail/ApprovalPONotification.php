<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalPONotification extends Mailable  implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $vendor;
    public $po;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($vendor, $po)
    {
        $this->vendor = $vendor;
        $this->po = $po;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.po.approval_notif')
        ->with([
            'vendor' => $this->vendor,
            'po' => $this->po
        ])->to(
            $this->vendor->email
        )->subject('Purchase Order '.$this->po->po_doc_no.' already approved');
    }
}
