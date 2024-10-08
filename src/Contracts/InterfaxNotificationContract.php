<?php

namespace NotificationChannels\Interfax\Contracts;

use NotificationChannels\Interfax\InterfaxMessage;

interface InterfaxNotificationContract
{
    /**
     * @param  mixed $notifiable
     * @return InterfaxMessage
     */
    public function toInterfax($notifiable): InterfaxMessage;
}
