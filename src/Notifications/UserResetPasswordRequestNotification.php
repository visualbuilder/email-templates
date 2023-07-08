<?php

namespace Visualbuilder\EmailTemplates\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Visualbuilder\EmailTemplates\Mail\UserRequestPasswordResetEmail;

class UserResetPasswordRequestNotification extends Notification
{
    use Queueable;

    public $tokenUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($tokenUrl)
    {
        $this->tokenUrl = $tokenUrl;
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
        return (new UserRequestPasswordResetEmail($notifiable, $this->tokenUrl));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [ ];
    }
}
