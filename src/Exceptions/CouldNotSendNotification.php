<?php

namespace NotificationChannels\Interfax\Exceptions;

use Exception;
use NotificationChannels\Interfax\InterfaxMessage;

class CouldNotSendNotification extends Exception
{
    protected InterfaxMessage $interfaxMessage;

    /** @var array<string, mixed> */
    protected array $responseAttributes;

    /**
     * @param string          $message
     * @param int             $code
     * @param Exception|null  $previous
     * @param InterfaxMessage $interfaxMessage
     * @param array<string, mixed>    $responseAttributes
     */
    final public function __construct(string $message, int $code, Exception $previous = null, InterfaxMessage $interfaxMessage, array $responseAttributes)
    {
        parent::__construct($message, $code, $previous);

        $this->interfaxMessage = $interfaxMessage;
        $this->responseAttributes = $responseAttributes;
    }

    /**
     * @param  InterfaxMessage $message
     * @param  array<string, mixed> $responseAttributes
     * @param  string          $exceptionMessage
     * @return CouldNotSendNotification
     */
    public static function serviceRespondedWithAnError(InterfaxMessage $message, array $responseAttributes, string $exceptionMessage = 'The fax failed to send via InterFAX.')
    {
        return new static($exceptionMessage, $responseAttributes['status'], null, $message, $responseAttributes);
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
