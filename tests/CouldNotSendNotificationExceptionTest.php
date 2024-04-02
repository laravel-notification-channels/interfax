<?php

namespace NotificationChannels\Interfax\Test;

use NotificationChannels\Interfax\Exceptions\CouldNotSendNotification;
use NotificationChannels\Interfax\InterfaxMessage;

class CouldNotSendNotificationExceptionTest extends TestCase
{
    protected $message;

    public function setUp(): void
    {
        parent::setUp();
        $this->message = (new InterfaxMessage)
                            ->addMetadata(['key' => 'Some sample metadata.'])
                            ->checkStatus()
                            ->user(new TestNotifiable)
                            ->file('test-file.pdf');
    }

    public function test_get_the_exception_user()
    {
        $exception = CouldNotSendNotification::serviceRespondedWithAnError($this->message, [
            'status' => 500,
            'message' => 'Failed to send.',
        ]);

        $this->assertInstanceOf(TestNotifiable::class, $exception->getUser());
    }

    public function test_get_the_exception_metadata()
    {
        $exception = CouldNotSendNotification::serviceRespondedWithAnError($this->message, [
            'status' => 500,
            'message' => 'Failed to send.',
        ]);

        $this->assertSame('Some sample metadata.', $exception->getMetadata()['key']);
    }

    public function test_get_the_default_exception_message()
    {
        $exception = CouldNotSendNotification::serviceRespondedWithAnError($this->message, [
            'status' => 500,
            'message' => 'Failed to send.',
        ]);

        $this->assertSame('The fax failed to send via InterFAX.', $exception->getMessage());
    }

    public function test_get_a_custom_exception_message()
    {
        $exceptionMessage = 'This is a test.';

        $exception = CouldNotSendNotification::serviceRespondedWithAnError($this->message, [
            'status' => 500,
            'message' => 'Failed to send.',
        ], $exceptionMessage);

        $this->assertSame($exceptionMessage, $exception->getMessage());
    }

    public function test_get_the_exception_attributes()
    {
        $exception = CouldNotSendNotification::serviceRespondedWithAnError($this->message, [
            'status' => 500,
            'message' => 'Failed to send.',
        ]);

        $this->assertSame([
            'status' => 500,
            'message' => 'Failed to send.',
        ], $exception->getAttributes());
    }
}
