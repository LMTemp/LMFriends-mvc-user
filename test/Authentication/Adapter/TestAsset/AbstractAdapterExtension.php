<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Adapter\TestAsset;

use Laminas\EventManager\EventInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter;

class AbstractAdapterExtension extends AbstractAdapter
{
    public function authenticate(EventInterface $e)
    {
    }
}
