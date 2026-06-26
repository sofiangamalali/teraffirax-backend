<?php

namespace App\Enums;

enum IntegrationTypeEnum: string
{
    case CHANNEL = 'channel';
    case BOT = 'bot';
    case GROUP = 'group';
    case PROFILE = 'profile';
    case PAGE = 'page';
}
