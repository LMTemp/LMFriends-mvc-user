<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Adapter;

use Laminas\EventManager\EventManager;
use PHPUnit\Framework\TestCase;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainServiceFactory;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainService;
use LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter;

class AdapterChainServiceFactoryTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AdapterChainServiceFactory
     */
    protected $factory;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;


    protected $serviceLocatorArray;

    public function helperServiceLocator($index)
    {
        return $this->serviceLocatorArray[$index];
    }

    /**
     * Prepare the object to be tested.
     */
    protected function setUp(): void
    {
        $this->serviceLocator = $this->createMock(ServiceLocatorInterface::class);

        $this->options = $this->getMockBuilder(ModuleOptions::class)
            ->disableOriginalConstructor()
            ->getMock();



        $this->serviceLocator
            ->method('get')
            ->willReturnCallback([$this, 'helperServiceLocator']);

        $this->eventManager = $this->createMock(EventManager::class);

        $this->serviceLocatorArray = [
            ModuleOptions::class => $this->options,
            'EventManager' => $this->eventManager
        ];


        $this->factory = new AdapterChainServiceFactory();
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainServiceFactory::__invoke
     */
    public function testCreateService()
    {
        $adapter = [
            'adapter1'=> $this->createMock(
                AbstractAdapter::class,
                ['authenticate', 'logout']
            ),
            'adapter2'=> $this->createMock(
                AbstractAdapter::class,
                ['authenticate', 'logout']
            )
        ];
        $adapterNames = [100 =>'adapter1', 200 =>'adapter2'];

        $this->serviceLocatorArray = array_merge($this->serviceLocatorArray, $adapter);

        $this->options->expects(static::once())
                      ->method('getAuthAdapters')
                      ->willReturn($adapterNames);

        $adapterChain = $this->factory->__invoke($this->serviceLocator, AdapterChainService::class);

        static::assertInstanceOf(AdapterChainService::class, $adapterChain);
    }
}
