<?php

namespace NotificationChannels\Interfax\Exceptions;

use Exception;
use Throwable;
use NotificationChannels\Interfax\InterfaxMessage;

class CouldNotSendNotification extends Exception
{
    protected InterfaxMessage $interfaxMessage;

    /** @var array<string, mixed> */
    protected array $responseAttributes;

    /**
     * @param  InterfaxMessage $message
     * @param  array<string, mixed> $responseAttributes
     * @param  string          $exceptionMessage
     * @return CouldNotSendNotification
     */
    public static function serviceRespondedWithAnError(InterfaxMessage $message, array $responseAttributes, string $exceptionMessage = 'The fax failed to send via InterFAX.')
    {
        $exception = new self($exceptionMessage, $responseAttributes['status'], null);
        $exception->setInterfaxMessage($message);
        $exception->setResponseAttributes($responseAttributes);

        return $exception;
    }

    /**
     * @param InterfaxMessage $interfaxMessage
     */
    public function setInterfaxMessage(InterfaxMessage $interfaxMessage): void
    {
        $this->interfaxMessage = $interfaxMessage;
    }

    /**
     * @param array<string, mixed> $responseAttributes
     */
    public function setResponseAttributes(array $responseAttributes): void
    {
        $this->responseAttributes = $responseAttributes;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->interfaxMessage->user;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->interfaxMessage->metadata;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->responseAttributes;
    }
}
