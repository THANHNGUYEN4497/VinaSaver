<?php
/**
 * Created by PhpStorm.
 * User: PC2017
 * Date: 19/02/12
 * Time: 9:25 AM
 */

namespace App\Repositories;


use Illuminate\Contracts\Mail\Mailable;

interface MailRepository
{
    public function sendCompanyCreated($mailTo, $attr);
}