<?php

namespace Visualbuilder\EmailTemplates\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Visualbuilder\EmailTemplates\Mail\UserVerifiedEmail;
use Visualbuilder\EmailTemplates\Mail\UserVerifyEmail;

class UserVerifyNotification extends Notification
{
    use Queueable;

    public $url;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

            return app(UserVerifyEmail::class, ['user' => $notifiable,'verificationUrl' => $this->url]);

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [

            ];
    }
}
