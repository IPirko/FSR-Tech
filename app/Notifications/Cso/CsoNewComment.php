<?php

namespace FSR\Notifications\Cso;

use FSR\Volunteer;
use FSR\Custom\CarbonFix;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CsoNewComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $listing_offer_id;

    /**
     * Create a new notification instance.
     * @param int $listing_offer_id
     * @return void
     */
    public function __construct(int $listing_offer_id)
    {
        $this->listing_offer_id = $listing_offer_id;
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
        return (new MailMessage)
                    ->subject('Нов коментар на прифатена донација!')
                    ->line('Имате нов коментар на вашата прифатена донација.')
                    ->line('Кликнете подолу за да го прочитате:')
                    ->action('Кон коментарот', route('cso.accepted_listings.single_accepted_listing', $this->listing_offer_id) . '#comments');
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
