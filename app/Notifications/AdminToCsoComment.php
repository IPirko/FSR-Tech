<?php

namespace FSR\Notifications;

use FSR\Volunteer;
use FSR\Cso;
use FSR\Donor;
use FSR\Admin;
use FSR\Custom\CarbonFix;
use FSR\Custom\Methods;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminToCsoComment extends Notification implements ShouldQueue
{
    use Queueable;

    private $listing_offer;
    private $comment_text;
    private $comments;
    private $comments_count;
    private $admin;

    /**
     * Create a new notification instance.
     * @param $listing_offer_id
     * @param string $comment_text
     * @return void
     */
    public function __construct($listing_offer, string $comment_text, $comments, $admin)
    {
        $this->listing_offer = $listing_offer;
        $this->comment_text = $comment_text;
        $this->comments = $comments;
        $this->comments_count = $comments->count();
        $this->admin = $admin;
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
        $messages = (new MailMessage)
                    ->subject('[Сите Сити] Додаден е коментар на вашата донација.')
                    ->line('Адмнистраторот на СитеСити остави коментар на вашата донација')
                    ->line('<div style="margin-bottom: 5px; color: black !important;">' .
                              '<div style="float:left;">' .
                                '<img style="width:60px; height:60px;" src="' . Methods::get_user_image_url($this->admin) . '">' .
                              '</div>' .
                              '<div style="overflow: auto; margin-left: 70px; background-color: #ddd; border-radius: 10px; color: black; font-weight: bold;">' .
                                '<div style="font-size: small; font-weight: bold; margin:5px;">' .
                                  $this->admin->first_name . ' ' .
                                  $this->admin->last_name .
                                  ' (администратор)' .
                                '</div>' .
                                '<hr style="margin: 0px;">' .
                                '<div style="font-size: medium; font-weight: normal !important; margin:5px;">' .
                                  $this->comment_text .
                                '</div>' .
                              '</div>' .
                            '</div>');
        $count = 1;

        if ($this->comments_count > 0) {
            $messages->line('Претходни коментари:');
        }

        foreach ($this->comments->sortByDesc('id') as $comment) {
            if ($count < 4) {
                if ($comment->sender_type == 'donor') {
                    $user = Donor::where('id', $comment->user_id)->first();
                    $type = 'донатор';
                } elseif ($comment->sender_type == 'cso') {
                    $user = Cso::where('id', $comment->user_id)->first();
                    $type = 'примател';
                } elseif ($comment->sender_type == 'admin') {
                    $user = Admin::where('id', $comment->user_id)->first();
                    $type = 'администратор';
                }
                $messages->line('<div style="margin-bottom:5px;">' .
                                  '<div style="float:left;">' .
                                    '<img style="width:60px; height:60px;" src="' . Methods::get_user_image_url($user) . '">' .
                                  '</div>' .
                                  '<div style="overflow: auto; margin-left: 70px; background-color: ' . (($comment->sender_type == 'cso') ? '#0084ff' : '#ddd') . '; border-radius: 10px; color: black; font-weight: bold;">' .
                                    '<div style="font-size: small; font-weight: bold; margin:5px;' . (($comment->sender_type == 'cso') ? ' color: white !important;' : ' color: black !important;') . '">' . //#0084ff
                                      $user->first_name . ' ' .
                                      $user->last_name .
                                      (($comment->sender_type == 'admin') ? '' : ' - ' . $user->organization->name) .
                                      ' (' . $type . ')' .
                                    '</div>' .
                                    '<hr style="margin: 0px;">' .
                                    '<div style="font-size: medium; font-weight: normal !important; margin:5px;' . (($comment->sender_type == 'cso') ? ' color: white !important;' : ' color: black !important;') . '">' .
                                      $comment->text .
                                    '</div>' .
                                  '</div>' .
                                '</div>');
                $count++;
            }
        }
        if ($this->comments_count > 3) {
            $comments_left = $this->comments_count-3;
            $messages->line('<a href="' . route('cso.accepted_listings.single_accepted_listing', $this->listing_offer->id) . '#comments"><div style="text-align: center;font-size: 0.8em;">(Уште ' . $comments_left . ' коментари)</div></a>');
        }

        $messages->line('<hr>');
        $messages->line('Информации за донацијата:');
        $messages->line('Производ: ' . $this->listing_offer->listing->product->name);
        $messages->line('Kоличина: ' . $this->listing_offer->quantity . ' ' . $this->listing_offer->listing->quantity_type->description);
        $messages->line('Ви благодариме што го поддржувате нашиот труд да го намалиме отпадот од храна и недостаток на храна во Македонија!');
        $messages->action('Кон коментарот', route('cso.accepted_listings.single_accepted_listing', $this->listing_offer->id) . '#comments');

        return $messages;
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
