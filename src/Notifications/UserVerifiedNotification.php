<?php

namespace Visualbuilder\EmailTemplates\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Visualbuilder\EmailTemplates\Mail\UserVerifiedEmail;

class UserVerifiedNotification extends Notification
{
    use Queueable;

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
        return array_merge($this->userVias($notifiable), ['telegram']);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if(config('email-templates.send_emails.user_verified')) {
            return app(UserVerifiedEmail::class, ['user' => $notifiable]);
        }
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
