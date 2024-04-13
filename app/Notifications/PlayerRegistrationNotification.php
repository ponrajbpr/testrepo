<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MAssociation;

class PlayerRegistrationNotification extends Notification
{
    use Queueable;

    public $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        // Get the state email based on the user's state
        // $stateEmail = MAssociation::join('state_details', 'state_details.association_id', '=', 'massociations.id')->where('id', $this->details->association)->value('state_details.office_email');
        return (new MailMessage)
        // ->to($stateEmail)
        ->subject('New Player Registration')
        ->line('A new player has been registered in your state:')
        ->line('Name: ' . $this->details->first_name . ' ' . $this->details->last_name)
        ->line('Email: ' . $this->details->email)
        ->line('Please verify the player.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
