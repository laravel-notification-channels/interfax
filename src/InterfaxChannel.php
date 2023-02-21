<?php

namespace NotificationChannels\Interfax;

use Interfax\Client;
use NotificationChannels\Interfax\Contracts\InterfaxNotificationContract;
use NotificationChannels\Interfax\Exceptions\CouldNotSendNotification;

class InterfaxChannel
{
    protected $client;
    protected $fax;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \NotificationChannels\Interfax\Contracts\InterfaxNotificationContract  $notification
     *
     * @throws \NotificationChannels\Interfax\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, InterfaxNotificationContract $notification)
    {
        if (! $faxNumber = $notifiable->routeNotificationFor('interfax')) {
            return;
        }

        $message = $notification->toInterfax($notifiable);

        try {
            $this->fax = $this->client->deliver([
                'faxNumber' => $faxNumber,
                'files' => $message->makeFiles(),
            ]);

            if ($message->shouldCheckStatus()) {
                $message->sleep();

                while ($this->fax->refresh()->status < 0) {
                    $message->sleep();
                }

                if ($this->fax->refresh()->status > 0) {
                    throw CouldNotSendNotification::serviceRespondedWithAnError($message, $this->fax->attributes());
                }
            }
        } catch (\Interfax\Exception\RequestException $e) {
            $exceptionMessage = $e->getMessage().': '.($e->getWrappedException())->getMessage();
            $attributes = $this->fax ? $this->fax->attributes() : ['status' => $e->getStatusCode()];

            throw CouldNotSendNotification::serviceRespondedWithAnError($message, $attributes, $exceptionMessage);
        }
    }
}
