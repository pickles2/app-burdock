<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail;

// 拡張元を Notification ではなく VerifyEmail とする。
// そうしなければ、後述の $this->verificationUrl() を使えない。
// class CustomVerifyEmail extends Notification
class CustomVerifyEmail extends VerifyEmail
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
     // toMail() メソッドの中身を下記のようにする
     public function toMail($notifiable)
     {
         return (new MailMessage)
             ->subject(__('Verify Your Email Address'))
             ->view('emails.verify')
             ->action(__('Verify Email Address'), $this->verificationUrl($notifiable));
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
            //
        ];
    }
}
