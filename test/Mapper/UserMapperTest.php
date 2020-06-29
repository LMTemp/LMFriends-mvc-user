<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Mapper;

use Exception;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Sql\Platform\Platform;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use PHPUnit\Framework\TestCase;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use LaminasFriends\Mvc\User\Entity\UserEntity as Entity;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Adapter\Adapter;
use LaminasFriends\Mvc\User\Mapper\UserHydrator;

class UserMapperTest extends TestCase
{

    protected $mapper;

    /** @var Adapter */
    protected $mockedDbAdapter;

    /** @var Adapter */
    protected $realAdapter = [];

    /** @var Select */
    protected $mockedSelect;

    /** @var HydratingResultSet */
    protected $mockedResultSet;

    /** @var Sql */
    protected $mockedDbSql;

    /** @var DriverInterface */
    protected $mockedDbAdapterDriver;

    /** @var PlatformInterface */
    protected $mockedDbAdapterPlatform;

    /**
     * @dataProvider providerTestFindBy
     *
     * @param string $method
     * @param array  $args
     * @param array  $expectedParams
     */
    public function testFindBy($method, $args, $expectedParams, $eventListener, $entityEqual)
    {
        $mockedParams =& $this->setUpMockedMapper($eventListener);

        $this->mockedResultSet->expects(static::once())
            ->method('current')
            ->willReturn($entityEqual);

        $return = call_user_func_array([$this->mapper, $method], $args);

        foreach ($expectedParams as $paramKey => $paramValue) {
            static::assertArrayHasKey($paramKey, $mockedParams);
            static::assertEquals($paramValue, $mockedParams[$paramKey]);
        }
        static::assertEquals($entityEqual, $return);
    }

    /**
     *
     * @param array $eventListenerArray
     *
     * @return array
     */
    public function &setUpMockedMapper($eventListenerArray, array $mapperMethods = [])
    {
        $returnMockedParams = [];

        $mapperMethods = count($mapperMethods)
            ? array_merge($mapperMethods, ['select'])
            : ['select'];

        $this->setUpMockMapperInsert($mapperMethods);

        /*$this->mapper->expects(static::once())
                     ->method('select')
                     ->willReturn($this->mockedResultSet);*/

        $mockedSelect = $this->mockedSelect;
        $this->mockedSelect->expects(static::once())
            ->method('where')
            ->willReturnCallback(
                static function () use (&$returnMockedParams, $mockedSelect) {
                    $returnMockedParams['whereArgs'] = func_get_args();
                    return $mockedSelect;
                }
            );

        foreach ($eventListenerArray as $eventKey => $eventListener) {
            $this->mapper->getEventManager()->attach($eventKey, $eventListener);
        }

        $this->mapper->setDbAdapter($this->mockedDbAdapter);
        $this->mapper->setEntityPrototype(new Entity());

        return $returnMockedParams;
    }

    /**
     *
     * @param array $eventListenerArray
     *
     * @return array
     */
    public function setUpMockMapperInsert($mapperMethods)
    {
        $this->mapper = $this->createMock(UserMapper::class);

        foreach ($mapperMethods as $method) {
            switch ($method) {
                case 'getSelect':
                    $this->mapper->expects(static::once())
                        ->method('getSelect')
                        ->willReturn($this->mockedSelect);
                    break;
                case 'initialize':
                    $this->mapper->expects(static::once())
                        ->method('initialize')
                        ->willReturn(true);
                    break;
            }
        }
    }

    /**
     * @todo         Integration test for UserMapper
     * @dataProvider providerTestFindBy
     */
    public function testIntegrationFindBy($method, $args, $expectedParams, $eventListener, $entityEqual)
    {
        /* @var $entityEqual Entity */
        /* @var $dbAdapter Adapter */
        foreach ($this->realAdapter as $dbAdapter) {
            if ($dbAdapter === false) {
                continue;
            }

            $this->mapper->setDbAdapter($dbAdapter);
            $return = call_user_func_array([$this->mapper, $method], $args);

            static::assertIsObject($return);
            static::assertInstanceOf(Entity::class, $return);
            static::assertEquals($entityEqual, $return);
        }

        if (!isset($return)) {
            static::markTestSkipped('Without real database we dont can test findByEmail / findByUsername / findById');
        }
    }

    public function testGetTableName()
    {
        static::assertEquals('user', $this->mapper->getTableName());
    }

    public function testSetTableName()
    {
        $this->mapper->setTableName('MvcUser');
        static::assertEquals('MvcUser', $this->mapper->getTableName());
    }

