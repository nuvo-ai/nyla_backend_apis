<?php

namespace App\Services\Notification;

use App\Helpers\Helper;
use App\Jobs\AppMailerJob;

class AppMailerService
{
    /**
     * Global email helper
     *  @param $params['data']           = ['foo' => 'Hello John Doe!']; //optional
     *  @param  $params['to']*             = 'recipient@example.com'; //required
     *  @param  $params['template_type']  = 'markdown';  //default is view
     *  @param  $params['template']*       = 'emails.app-mailer'; //path to the email template
     *  @param  $params['subject']*        = 'Some Awesome Subject'; //required
     *  @param  $params['from_email']     = 'johndoe@example.com';
     *  @param  $params['from_name']      = 'John Doe';
     *  @param  $params['cc_emails']      = ['email@mail.com', 'email1@mail.com'];
     *  @param  $params['bcc_emails']      = ['email@mail.com', 'email1@mail.com'];
     */
    public static function send(array $data)
    {
        try {
            Helper::dispatchJob(new AppMailerJob($data));
            \Log::info('Email queued successfully', [
                'to' => $data['to'],
                'subject' => $data['subject'],
            ]);
        } catch (\Exception $e) {
            // Log the error message
            report_error($e);
            throw $e;
        }
    }

    /**
     * Global email helper
     *
     * @param  $params['data']  = ['foo' => 'Hello John Doe!']; //optional
     * @param  $params['to']*  = 'recipient@example.com'; //required
     * @param  $params['template_type']  = 'markdown';  //default is view
     * @param  $params['template']*  = 'emails.app-mailer'; //path to the email template
     * @param  $params['subject']*  = 'Some Awesome Subject'; //required
     * @param  $params['from_email']  = 'johndoe@example.com';
     * @param  $params['from_name']  = 'John Doe';
     * @param  $params['cc_emails']  = ['email@mail.com', 'email1@mail.com'];
     * @param  $params['bcc_emails']  = ['email@mail.com', 'email1@mail.com'];
     */
    public static function sendNow(array $data)
    {
        try {
            Helper::dispatchJobSync(new AppMailerJob($data));
            \Log::info('Email sent successfully', [
                'to' => $data['to'],
                'subject' => $data['subject'],
            ]);
        } catch (\Exception $e) {
            // Log the error message
            report_error($e);
            throw $e;
        }
    }
}
