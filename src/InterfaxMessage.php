<?php

namespace NotificationChannels\Interfax;

use Illuminate\Support\Arr;

class InterfaxMessage
{
    /** @var array<int, InterfaxFile|string|array<string, mixed>> */
    protected array $files;

    /** @var resource|false $stream */
    protected $stream;
    protected string $filename;
    protected string $method;
    protected bool $statusCheck = false;
    public mixed $user;

    /** @var array<string, mixed> */
    public array $metadata = [];

    const FILES = 'files';
    const STREAM = 'stream';

    const POLLING_INTERVAL_DEFAULT = 15;
    const POLLING_INTERVAL_MINIMUM = 10;

    protected static int $DEFAULT_CHUNK_SIZE = 1048576;

    public function file(string $file): InterfaxMessage
    {
        $this->files = Arr::wrap($file);
        $this->method = static::FILES;

        return $this;
    }

    /**
     * @param  array<int, array<string, mixed>>  $files
     * @return InterfaxMessage
     */
    public function files(array $files): InterfaxMessage
    {
        $this->files = $files;
        $this->method = static::FILES;

        return $this;
    }

    /**
     * @param  resource|false $stream
     * @param  string $filename
     * @return InterfaxMessage
     */
    public function stream($stream, string $filename): InterfaxMessage
    {
        $this->stream = $stream;
        $this->filename = $filename;
        $this->method = static::STREAM;

        return $this;
    }

    public function checkStatus(bool $shouldCheck = true): InterfaxMessage
    {
        $this->statusCheck = $shouldCheck;
        return $this;
    }

    public function shouldCheckStatus(): bool
    {
        return $this->statusCheck;
    }

    /**
     * Set a user who can be notified in case the fax fails to send.
     *
     * @param  mixed  $notifiable  The user to notify
     * @return InterfaxMessage
     */
    public function user($notifiable): InterfaxMessage
    {
        $this->user = $notifiable;
        return $this;
    }

    /**
     * Add metadata to the message for logging purposes.
     *
     * @param  array<string, mixed>  $data  The data to add to the metadata array
     * @return InterfaxMessage
     */
    public function addMetadata(array $data): InterfaxMessage
    {
        foreach ($data as $key => $value) {
            $this->metadata[$key] = $value;
        }

        return $this;
    }

    /**
     * @return array<int, InterfaxFile|array<string, mixed>>|array<int, array<int, mixed>>
     */
    public function makeFiles(): array
    {
        if ($this->method === static::STREAM) {
            return [
                [
                    $this->stream,
                    [
                        'name' => $this->filename,
                        'mime_type' => app('files')->mimeType($this->filename),
                        'chunk_size' => config('services.interfax.chunk_size', static::$DEFAULT_CHUNK_SIZE),
                    ],
                ],
            ];
        }

        return array_map(fn ($file) => static::setChunkSize($file), $this->files);
    }

    public function sleep(): void
    {
        $interval = config('services.interfax.interval', static::POLLING_INTERVAL_DEFAULT);
        sleep(max($interval, static::POLLING_INTERVAL_MINIMUM));
    }

    /**
     * @param InterfaxFile|string|array<string, mixed> $file
     * @return InterfaxFile|array<string, mixed>
     */
    protected static function setChunkSize(InterfaxFile|string|array $file): InterfaxFile|array
    {
        $chunk_size = config('services.interfax.chunk_size', static::$DEFAULT_CHUNK_SIZE);

        if (is_string($file)) {
            return [
                'location' => $file,
                'params' => [
                    'chunk_size' => $chunk_size,
                ],
            ];
        } elseif (is_array($file)) {
            $file['params']['chunk_size'] = $chunk_size;

            return $file;
        } else {
            return $file;
        }
    }
}
