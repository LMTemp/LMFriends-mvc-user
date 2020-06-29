<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Service;

use Laminas\Form\FormInterface;
use LaminasFriends\Mvc\User\Options\UserServiceOptionsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Service\UserService;
use Laminas\Crypt\Password\Bcrypt;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;
use LaminasFriends\Mvc\User\Entity\UserEntity;
use Laminas\EventManager\EventManager;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Authentication\AuthenticationService;

class UserServiceTest extends TestCase
{
    /** @var UserService */
    protected UserService $service;
    /** @var UserServiceOptionsInterface|MockObject */
    protected $options;
    /** @var HydratorInterface|MockObject */
    protected $formHydrator;
    /** @var EventManager|MockObject */
    protected $eventManager;
    /** @var UserMapperInterface|MockObject */
    protected $mapper;
    /** @var AuthenticationService|MockObject */
    protected $authService;
    /** @var FormInterface|MockObject */
    protected $registerForm;

    /**
     * @throws RuntimeException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->eventManager = $this->createMock(EventManager::class);
        $this->options = $this->createMock(UserServiceOptionsInterface::class);
        $this->mapper = $this->createMock(UserMapperInterface::class);
        $this->authService = $this->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new UserService(
            $this->options,
            $this->mapper,
            $this->authService
        );
        $this->service->setEventManager($this->eventManager);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::register
     */
    public function testRegister()
    {
        $formUser = new UserEntity();
        $formUser->setUsername('MvcUser');
        $formUser->setDisplayName('Mvc UserService');
        $formUser->setState(0);

        $user = $this->createMock(UserEntity::class);
        $user->expects(static::once())
            ->method('setPassword');
        $user->expects(static::once())
            ->method('getPassword');


        $this->options->expects(static::once())
            ->method('getPasswordCost')
            ->willReturn(4);

        $this->eventManager->expects(static::exactly(2))
            ->method('trigger');

        $this->mapper->expects(static::once())
            ->method('insert')
            ->with($user)
            ->willReturn($user);

        $result = $this->service->register($user);

        static::assertSame($user, $result);
        static::assertEquals(0, $user->getState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::register
     */
    public function testRegisterWithDefaultUserStateOfZero()
    {
        $formUser = new UserEntity();
        $formUser->setUsername('MvcUser');
        $formUser->setDisplayName('Mvc UserService');
        $formUser->setState(0);

        $user = $this->createMock(UserEntity::class);
        $user->expects(static::once())
             ->method('setPassword');
        $user->expects(static::once())
             ->method('getPassword');


        $this->options->expects(static::once())
                      ->method('getPasswordCost')
                      ->willReturn(4);

        $this->eventManager->expects(static::exactly(2))
                           ->method('trigger');

        $this->mapper->expects(static::once())
                     ->method('insert')
                     ->with($user)
                     ->willReturn($user);

        $result = $this->service->register($user);

        static::assertSame($user, $result);
        static::assertEquals(0, $user->getState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::changePassword
     */
    public function testChangePasswordWithWrongOldPassword()
    {
        $data = ['newCredential' => 'mvcUser', 'credential' => 'mvcUserOld'];

        $this->options
             ->method('getPasswordCost')
             ->willReturn(4);

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());

        $user = $this->createMock(UserEntity::class);
        $user->method('getPassword')
             ->willReturn($bcrypt->create('wrongPassword'));

        $this->authService->method('getIdentity')
            ->willReturn($user);

        $result = $this->service->changePassword($data);
        static::assertFalse($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::changePassword
     */
    public function testChangePassword()
    {
        $data = ['newCredential' => 'mvcUser', 'credential' => 'mvcUserOld'];

        $this->options
             ->method('getPasswordCost')
             ->willReturn(4);

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());

        $user = $this->createMock(UserEntity::class);
        $user
             ->method('getPassword')
             ->willReturn($bcrypt->create($data['credential']));
        $user
             ->method('setPassword');

        $this->authService
             ->method('getIdentity')
             ->willReturn($user);

        $this->eventManager->expects(static::exactly(2))
             ->method('trigger');

        $this->mapper->expects(static::once())
             ->method('update')
             ->with($user);

        $result = $this->service->changePassword($data);
        static::assertTrue($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::changeEmail
     */
    public function testChangeEmail()
    {
        $data = ['credential' => 'mvcUser', 'newIdentity' => 'mvcUser@mvcUser.com'];

        $this->options
             ->method('getPasswordCost')
             ->willReturn(4);

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());

        $user = $this->createMock(UserEntity::class);
        $user
             ->method('getPassword')
             ->willReturn($bcrypt->create($data['credential']));
        $user
             ->method('setEmail')
             ->with($data['newIdentity']);

        $this->authService
             ->method('getIdentity')
             ->willReturn($user);

        $this->eventManager->expects(static::exactly(2))
             ->method('trigger');

        $this->mapper->expects(static::once())
             ->method('update')
             ->with($user);

        $result = $this->service->changeEmail($data);
        static::assertTrue($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::changeEmail
     */
    public function testChangeEmailWithWrongPassword()
    {
        $data = ['credential' => 'mvcUserOld'];

        $this->options
             ->method('getPasswordCost')
             ->willReturn(4);

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());

        $user = $this->createMock(UserEntity::class);
        $user
             ->method('getPassword')
             ->willReturn($bcrypt->create('wrongPassword'));

        $this->authService
             ->method('getIdentity')
             ->willReturn($user);

        $result = $this->service->changeEmail($data);
        static::assertFalse($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getAuthService
     */
    public function testGetAuthService()
    {
        static::assertSame($this->authService, $this->service->getAuthService());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getOptions
     */
    public function testGetOptions()
    {
        static::assertInstanceOf(UserServiceOptionsInterface::class, $this->service->getOptions());
    }
}
