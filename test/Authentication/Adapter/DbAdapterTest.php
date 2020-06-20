<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Adapter;

use Laminas\Crypt\Password\Bcrypt;
use Laminas\Http\Request;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\SessionManager;
use Laminas\Stdlib\Parameters;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Laminas\Authentication\Result;
use Laminas\Authentication\Storage\Session;
use Laminas\EventManager\Event;
use Laminas\Session\AbstractContainer;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent;

class DbAdapterTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var Db
     */
    protected $db;

    /**
     * Mock of AuthEvent.
     *
     * @var AdapterChainEvent|MockObject
     */
    protected $authEvent;

    /**
     * Mock of Storage.
     *
     * @var Session|MockObject
     */
    protected $storage;

    /**
     * Mock of Options.
     *
     * @var ModuleOptions|MockObject
     */
    protected $options;

    /**
     * Mock of Mapper.
     *
     * @var UserMapperInterface|MockObject
     */
    protected $mapper;

    /**
     * Mock of UserService.
     *
     * @var \LaminasFriends\Mvc\User\Entity\UserEntityInterface|MockObject
     */
    protected $user;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(Session::class);;
        $this->authEvent = $this->createMock(AdapterChainEvent::class);
        $this->options = $this->createMock(ModuleOptions::class);
        $this->mapper = $this->createMock(UserMapperInterface::class);
        $this->user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntityInterface::class);;

        $this->db = new DbAdapter();
        $this->db->setStorage($this->storage);

        $sessionManager = $this->createMock(SessionManager::class);
        AbstractContainer::setDefaultManager($sessionManager);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::logout
     */
    public function testLogout(): void
    {
        $this->storage->expects(static::once())
                      ->method('clear');

         $this->db->logout($this->authEvent);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::Authenticate
     */
    public function testAuthenticateWhenSatisfies(): void
    {
        $this->authEvent->expects(static::once())
                        ->method('setIdentity')
                        ->with('ZfcUser')
                        ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
                        ->method('setCode')
                        ->with(Result::SUCCESS)
                        ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
                        ->method('setMessages')
                        ->with(['Authentication successful.'])
                        ->willReturn($this->authEvent);

        $this->storage->expects(static::at(0))
            ->method('read')
            ->willReturn(['is_satisfied' => true]);
        $this->storage->expects(static::at(1))
            ->method('read')
            ->willReturn(['identity' => 'ZfcUser']);

        $event = new AdapterChainEvent(null, $this->authEvent);

        $result = $this->db->authenticate($this->authEvent);
        static::assertNull($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::Authenticate
     */
    public function testAuthenticateNoUserObject()
    {
        $this->setAuthenticationCredentials();

        $this->options->expects(static::once())
            ->method('getAuthIdentityFields')
            ->willReturn([]);

        $this->authEvent->expects(static::once())
            ->method('setCode')
            ->with(Result::FAILURE_IDENTITY_NOT_FOUND)
            ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
            ->method('setMessages')
            ->with(['A record with the supplied identity could not be found.'])
            ->willReturn($this->authEvent);

        $this->db->setOptions($this->options);

        $event = new AdapterChainEvent(null, $this->authEvent);
        $result = $this->db->authenticate($this->authEvent);

        static::assertFalse($result);
        static::assertFalse($this->db->isSatisfied());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::Authenticate
     */
    public function testAuthenticationUserStateEnabledUserButUserStateNotInArray()
    {
        $this->setAuthenticationCredentials();
        $this->setAuthenticationUser();

        $this->options->expects(static::once())
            ->method('getEnableUserState')
            ->willReturn(true);
        $this->options->expects(static::once())
            ->method('getAllowedLoginStates')
            ->willReturn([2, 3]);

        $this->authEvent->expects(static::once())
            ->method('setCode')
            ->with(Result::FAILURE_UNCATEGORIZED)
            ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
            ->method('setMessages')
            ->with(['A record with the supplied identity is not active.'])
            ->willReturn($this->authEvent);

        $this->user->expects(static::once())
            ->method('getState')
            ->willReturn(1);

        $this->db->setMapper($this->mapper);
        $this->db->setOptions($this->options);

        $event = new AdapterChainEvent(null, $this->authEvent);
        $result = $this->db->authenticate($this->authEvent);

        static::assertFalse($result);
        static::assertFalse($this->db->isSatisfied());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::Authenticate
     */
    public function testAuthenticateWithWrongPassword()
    {
        $this->setAuthenticationCredentials();
        $this->setAuthenticationUser();

        $this->options->expects(static::once())
            ->method('getEnableUserState')
            ->willReturn(false);

        // Set lowest possible to spent the least amount of resources/time
        $this->options->expects(static::once())
            ->method('getPasswordCost')
            ->willReturn(4);

        $this->authEvent->expects(static::once())
            ->method('setCode')
            ->with(Result::FAILURE_CREDENTIAL_INVALID)
            ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
            ->method('setMessages')
            ->with(['Supplied credential is invalid.']);

        $this->db->setMapper($this->mapper);
        $this->db->setOptions($this->options);

        $event = new AdapterChainEvent(null, $this->authEvent);
        $result = $this->db->authenticate($this->authEvent);

        static::assertFalse($result);
        static::assertFalse($this->db->isSatisfied());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::Authenticate
     */
    public function testAuthenticationAuthenticatesWithEmail(): void
    {
        $this->setAuthenticationCredentials('zfc-user@zf-commons.io');
        $this->setAuthenticationEmail();

        $this->options->expects(static::once())
            ->method('getEnableUserState')
            ->willReturn(false);

        $this->options->expects(static::once())
            ->method('getPasswordCost')
            ->willReturn(4);

        $this->user->expects(static::exactly(2))
            ->method('getPassword')
            ->willReturn('$2a$04$5kq1mnYWbww8X.rIj7eOVOHXtvGw/peefjIcm0lDGxRTEjm9LnOae');
        $this->user->expects(static::once())
                   ->method('getId')
                   ->willReturn(1);

        $this->storage
                      ->method('getNameSpace')
                      ->willReturn('test');

        $this->authEvent->expects(static::once())
                        ->method('setIdentity')
                        ->with(1)
                        ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
                        ->method('setCode')
                        ->with(Result::SUCCESS)
                        ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
                        ->method('setMessages')
                        ->with(['Authentication successful.'])
                        ->willReturn($this->authEvent);

        $this->db->setMapper($this->mapper);
        $this->db->setOptions($this->options);

        $event = new AdapterChainEvent(null, $this->authEvent);
        $result = $this->db->authenticate($this->authEvent);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::Authenticate
     */
    public function testAuthenticationAuthenticates()
    {
        $this->setAuthenticationCredentials();
        $this->setAuthenticationUser();

        $this->options->expects(static::once())
             ->method('getEnableUserState')
             ->willReturn(true);

        $this->options->expects(static::once())
             ->method('getAllowedLoginStates')
             ->willReturn([1, 2, 3]);

        $this->options->expects(static::once())
            ->method('getPasswordCost')
            ->willReturn(4);

        $this->user->expects(static::exactly(2))
                   ->method('getPassword')
                   ->willReturn('$2a$04$5kq1mnYWbww8X.rIj7eOVOHXtvGw/peefjIcm0lDGxRTEjm9LnOae');
        $this->user->expects(static::once())
                   ->method('getId')
                   ->willReturn(1);
        $this->user->expects(static::once())
                   ->method('getState')
                   ->willReturn(1);

        $this->storage
                      ->method('getNameSpace')
                      ->willReturn('test');

        $this->authEvent->expects(static::once())
                        ->method('setIdentity')
                        ->with(1)
                        ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
                        ->method('setCode')
                        ->with(Result::SUCCESS)
                        ->willReturn($this->authEvent);
        $this->authEvent->expects(static::once())
                        ->method('setMessages')
                        ->with(['Authentication successful.'])
                        ->willReturn($this->authEvent);

        $this->db->setMapper($this->mapper);
        $this->db->setOptions($this->options);

        $event = new AdapterChainEvent(null, $this->authEvent);
        $result = $this->db->authenticate($this->authEvent);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::updateUserPasswordHash
     */
    public function testUpdateUserPasswordHashWithSameCost()
    {
        $user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntity::class);
        $user->expects(static::once())
            ->method('getPassword')
            ->willReturn('$2a$10$x05G2P803MrB3jaORBXBn.QHtiYzGQOBjQ7unpEIge.Mrz6c3KiVm');

        $bcrypt = $this->createMock(Bcrypt::class);
        $bcrypt->expects(static::once())
            ->method('getCost')
            ->willReturn('10');

        $method = new ReflectionMethod(
            DbAdapter::class,
            'updateUserPasswordHash'
        );
        $method->setAccessible(true);

        $result = $method->invoke($this->db, $user, 'ZfcUser', $bcrypt);
        static::assertNull($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::updateUserPasswordHash
     */
    public function testUpdateUserPasswordHashWithoutSameCost()
    {
        $user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntity::class);
        $user->expects(static::once())
            ->method('getPassword')
            ->willReturn('$2a$10$x05G2P803MrB3jaORBXBn.QHtiYzGQOBjQ7unpEIge.Mrz6c3KiVm');
        $user->expects(static::once())
            ->method('setPassword')
            ->with('$2a$10$D41KPuDCn6iGoESjnLee/uE/2Xo985sotVySo2HKDz6gAO4hO/Gh6');

        $bcrypt = $this->createMock(Bcrypt::class);
        $bcrypt->expects(static::once())
            ->method('getCost')
            ->willReturn('5');
        $bcrypt->expects(static::once())
            ->method('create')
            ->with('ZfcUserNew')
            ->willReturn('$2a$10$D41KPuDCn6iGoESjnLee/uE/2Xo985sotVySo2HKDz6gAO4hO/Gh6');

        $mapper = $this->createMock(UserMapper::class);
        $mapper->expects(static::once())
            ->method('update')
            ->with($user);

        $this->db->setMapper($mapper);

        $method = new ReflectionMethod(
            DbAdapter::class,
            'updateUserPasswordHash'
        );
        $method->setAccessible(true);

        $result = $method->invoke($this->db, $user, 'ZfcUserNew', $bcrypt);
        static::assertInstanceOf(DbAdapter::class, $result);
    }


    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::preprocessCredential
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::setCredentialPreprocessor
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getCredentialPreprocessor
     */
    public function testPreprocessCredentialWithCallable()
    {
        $test = $this;
        $testVar = false;
        $callable = static function ($credential) use ($test, &$testVar) {
            $test::assertEquals('ZfcUser', $credential);
            $testVar = true;
        };
        $this->db->setCredentialPreprocessor($callable);

        $this->db->preProcessCredential('ZfcUser');
        static::assertTrue($testVar);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::preprocessCredential
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::setCredentialPreprocessor
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getCredentialPreprocessor
     */
    public function testPreprocessCredentialWithoutCallable()
    {
        $this->db->setCredentialPreprocessor(false);
        static::assertSame('zfcUser', $this->db->preProcessCredential('zfcUser'));
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::setServiceManager
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getServiceManager
     */
    public function testSetGetServicemanager()
    {
        $sm = $this->createMock(ServiceManager::class);

        $this->db->setServiceManager($sm);

        $serviceManager = $this->db->getServiceManager();

        static::assertInstanceOf(ServiceLocatorInterface::class, $serviceManager);
        static::assertSame($sm, $serviceManager);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getOptions
     */
    public function testGetOptionsWithNoOptionsSet()
    {
        $serviceMapper = $this->createMock(ServiceManager::class);
        $serviceMapper->expects(static::once())
            ->method('get')
            ->with('zfcuser_module_options')
            ->willReturn($this->options);

        $this->db->setServiceManager($serviceMapper);

        $options = $this->db->getOptions();

        static::assertInstanceOf(ModuleOptions::class, $options);
        static::assertSame($this->options, $options);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::setOptions
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getOptions
     */
    public function testSetGetOptions()
    {
        $options = new ModuleOptions();
        $options->setLoginRedirectRoute('zfcUser');

        $this->db->setOptions($options);

        static::assertInstanceOf(ModuleOptions::class, $this->db->getOptions());
        static::assertSame('zfcUser', $this->db->getOptions()->getLoginRedirectRoute());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getMapper
     */
    public function testGetMapperWithNoMapperSet()
    {
        $serviceMapper = $this->createMock(ServiceManager::class);
        $serviceMapper->expects(static::once())
            ->method('get')
            ->with('zfcuser_user_mapper')
            ->willReturn($this->mapper);

        $this->db->setServiceManager($serviceMapper);

        $mapper = $this->db->getMapper();
        static::assertInstanceOf(UserMapperInterface::class, $mapper);
        static::assertSame($this->mapper, $mapper);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::setMapper
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter::getMapper
     */
    public function testSetGetMapper()
    {
        $mapper = new UserMapper();
        $mapper->setTableName('zfcUser');

        $this->db->setMapper($mapper);

        static::assertInstanceOf(UserMapper::class, $this->db->getMapper());
        static::assertSame('zfcUser', $this->db->getMapper()->getTableName());
    }

    protected function setAuthenticationEmail()
    {
        $this->mapper->expects(static::once())
            ->method('findByEmail')
            ->with('zfc-user@zf-commons.io')
            ->willReturn($this->user);

        $this->options->expects(static::once())
            ->method('getAuthIdentityFields')
            ->willReturn(['email']);
    }

    protected function setAuthenticationUser()
    {
        $this->mapper->expects(static::once())
            ->method('findByUsername')
            ->with('ZfcUser')
            ->willReturn($this->user);

        $this->options->expects(static::once())
            ->method('getAuthIdentityFields')
            ->willReturn(['username']);
    }

    protected function setAuthenticationCredentials($identity = 'ZfcUser', $credential = 'ZfcUserPassword')
    {
        $this->storage->expects(static::at(0))
            ->method('read')
            ->willReturn(['is_satisfied' => false]);

        $post = $this->createMock(Parameters::class);
        $post->expects(static::at(0))
            ->method('get')
            ->with('identity')
            ->willReturn($identity);
        $post->expects(static::at(1))
            ->method('get')
            ->with('credential')
            ->willReturn($credential);

        $request = $this->createMock(Request::class);
        $request->expects(static::exactly(2))
            ->method('getPost')
            ->willReturn($post);

        $this->authEvent->expects(static::exactly(2))
            ->method('getRequest')
            ->willReturn($request);
    }
}
