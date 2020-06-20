<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use LaminasFriends\Mvc\User\Form\RegisterForm as Form;
use LaminasFriends\Mvc\User\Options\RegistrationOptionsInterface;
use Laminas\Captcha\AbstractAdapter;
use Laminas\Form\Element\Captcha;

class RegisterFormTest extends TestCase
{
    /**
     * @dataProvider providerTestConstruct
     */
    public function testConstruct($useCaptcha = false)
    {
        $options = $this->createMock(RegistrationOptionsInterface::class);
        $options->expects(static::once())
                ->method('getEnableUsername')
                ->willReturn(false);
        $options->expects(static::once())
                ->method('getEnableDisplayName')
                ->willReturn(false);
        $options
                ->method('getUseRegistrationFormCaptcha')
                ->willReturn($useCaptcha);
        if ($useCaptcha && class_exists(AbstractAdapter::class)) {
            $captcha = $this->getMockForAbstractClass(AbstractAdapter::class);

            $options->expects(static::once())
                ->method('getFormCaptchaOptions')
                ->willReturn($captcha);
        }

        $form = new Form(null, $options);

        $elements = $form->getElements();

        static::assertArrayNotHasKey('userId', $elements);
        static::assertArrayNotHasKey('username', $elements);
        static::assertArrayNotHasKey('display_name', $elements);
        static::assertArrayHasKey('email', $elements);
        static::assertArrayHasKey('password', $elements);
        static::assertArrayHasKey('passwordVerify', $elements);
    }

    public function providerTestConstruct()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testSetGetRegistrationOptions()
    {
        $options = $this->createMock(RegistrationOptionsInterface::class);
        $options->expects(static::once())
                ->method('getEnableUsername')
                ->willReturn(false);
        $options->expects(static::once())
                ->method('getEnableDisplayName')
                ->willReturn(false);
        $options
                ->method('getUseRegistrationFormCaptcha')
                ->willReturn(false);
        $form = new Form(null, $options);

        static::assertSame($options, $form->getRegistrationOptions());

        $optionsNew = $this->createMock(RegistrationOptionsInterface::class);
        $form->setRegistrationOptions($optionsNew);
        static::assertSame($optionsNew, $form->getRegistrationOptions());
    }

    public function testSetCaptchaElement()
    {
        $options = $this->createMock(RegistrationOptionsInterface::class);
        $options->expects(static::once())
                ->method('getEnableUsername')
                ->willReturn(false);
        $options->expects(static::once())
                ->method('getEnableDisplayName')
                ->willReturn(false);
        $options
                ->method('getUseRegistrationFormCaptcha')
                ->willReturn(false);

        $captcha = $this->createMock(Captcha::class);
        $form = new Form(null, $options);

        $form->setCaptchaElement($captcha);

        $reflection = $this->helperMakePropertyAccessable($form, 'captchaElement');
        static::assertSame($captcha, $reflection->getValue($form));
    }


    /**
     *
     * @param mixed $objectOrClass
     * @param string $property
     * @param mixed $value = null
     * @return ReflectionProperty
     */
    public function helperMakePropertyAccessable($objectOrClass, $property, $value = null)
    {
        $reflectionProperty = new ReflectionProperty($objectOrClass, $property);
        $reflectionProperty->setAccessible(true);

        if ($value !== null) {
            $reflectionProperty->setValue($objectOrClass, $value);
        }
        return $reflectionProperty;
    }
}
