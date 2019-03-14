<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyCreated extends Mailable
{
    use Queueable, SerializesModels;
    protected $attributes;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.companies.created')
            ->subject("Conet へようこそ！")
            ->with([
                'email' => $this->attributes['email'],
                'password' => $this->attributes['password']
            ]);
    }
}
