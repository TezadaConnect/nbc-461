<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnNotification extends Notification
{
    use Queueable;
    private $notificationData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notificationData)
    {
        $this->notificationData = $notificationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        if($this->notificationData['accomplishment_type'] == 'individual')
            return (new MailMessage)
                    ->subject('PUP eQAR | Returned Accomplishment Notification')
                    ->greeting('Hello '.$this->notificationData['receiver'].'!')
                    ->line('Your accomplishment on '.$this->notificationData['category_name'].' has been returned by '.$this->notificationData['sender'].'.')
                    ->line('To edit:')
                    ->line('1. Click the button "View Accomplishment" in this message.')
                    ->line('2. Read the remarks displayed in the editing page.')
                    ->line('2. Save and click Submit to resubmit the accomplishment.')
                    ->action('View Accomplishment', $this->notificationData['url'])
                    ->line('Thank you for using our application!');
        elseif($this->notificationData['accomplishment_type'] == 'department')
            return (new MailMessage)
                    ->subject('PUP eQAR | Returned Department-Wide Accomplishment Notification')
                    ->greeting('Hello '.$this->notificationData['receiver'].'!')
                    ->line('The accomplishment of '.$this->notificationData['department_name'].' on '.$this->notificationData['category_name'].' has been returned by '.$this->notificationData['sender'].'.')
                    ->line('To edit:')
                    ->line('1. Click the button "View Accomplishment" in this message.')
                    ->line('2. Read the remarks displayed in the editing page.')
                    ->line('2. Save and click Submit to resubmit the accomplishment.')
                    ->action('View Accomplishment', $this->notificationData['url'])
                    ->line('Thank you for using our application!');

        elseif($this->notificationData['accomplishment_type'] == 'college')
            return (new MailMessage)
                    ->subject('PUP eQAR | Returned College-Wide Accomplishment Notification')
                    ->greeting('Hello '.$this->notificationData['receiver'].'!')
                    ->line('The accomplishment of '.$this->notificationData['college_name'].' on '.$this->notificationData['category_name'].' has been returned by '.$this->notificationData['sender'].'.')
                    ->line('To edit:')
                    ->line('1. Click the button "View Accomplishment" in this message.')
                    ->line('2. Read the remarks displayed in the editing page.')
                    ->line('2. Save and click Submit to resubmit the accomplishment.')
                    ->action('View Accomplishment', $this->notificationData['url'])
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if($this->notificationData['accomplishment_type'] == 'individual')
            return [
                'type' => 'returned',
                'sender' => $this->notificationData['sender'],
                'url' => urlencode($this->notificationData['url']),
                'category_name' => $this->notificationData['category_name'],
                'user_id' => $this->notificationData['user_id'],
                'reason' => $this->notificationData['reason'],
                'accomplishment_type' => $this->notificationData['accomplishment_type'],
                'date' => $this->notificationData['date']

            ];
        elseif($this->notificationData['accomplishment_type'] == 'department')
            return [
                'type' => 'returned',
                'sender' => $this->notificationData['sender'],
                'url' => urlencode($this->notificationData['url']),
                'category_name' => $this->notificationData['category_name'],
                'user_id' => $this->notificationData['user_id'],
                'reason' => $this->notificationData['reason'],
                'accomplishment_type' => $this->notificationData['accomplishment_type'],
                'date' => $this->notificationData['date'],
                'department_name' => $this->notificationData['department_name']
            ];
        elseif($this->notificationData['accomplishment_type'] == 'college')
            return [
                'type' => 'returned',
                'sender' => $this->notificationData['sender'],
                'url' => urlencode($this->notificationData['url']),
                'category_name' => $this->notificationData['category_name'],
                'user_id' => $this->notificationData['user_id'],
                'reason' => $this->notificationData['reason'],
                'accomplishment_type' => $this->notificationData['accomplishment_type'],
                'date' => $this->notificationData['date'],
                'department_name' => $this->notificationData['college_name']
            ];
    }
}
