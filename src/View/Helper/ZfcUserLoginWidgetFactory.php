<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\View;

class ZfcUserLoginWidgetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $viewHelper = new View\Helper\ZfcUserLoginWidget();
        $viewHelper->setViewTemplate($container->get('zfcuser_module_options')->getUserLoginWidgetViewTemplate());
        $viewHelper->setLoginForm($container->get('zfcuser_login_form'));

        return $viewHelper;
    }
}
