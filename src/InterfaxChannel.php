<?php

namespace NotificationChannels\Interfax;

use Interfax\Client;
use NotificationChannels\Interfax\Contracts\InterfaxNotificationContract;
use NotificationChannels\Interfax\Exceptions\CouldNotSendNotification;

class InterfaxChannel
{
    /** @var \Interfax\Outbound\Fax $fax */
    protected $fax;

    public function __construct(protected Client $client) {}

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \NotificationChannels\Interfax\Contracts\InterfaxNotificationContract  $notification
     *
     * @throws \NotificationChannels\Interfax\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, InterfaxNotificationContract $notification): void
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

                while ($this->getStatus() < 0) {
                    $message->sleep();
                }

                if ($this->getStatus() > 0) {
                    throw CouldNotSendNotification::serviceRespondedWithAnError($message, $this->fax->attributes());
                }
            }
        } catch (\Interfax\Exception\RequestException $e) {
            $exceptionMessage = $e->getMessage().': '.($e->getWrappedException())->getMessage();
            $attributes = $this->fax ? $this->fax->attributes() : ['status' => $e->getStatusCode()];

            throw CouldNotSendNotification::serviceRespondedWithAnError($message, $attributes, $exceptionMessage);
        }
    }

    protected function getStatus(): int
    {
        $fax = $this->fax->refresh();
        return $fax->status;
    }
}
