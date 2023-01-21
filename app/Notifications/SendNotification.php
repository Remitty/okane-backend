<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The name of the queue connection to use when queueing the notification.
     *
     * @var string
     */
    // public $connection = 'redis';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['telegram'];
    }

    /**
     * Get the telegram representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\Telegram\TelegramMessage
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->to('-815369412')
            ->content($notifiable);
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
