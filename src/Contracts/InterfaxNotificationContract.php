<?php

namespace NotificationChannels\Interfax\Contracts;

use NotificationChannels\Interfax\InterfaxMessage;

interface InterfaxNotificationContract
{
    public function toInterfax($notifiable): InterfaxMessage;
}
