<?php
/**
 * Created by PhpStorm.
 * User: PC2017
 * Date: 19/02/12
 * Time: 9:27 AM
 */

namespace App\Services\Mail;

use App\Mail\CompanyCreated;
use App\Repositories\MailRepository;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService implements MailRepository
{

    public function sendCompanyCreated($mailTo, $attibutes)
    {
        $this->sendMail($mailTo, new CompanyCreated($attibutes));
    }

    protected function sendMail($mailTo, Mailable $mailable) {
        Mail::to($mailTo)->send($mailable);
    }
}