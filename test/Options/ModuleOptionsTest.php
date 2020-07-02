<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Options;

use Laminas\Captcha\Figlet;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter;
use LaminasFriends\Mvc\User\Module;
use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Options\ModuleOptions as Options;
use LaminasFriends\Mvc\User\Entity\UserEntity;

class ModuleOptionsTest extends TestCase
{
    /**
     * @var Options $options
     */
    protected $options;

    protected function setUp(): void
    {
        $options = new Options();
        $this->options = $options;
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLoginRedirectRoute
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setLoginRedirectRoute
     */
    public function testSetGetLoginRedirectRoute()
    {
        $this->options->setLoginRedirectRoute('mvcUserRoute');
        static::assertEquals('mvcUserRoute', $this->options->getLoginRedirectRoute());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLoginRedirectRoute
     */
    public function testGetLoginRedirectRoute()
    {
        static::assertEquals('mvcuser', $this->options->getLoginRedirectRoute());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLogoutRedirectRoute
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setLogoutRedirectRoute
     */
    public function testSetGetLogoutRedirectRoute()
    {
        $this->options->setLogoutRedirectRoute('mvcUserRoute');
        static::assertEquals('mvcUserRoute', $this->options->getLogoutRedirectRoute());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLogoutRedirectRoute
     */
    public function testGetLogoutRedirectRoute()
    {
        static::assertSame(Module::ROUTE_LOGIN, $this->options->getLogoutRedirectRoute());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUseRedirectParameterIfPresent
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setUseRedirectParameterIfPresent
     */
    public function testSetGetUseRedirectParameterIfPresent()
    {
        $this->options->setUseRedirectParameterIfPresent(false);
        static::assertFalse($this->options->getUseRedirectParameterIfPresent());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUseRedirectParameterIfPresent
     */
    public function testGetUseRedirectParameterIfPresent()
    {
        static::assertTrue($this->options->getUseRedirectParameterIfPresent());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUserLoginWidgetViewTemplate
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setUserLoginWidgetViewTemplate
     */
    public function testSetGetUserLoginWidgetViewTemplate()
    {
        $this->options->setUserLoginWidgetViewTemplate('mvcUser.phtml');
        static::assertEquals('mvcUser.phtml', $this->options->getUserLoginWidgetViewTemplate());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUserLoginWidgetViewTemplate
     */
    public function testGetUserLoginWidgetViewTemplate()
    {
        static::assertEquals('mvc-user/user/login.phtml', $this->options->getUserLoginWidgetViewTemplate());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableRegistration
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setEnableRegistration
     */
    public function testSetGetEnableRegistration()
    {
        $this->options->setEnableRegistration(false);
        static::assertFalse($this->options->getEnableRegistration());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableRegistration
     */
    public function testGetEnableRegistration()
    {
        static::assertTrue($this->options->getEnableRegistration());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLoginFormTimeout
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setLoginFormTimeout
     */
    public function testSetGetLoginFormTimeout()
    {
        $this->options->setLoginFormTimeout(100);
        static::assertEquals(100, $this->options->getLoginFormTimeout());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLoginFormTimeout
     */
    public function testGetLoginFormTimeout()
    {
        static::assertEquals(300, $this->options->getLoginFormTimeout());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLoginAfterRegistration
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setLoginAfterRegistration
     */
    public function testSetGetLoginAfterRegistration()
    {
        $this->options->setLoginAfterRegistration(false);
        static::assertFalse($this->options->getLoginAfterRegistration());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getLoginAfterRegistration
     */
    public function testGetLoginAfterRegistration()
    {
        static::assertTrue($this->options->getLoginAfterRegistration());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableUserState
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setEnableUserState
     */
    public function testSetGetEnableUserState()
    {
        $this->options->setEnableUserState(true);
        static::assertTrue($this->options->getEnableUserState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableUserState
     */
    public function testGetEnableUserState()
    {
        static::assertFalse($this->options->getEnableUserState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getDefaultUserState
     */
    public function testGetDefaultUserState()
    {
        static::assertEquals(1, $this->options->getDefaultUserState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getDefaultUserState
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setDefaultUserState
     */
    public function testSetGetDefaultUserState()
    {
        $this->options->setDefaultUserState(3);
        static::assertEquals(3, $this->options->getDefaultUserState());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getAllowedLoginStates
     */
    public function testGetAllowedLoginStates()
    {
        static::assertEquals([null, 1], $this->options->getAllowedLoginStates());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getAllowedLoginStates
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setAllowedLoginStates
     */
    public function testSetGetAllowedLoginStates()
    {
        $this->options->setAllowedLoginStates([2, 5, null]);
        static::assertEquals([2, 5, null], $this->options->getAllowedLoginStates());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getAuthAdapters
     */
    public function testGetAuthAdapters()
    {
        static::assertEquals([100 => DbAdapter::class], $this->options->getAuthAdapters());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getAuthAdapters
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setAuthAdapters
     */
    public function testSetGetAuthAdapters()
    {
        $this->options->setAuthAdapters([40 => 'SomeAdapter']);
        static::assertEquals([40 => 'SomeAdapter'], $this->options->getAuthAdapters());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getAuthIdentityFields
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setAuthIdentityFields
     */
    public function testSetGetAuthIdentityFields()
    {
        $this->options->setAuthIdentityFields(['username']);
        static::assertEquals(['username'], $this->options->getAuthIdentityFields());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getAuthIdentityFields
     */
    public function testGetAuthIdentityFields()
    {
        static::assertEquals(['email'], $this->options->getAuthIdentityFields());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableUsername
     */
    public function testGetEnableUsername()
    {
        static::assertFalse($this->options->getEnableUsername());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableUsername
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setEnableUsername
     */
    public function testSetGetEnableUsername()
    {
        $this->options->setEnableUsername(true);
        static::assertTrue($this->options->getEnableUsername());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableDisplayName
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setEnableDisplayName
     */
    public function testSetGetEnableDisplayName()
    {
        $this->options->setEnableDisplayName(true);
        static::assertTrue($this->options->getEnableDisplayName());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getEnableDisplayName
     */
    public function testGetEnableDisplayName()
    {
        static::assertFalse($this->options->getEnableDisplayName());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUseRegistrationFormCaptcha
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setUseRegistrationFormCaptcha
     */
    public function testSetGetUseRegistrationFormCaptcha()
    {
        $this->options->setUseRegistrationFormCaptcha(true);
        static::assertTrue($this->options->getUseRegistrationFormCaptcha());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUseRegistrationFormCaptcha
     */
    public function testGetUseRegistrationFormCaptcha()
    {
        static::assertFalse($this->options->getUseRegistrationFormCaptcha());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUserEntityClass
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setUserEntityClass
     */
    public function testSetGetUserEntityClass()
    {
        $this->options->setUserEntityClass('mvcUser');
        static::assertEquals('mvcUser', $this->options->getUserEntityClass());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getUserEntityClass
     */
    public function testGetUserEntityClass()
    {
        static::assertEquals(UserEntity::class, $this->options->getUserEntityClass());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getPasswordCost
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setPasswordCost
     */
    public function testSetGetPasswordCost()
    {
        $this->options->setPasswordCost(10);
        static::assertEquals(10, $this->options->getPasswordCost());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getPasswordCost
     */
    public function testGetPasswordCost()
    {
        static::assertEquals(14, $this->options->getPasswordCost());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getTableName
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setTableName
     */
    public function testSetGetTableName()
    {
        $this->options->setTableName('mvcUser');
        static::assertEquals('mvcUser', $this->options->getTableName());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getTableName
     */
    public function testGetTableName()
    {
        static::assertEquals('user', $this->options->getTableName());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getFormCaptchaOptions
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::setFormCaptchaOptions
     */
    public function testSetGetFormCaptchaOptions()
    {
        $expected = [
            'class'   => 'someClass',
            'options' => [
                'anOption' => 3,
            ],
        ];
        $this->options->setFormCaptchaOptions($expected);
        static::assertEquals($expected, $this->options->getFormCaptchaOptions());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Options\ModuleOptions::getFormCaptchaOptions
     */
    public function testGetFormCaptchaOptions()
    {
        $expected = [
            'class'   => Figlet::class,
            'options' => [
                'wordLen'    => 5,
                'expiration' => 300,
                'timeout'    => 300,
            ],
        ];
        static::assertEquals($expected, $this->options->getFormCaptchaOptions());
    }
}
