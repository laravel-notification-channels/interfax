# InterFAX notification channel for Laravel 9.x, 10.x

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/interfax.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/interfax)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/laravel-notification-channels/interfax/main.svg?style=flat-square)](https://travis-ci.org/laravel-notification-channels/interfax)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/interfax.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/interfax)

This package makes it easy to send notifications using [InterFAX](https://interfax.net) with Laravel 9.x and 10.x.

## Contents

- [Installation](#installation)
  - [Setting up the InterFAX service](#setting-up-the-InterFAX-service)
- [Usage](#usage)
  - [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install this package via composer:

```bash
composer require laravel-notification-channels/interfax
```

The service provider gets loaded automatically.

### Setting up the InterFAX service

This channel will use your InterFAX username and password. To use the channel, add this to your `config/services.php` file:

```php
...
'interfax' => [
    'username' => env('INTERFAX_USERNAME'),
    'password' => env('INTERFAX_PASSWORD'),
    'pci'      => env('INTERFAX_PCI', false),
    'interval' => 15,
    'chunk_size' => null,
],
...
```

This will load your InterFAX credentials from the `.env` file. If your requests must be PCI-DSS-compliant, set `INTERFAX_PCI=true` in your `.env` file.

The `services.interfax.interval` configuration setting is the polling interval, in seconds, for a fax if it is set to check the status until it is complete. This is optional and will default to 15 if left empty. The interval has a minimum of 10 seconds, as the outbound service in the API has a maximum freqncy of 6 requests per minute and can return errors if polled more frequently.

Faxes can sometimes take more than 10 minutes to send, so it is recommended to configure a long-running queue and to push your fax notifications to that queue. More information on configuring long-running queues can be found [here](https://medium.com/@williamvicary/long-running-jobs-with-laravel-horizon-7655e34752f7).

The `services.interfax.chunk_size` configuration setting is the maximum file size before the InterFAX core SDK starts chunking files. The default chunk size is 1048576. When chunking, an `\Interfax\Document` object is created, but the `/outbound/documents` endpoint does not exist for the PCI-DSS-compliant API. If `services.interfax.pci` is set to `true`, it is recommended to increase the chunk size to avoid 404 errors.

## Usage

To use this package, you can create a notification class, like `DocumentWasSent` from the example below, in your Laravel application. Make sure to check out [Laravel's documentation](https://laravel.com/docs/master/notifications) for this process.

### Send PDF via fax

```php
<?php
use NotificationChannels\Interfax\InterfaxChannel;
use NotificationChannels\Interfax\InterfaxMessage;
use NotificationChannels\Interfax\Contracts\InterfaxNotificationContract;

class DocumentWasSent extends Notification implements InterfaxNotificationContract
{

    protected $files;

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [InterfaxChannel::class];
    }

    public function toInterfax($notifiable): InterfaxMessage
    {
        return (new InterfaxMessage)
              ->files($this->files);
    }
}
```

The Notifiable model will need to return a destination fax number.

```php
public function routeNotificationForInterfax($notification)
{
    if($this->fax)
        return preg_replace('/[^\d]/', '', $this->fax);

    return null;
}
```

### Available Message methods

`file(string $file)` : Accepts the full path to a single file (full list of supported file types [found here](https://www.interfax.net/en/help/supported_file_types)).  
`files(array $array)` : Accepts an array of file paths.  If overriding the default chunk_size in the config and using an `\Interfax\File` object in the array, use `\NotificationChannels\Interfax\InterfaxFile` instead to automatically set the file's chunk size on initialization.  
`stream(Filestream $stream, string $name)` : Accepts a file stream.  
`addMetadata(array $data)`: Add metadata for logging purposes in case of an error.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email craig.spivack@ivinteractive.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Craig Spivack](https://github.com/iv-craig)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
