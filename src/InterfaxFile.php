<?php

namespace NotificationChannels\Interfax;

class InterfaxFile extends \Interfax\File
{
    /**
     * File constructor.
     *
     * @param  resource|string $location
     * @param  array<string, mixed>  $params
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\Interfax\Client $client, $location, $params = [], \Interfax\GenericFactory $factory = null)
    {
        if ($chunkSize = config('services.interfax.chunk_size')) {
            static::$DEFAULT_CHUNK_SIZE = $chunkSize;
        }

        parent::__construct($client, $location, $params, $factory);
    }
}
