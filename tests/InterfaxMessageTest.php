<?php

namespace NotificationChannels\Interfax\Test;

use NotificationChannels\Interfax\InterfaxMessage;

class InterfaxMessageTest extends TestCase
{
    /** @test */
    public function it_should_check_the_status_via_refresh()
    {
        $message = (new InterfaxMessage)
                        ->checkStatus()
                        ->user(new TestNotifiable)
                        ->file('test-file.pdf');

        $this->assertTrue($message->shouldCheckStatus());
    }

    /** @test */
    public function it_should_not_check_the_status_via_refresh_manual()
    {
        $message = (new InterfaxMessage)
                        ->checkStatus(false)
                        ->user(new TestNotifiable)
                        ->file('test-file.pdf');

        $this->assertFalse($message->shouldCheckStatus());
    }

    /** @test */
    public function it_should_not_check_the_status_via_refresh_default()
    {
        $message = (new InterfaxMessage)
                        ->user(new TestNotifiable)
                        ->file('test-file.pdf');

        $this->assertFalse($message->shouldCheckStatus());
    }

    /** @test */
    public function it_should_set_the_file_chunk_size_filename()
    {
        $this->increaseChunkSize();

        $message = (new InterfaxMessage)
                        ->user(new TestNotifiable)
                        ->file(__DIR__.'/resources/test.pdf');

        $files = $message->makeFiles();
        $delivery = new \Interfax\Outbound\Delivery(new \Interfax\Client, ['faxNumber'=>'0000000000', 'files'=>$files]);

        $this->assertSame($this->chunkSize, $this->getChunkSize($delivery));
    }

    /** @test */
    public function it_should_set_the_file_chunk_size_file_array()
    {
        $this->increaseChunkSize();

        $message = (new InterfaxMessage)
                        ->user(new TestNotifiable)
                        ->files([['location' => __DIR__.'/resources/test.pdf']]);

        $files = $message->makeFiles();
        $delivery = new \Interfax\Outbound\Delivery(new \Interfax\Client, ['faxNumber'=>'0000000000', 'files'=>$files]);

        $this->assertSame($this->chunkSize, $this->getChunkSize($delivery));
    }

    /** @test */
    public function it_should_set_the_file_chunk_size_file_object()
    {
        $this->increaseChunkSize();
        $client = new \Interfax\Client;

        $file = new \NotificationChannels\Interfax\InterfaxFile($client, __DIR__.'/resources/test.pdf');

        $message = (new InterfaxMessage)
                        ->user(new TestNotifiable)
                        ->files([$file]);

        $files = $message->makeFiles();
        $delivery = new \Interfax\Outbound\Delivery($client, ['faxNumber'=>'0000000000', 'files'=>$files]);

        $this->assertSame($this->chunkSize, $this->getChunkSize($delivery));
    }

    protected function getChunkSize($delivery)
    {
        $deliveryReflection = new \ReflectionClass($delivery);
        $filesProperty = $deliveryReflection->getProperty('files');
        $filesProperty->setAccessible(true);

        $files = $filesProperty->getValue($delivery);

        $fileReflection = new \ReflectionClass($files[0]);

        if ($files[0] instanceof \NotificationChannels\Interfax\InterfaxFile) {
            $fileReflection = $fileReflection->getParentClass();
        }

        $chunkProperty = $fileReflection->getProperty('chunk_size');
        $chunkProperty->setAccessible(true);

        return $chunkProperty->getValue($files[0]);
    }
}
