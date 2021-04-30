<?php

namespace NotificationChannels\Interfax;

use Illuminate\Notifications\Notification;
use Interfax\Client;
use NotificationChannels\Interfax\Exceptions\CouldNotSendNotification;

class InterfaxChannel
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\Interfax\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $faxNumber = $notifiable->routeNotificationFor('interfax')) {
            return;
        }

        $message = $notification->toInterfax($notifiable);

        try {
            $fax = $this->client->deliver([
                'faxNumber' => $faxNumber,
                'files' => $message->makeFiles(),
            ]);

            if ($message->shouldCheckStatus()) {
                while ($fax->refresh()->status < 0) {
                    sleep(config('services.interfax.interval', 15));
                }

                if ($fax->refresh()->status > 0) {
                    throw CouldNotSendNotification::serviceRespondedWithAnError($message, $fax->refresh()->attributes());
                }
            }
        } catch (\Interfax\Exception\RequestException $e) {
            $exceptionMessage = $e->getMessage().': '.($e->getWrappedException())->getMessage();
            throw CouldNotSendNotification::serviceRespondedWithAnError($message, $fax->refresh()->attributes(), $exceptionMessage);
        }
    }
}
