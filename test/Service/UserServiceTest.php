<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Service;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Service\UserService as Service;
use Laminas\Crypt\Password\Bcrypt;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Form\ChangePasswordForm;
use LaminasFriends\Mvc\User\Form\RegisterForm;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;
use LaminasFriends\Mvc\User\Entity\UserEntity;
use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\EventManager;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Authentication\AuthenticationService;

class UserServiceTest extends TestCase
{
    protected $service;

    protected $options;

    protected $serviceManager;

    protected $formHydrator;

    protected $eventManager;

    protected $mapper;

    protected $authService;

    protected function setUp(): void
    {
        $service = new Service();
        $this->service = $service;

        $options = $this->createMock(ModuleOptions::class);
        $this->options = $options;

        $serviceManager = $this->createMock(ServiceManager::class);
        $this->serviceManager = $serviceManager;

        $eventManager = $this->createMock(EventManager::class);
        $this->eventManager = $eventManager;

        $formHydrator = $this->createMock(HydratorInterface::class);
        $this->formHydrator = $formHydrator;

        $mapper = $this->createMock(UserMapperInterface::class);
        $this->mapper = $mapper;

        $authService = $this->getMockBuilder(AuthenticationService::class)->disableOriginalConstructor()->getMock();
        $this->authService = $authService;

        $service->setOptions($options);
        $service->setServiceManager($serviceManager);
        $service->setFormHydrator($formHydrator);
        $service->setEventManager($eventManager);
        $service->setUserMapper($mapper);
        $service->setAuthService($authService);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::register
     */
    public function testRegisterWithInvalidForm()
    {
        $expectArray = ['username' => 'ZfcUser'];

        $this->options->expects(static::once())
                      ->method('getUserEntityClass')
                      ->willReturn(UserEntity::class);

        $registerForm = $this->getMockBuilder(RegisterForm::class)->disableOriginalConstructor()->getMock();
        $registerForm->expects(static::once())
                     ->method('setHydrator');
        $registerForm->expects(static::once())
                     ->method('bind');
        $registerForm->expects(static::once())
                     ->method('setData')
                     ->with($expectArray);
        $registerForm->expects(static::once())
                     ->method('isValid')
                     ->willReturn(false);

        $this->service->setRegisterForm($registerForm);

        $result = $this->service->register($expectArray);

        static::assertFalse($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::register
     */
    public function testRegisterWithUsernameAndDisplayNameUserStateDisabled()
    {
        $expectArray = ['username' => 'ZfcUser', 'display_name' => 'Zfc UserService'];

        $user = $this->createMock(UserEntity::class);
        $user->expects(static::once())
             ->method('setPassword');
        $user->expects(static::once())
             ->method('getPassword');
        $user->expects(static::once())
             ->method('setUsername')
             ->with('ZfcUser');
        $user->expects(static::once())
             ->method('setDisplayName')
             ->with('Zfc UserService');
        $user->expects(static::once())
             ->method('setState')
             ->with(1);

        $this->options->expects(static::once())
                      ->method('getUserEntityClass')
                      ->willReturn(UserEntity::class);
        $this->options->expects(static::once())
                      ->method('getPasswordCost')
                      ->willReturn(4);
        $this->options->expects(static::once())
                      ->method('getEnableUsername')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getEnableDisplayName')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getEnableUserState')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getDefaultUserState')
                      ->willReturn(1);

        $registerForm = $this->getMockBuilder(RegisterForm::class)->disableOriginalConstructor()->getMock();
        $registerForm->expects(static::once())
                     ->method('setHydrator');
        $registerForm->expects(static::once())
                     ->method('bind');
        $registerForm->expects(static::once())
                     ->method('setData')
                     ->with($expectArray);
        $registerForm->expects(static::once())
                     ->method('getData')
                     ->willReturn($user);
        $registerForm->expects(static::once())
                     ->method('isValid')
                     ->willReturn(true);

        $this->eventManager->expects(static::exactly(2))
                           ->method('trigger');

        $this->mapper->expects(static::once())
                     ->method('insert')
                     ->with($user)
                     ->willReturn($user);

        $this->service->setRegisterForm($registerForm);

        $result = $this->service->register($expectArray);

        static::assertSame($user, $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::register
     */
    public function testRegisterWithDefaultUserStateOfZero()
    {
        $expectArray = ['username' => 'ZfcUser', 'display_name' => 'Zfc UserService'];

        $user = $this->createMock(UserEntity::class);
        $user->expects(static::once())
             ->method('setPassword');
        $user->expects(static::once())
             ->method('getPassword');
        $user->expects(static::once())
             ->method('setUsername')
             ->with('ZfcUser');
        $user->expects(static::once())
             ->method('setDisplayName')
             ->with('Zfc UserService');
        $user->expects(static::once())
             ->method('setState')
             ->with(0);

        $this->options->expects(static::once())
                      ->method('getUserEntityClass')
                      ->willReturn(UserEntity::class);
        $this->options->expects(static::once())
                      ->method('getPasswordCost')
                      ->willReturn(4);
        $this->options->expects(static::once())
                      ->method('getEnableUsername')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getEnableDisplayName')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getEnableUserState')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getDefaultUserState')
                      ->willReturn(0);

        $registerForm = $this->getMockBuilder(RegisterForm::class)->disableOriginalConstructor()->getMock();
        $registerForm->expects(static::once())
                     ->method('setHydrator');
        $registerForm->expects(static::once())
                     ->method('bind');
        $registerForm->expects(static::once())
                     ->method('setData')
                     ->with($expectArray);
        $registerForm->expects(static::once())
                     ->method('getData')
                     ->willReturn($user);
        $registerForm->expects(static::once())
                     ->method('isValid')
                     ->willReturn(true);

        $this->eventManager->expects(static::exactly(2))
                           ->method('trigger');

        $this->mapper->expects(static::once())
                     ->method('insert')
                     ->with($user)
                     ->willReturn($user);

        $this->service->setRegisterForm($registerForm);

        $result = $this->service->register($expectArray);

        static::assertSame($user, $result);
        static::assertEquals(0, $user->getState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::register
     */
    public function testRegisterWithUserStateDisabled()
    {
        $expectArray = ['username' => 'ZfcUser', 'display_name' => 'Zfc UserService'];

        $user = $this->createMock(UserEntity::class);
        $user->expects(static::once())
             ->method('setPassword');
        $user->expects(static::once())
             ->method('getPassword');
        $user->expects(static::once())
             ->method('setUsername')
             ->with('ZfcUser');
        $user->expects(static::once())
             ->method('setDisplayName')
             ->with('Zfc UserService');
        $user->expects(static::never())
             ->method('setState');

        $this->options->expects(static::once())
                      ->method('getUserEntityClass')
                      ->willReturn(UserEntity::class);
        $this->options->expects(static::once())
                      ->method('getPasswordCost')
                      ->willReturn(4);
        $this->options->expects(static::once())
                      ->method('getEnableUsername')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getEnableDisplayName')
                      ->willReturn(true);
        $this->options->expects(static::once())
                      ->method('getEnableUserState')
                      ->willReturn(false);
        $this->options->expects(static::never())
                      ->method('getDefaultUserState');

        $registerForm = $this->getMockBuilder(RegisterForm::class)->disableOriginalConstructor()->getMock();
        $registerForm->expects(static::once())
                     ->method('setHydrator');
        $registerForm->expects(static::once())
                     ->method('bind');
        $registerForm->expects(static::once())
                     ->method('setData')
                     ->with($expectArray);
        $registerForm->expects(static::once())
                     ->method('getData')
                     ->willReturn($user);
        $registerForm->expects(static::once())
                     ->method('isValid')
                     ->willReturn(true);

        $this->eventManager->expects(static::exactly(2))
                           ->method('trigger');

        $this->mapper->expects(static::once())
                     ->method('insert')
                     ->with($user)
                     ->willReturn($user);

        $this->service->setRegisterForm($registerForm);

        $result = $this->service->register($expectArray);

        static::assertSame($user, $result);
        static::assertEquals(0, $user->getState());
    }
    
    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::changePassword
     */
    public function testChangePasswordWithWrongOldPassword()
    {
        $data = ['newCredential' => 'zfcUser', 'credential' => 'zfcUserOld'];

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

        $result = $this->service->changePassword($data);
        static::assertFalse($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::changePassword
     */
    public function testChangePassword()
    {
        $data = ['newCredential' => 'zfcUser', 'credential' => 'zfcUserOld'];

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
        $data = ['credential' => 'zfcUser', 'newIdentity' => 'zfcUser@zfcUser.com'];

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
             ->with('zfcUser@zfcUser.com');

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
        $data = ['credential' => 'zfcUserOld'];

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
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getUserMapper
     */
    public function testGetUserMapper()
    {
        $this->serviceManager->expects(static::once())
                             ->method('get')
                             ->with('zfcuser_user_mapper')
                             ->willReturn($this->mapper);

        $service = new Service();
        $service->setServiceManager($this->serviceManager);
        static::assertInstanceOf(UserMapperInterface::class, $service->getUserMapper());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getUserMapper
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setUserMapper
     */
    public function testSetGetUserMapper()
    {
        static::assertSame($this->mapper, $this->service->getUserMapper());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getAuthService
     */
    public function testGetAuthService()
    {
        $this->serviceManager->expects(static::once())
             ->method('get')
             ->with('zfcuser_auth_service')
             ->willReturn($this->authService);

        $service = new Service();
        $service->setServiceManager($this->serviceManager);
        static::assertInstanceOf(AuthenticationService::class, $service->getAuthService());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getAuthService
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setAuthService
     */
    public function testSetGetAuthService()
    {
        static::assertSame($this->authService, $this->service->getAuthService());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getRegisterForm
     */
    public function testGetRegisterForm()
    {
        $form = $this->getMockBuilder(RegisterForm::class)->disableOriginalConstructor()->getMock();

        $this->serviceManager->expects(static::once())
             ->method('get')
             ->with('zfcuser_register_form')
             ->willReturn($form);

        $service = new Service();
        $service->setServiceManager($this->serviceManager);

        $result = $service->getRegisterForm();

        static::assertInstanceOf(RegisterForm::class, $result);
        static::assertSame($form, $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getRegisterForm
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setRegisterForm
     */
    public function testSetGetRegisterForm()
    {
        $form = $this->getMockBuilder(RegisterForm::class)->disableOriginalConstructor()->getMock();
        $this->service->setRegisterForm($form);

        static::assertSame($form, $this->service->getRegisterForm());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getChangePasswordForm
     */
    public function testGetChangePasswordForm()
    {
        $form = $this->getMockBuilder(ChangePasswordForm::class)->disableOriginalConstructor()->getMock();

        $this->serviceManager->expects(static::once())
             ->method('get')
             ->with('zfcuser_change_password_form')
             ->willReturn($form);

        $service = new Service();
        $service->setServiceManager($this->serviceManager);
        static::assertInstanceOf(ChangePasswordForm::class, $service->getChangePasswordForm());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getChangePasswordForm
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setChangePasswordForm
     */
    public function testSetGetChangePasswordForm()
    {
        $form = $this->getMockBuilder(ChangePasswordForm::class)->disableOriginalConstructor()->getMock();
        $this->service->setChangePasswordForm($form);

        static::assertSame($form, $this->service->getChangePasswordForm());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getOptions
     */
    public function testGetOptions()
    {
        $this->serviceManager->expects(static::once())
             ->method('get')
             ->with('zfcuser_module_options')
             ->willReturn($this->options);

        $service = new Service();
        $service->setServiceManager($this->serviceManager);
        static::assertInstanceOf(ModuleOptions::class, $service->getOptions());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setOptions
     */
    public function testSetOptions()
    {
        static::assertSame($this->options, $this->service->getOptions());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getServiceManager
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setServiceManager
     */
    public function testSetGetServiceManager()
    {
        static::assertSame($this->serviceManager, $this->service->getServiceManager());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::getFormHydrator
     */
    public function testGetFormHydrator()
    {
        $this->serviceManager->expects(static::once())
             ->method('get')
             ->with('zfcuser_register_form_hydrator')
             ->willReturn($this->formHydrator);

        $service = new Service();
        $service->setServiceManager($this->serviceManager);
        static::assertInstanceOf(HydratorInterface::class, $service->getFormHydrator());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Service\UserService::setFormHydrator
     */
    public function testSetFormHydrator()
    {
        static::assertSame($this->formHydrator, $this->service->getFormHydrator());
    }
}
