<?php

namespace NotificationChannels\Interfax\Exceptions;

use Exception;
use NotificationChannels\Interfax\InterfaxMessage;

class CouldNotSendNotification extends Exception
{
    protected $interfaxMessage;
    protected $responseAttributes;

    public function __construct($message, $code, Exception $previous = null, InterfaxMessage $interfaxMessage, array $responseAttributes)
    {
        parent::__construct($message, $code, $previous);

        $this->interfaxMessage = $interfaxMessage;
        $this->responseAttributes = $responseAttributes;
    }

    public static function serviceRespondedWithAnError($message, $responseAttributes, string $exceptionMessage = 'The fax failed to send via InterFAX.')
    {
        return new static($exceptionMessage, $responseAttributes['status'], null, $message, $responseAttributes);
    }

    public function getUser()
    {
        return $this->interfaxMessage->user;
    }

    public function getMetadata()
    {
        return $this->interfaxMessage->metadata;
    }

    public function getAttributes()
    {
        return $this->responseAttributes;
    }
}
