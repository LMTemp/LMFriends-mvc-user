<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\View;

/**
 * Class MvcUserLoginWidgetFactory
 */
class MvcUserLoginWidgetFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return MvcUserLoginWidget
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MvcUserLoginWidget(
            $container->get(Module::MVC_USER_FORM_LOGIN),
            $container->get(ModuleOptions::class)->getUserLoginWidgetViewTemplate()
        );
    }
}
