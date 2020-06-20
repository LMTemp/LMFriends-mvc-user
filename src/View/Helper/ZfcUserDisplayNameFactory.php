<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\View;

class ZfcUserDisplayNameFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $viewHelper = new View\Helper\ZfcUserDisplayName();
        $viewHelper->setAuthService($container->get('zfcuser_auth_service'));

        return $viewHelper;
    }
}