    public function testInsertUpdateDelete()
    {
        $baseEntity = new Entity();
        $baseEntity->setEmail('mvc-user-foo@laminas-framework.org');
        $baseEntity->setUsername('mvc-user-foo');
        $baseEntity->setPassword('mvc-user-foo');

        /* @var $entityEqual Entity */
        /* @var $dbAdapter Adapter */
        foreach ($this->realAdapter as $diver => $dbAdapter) {
            if ($dbAdapter === false) {
                continue;
            }
            $this->mapper->setDbAdapter($dbAdapter);

            // insert
            $entity = clone $baseEntity;

            $result = $this->mapper->insert($entity);

            static::assertNotNull($entity->getId());
            static::assertGreaterThanOrEqual(1, $entity->getId());

            $entityEqual = $this->mapper->findById($entity->getId());
            static::assertEquals($entity, $entityEqual);

            // update
            $entity->setUsername($entity->getUsername() . '-' . $diver);
            $entity->setEmail($entity->getUsername() . '@github.com');

            $result = $this->mapper->update($entity);

            $entityEqual = $this->mapper->findById($entity->getId());
            static::assertNotEquals($baseEntity->getUsername(), $entityEqual->getUsername());
            static::assertNotEquals($baseEntity->getEmail(), $entityEqual->getEmail());

            static::assertEquals($entity->getUsername(), $entityEqual->getUsername());
            static::assertEquals($entity->getEmail(), $entityEqual->getEmail());
            /**
             *
             * @todo delete is currently protected
             *
             * // delete
             * $result = $this->mapper->delete($entity->getId());
             *
             * $this->assertNotEquals($baseEntity->getEmail(), $entityEqual->getEmail());
             * $this->assertEquals($entity->getEmail(), $entityEqual->getEmail());
             */
        }

        if (!isset($result)) {
            static::markTestSkipped('Without real database we dont can test insert, update and delete');
        }
    }

    public function providerTestFindBy()
    {
        $user = new Entity();
        $user->setEmail('mvc-user@github.com');
        $user->setUsername('mvc-user');
        $user->setDisplayName('Mvc-UserService');
        $user->setId('1');
        $user->setState(1);
        $user->setPassword('mvc-user');

        return [
            [
                'findByEmail',
                [$user->getEmail()],
                [
                    'whereArgs' => [
                        ['email' => $user->getEmail()],
                        'AND',
                    ],
                ],
                [],
                $user,
            ],
            [
                'findByUsername',
                [$user->getUsername()],
                [
                    'whereArgs' => [
                        ['username' => $user->getUsername()],
                        'AND',
                    ],
                ],
                [],
                $user,
            ],
            [
                'findById',
                [$user->getId()],
                [
                    'whereArgs' => [
                        ['id' => $user->getId()],
                        'AND',
                    ],
                ],
                [],
                $user,
            ],
        ];
    }

    protected function setUp(): void
    {
        $mapper = new UserMapper();
        $mapper->setEntityPrototype(new Entity());
        $mapper->setHydrator(new UserHydrator());
        $this->mapper = $mapper;


        $this->setUpMockedAdapter();

        $this->mockedSelect = $this->createMock(Select::class, ['where']);

        $this->mockedResultSet = $this->createMock(HydratingResultSet::class);

        $this->setUpAdapter('mysql');
//         $this->setUpAdapter('pgsql');
        $this->setUpAdapter('sqlite');
    }

    /**
     *
     */
    public function setUpMockedAdapter()
    {
        $this->mockedDbAdapterDriver = $this->createMock(DriverInterface::class);
        $this->mockedDbAdapterPlatform = $this->createMock(PlatformInterface::class);
        $this->mockedDbAdapterStatement = $this->createMock(StatementInterface::class);

        $this->mockedDbAdapterPlatform
            ->method('getName')
            ->willReturn('null');

        $this->mockedDbAdapter = $this->getMockBuilder(Adapter::class)
            ->setConstructorArgs(
                [
                    $this->mockedDbAdapterDriver,
                    $this->mockedDbAdapterPlatform,
                ]
            )
            ->getMock();

        $this->mockedDbAdapter
            ->method('getPlatform')
            ->willReturn($this->mockedDbAdapterPlatform);

        $this->mockedDbSql = $this->getMockBuilder(Sql::class)
            ->setConstructorArgs([$this->mockedDbAdapter])
            ->getMock();
        $this->mockedDbSql
            ->method('prepareStatementForSqlObject')
            ->willReturn($this->mockedDbAdapterStatement);

        $this->mockedDbSqlPlatform = $this->getMockBuilder(Platform::class)
            ->setConstructorArgs([$this->mockedDbAdapter])
            ->getMock();
    }

    /**
     *
     */
    public function setUpAdapter($driver)
    {
        $upCase = strtoupper($driver);
        if (!defined(sprintf('DB_%s_DSN', $upCase))
            || !defined(sprintf('DB_%s_USERNAME', $upCase))
            || !defined(sprintf('DB_%s_PASSWORD', $upCase))
            || !defined(sprintf('DB_%s_SCHEMA', $upCase))
        ) {
            return false;
        }

        try {
            $connection = [
                'driver' => sprintf('Pdo_%s', ucfirst($driver)),
                'dsn'    => constant(sprintf('DB_%s_DSN', $upCase)),
            ];
            if (constant(sprintf('DB_%s_USERNAME', $upCase)) !== '') {
                $connection['username'] = constant(sprintf('DB_%s_USERNAME', $upCase));
                $connection['password'] = constant(sprintf('DB_%s_PASSWORD', $upCase));
            }
            $adapter = new Adapter($connection);

            $this->setUpSqlDatabase($adapter, constant(sprintf('DB_%s_SCHEMA', $upCase)));

            $this->realAdapter[$driver] = $adapter;
        } catch (Exception $e) {
            $this->realAdapter[$driver] = false;
        }
    }

    public function setUpSqlDatabase($adapter, $schemaPath)
    {
        $queryStack = ['DROP TABLE IF EXISTS user'];
        $queryStack = array_merge($queryStack, explode(';', file_get_contents($schemaPath)));
        $queryStack = array_merge($queryStack, explode(';', file_get_contents(__DIR__ . '/_files/user.sql')));

        foreach ($queryStack as $query) {
            if (!preg_match('/\S+/', $query)) {
                continue;
            }
            $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        }
    }
}
