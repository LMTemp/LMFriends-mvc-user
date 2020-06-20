<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\View\Helper;

use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidget as ViewHelper;
use Laminas\View\Model\ViewModel;
use LaminasFriends\Mvc\User\Form\LoginForm;

class ZfcUserLoginWidgetTest extends TestCase
{
    protected $helper;

    protected $view;

    protected function setUp(): void
    {
        $this->helper = new ViewHelper();

        $view = $this->createMock(RendererInterface::class);
        $this->view = $view;

        $this->helper->setView($view);
    }

    public function providerTestInvokeWithRender()
    {
        $attr = [];
        $attr[] = [
            [
                'render' => true,
                'redirect' => 'zfcUser'
            ],
            [
                'loginForm' => null,
                'redirect' => 'zfcUser'
            ],
        ];
        $attr[] = [
            [
                'redirect' => 'zfcUser'
            ],
            [
                'loginForm' => null,
                'redirect' => 'zfcUser'
            ],
        ];
        $attr[] = [
            [
                'render' => true,
            ],
            [
                'loginForm' => null,
                'redirect' => false
            ],
        ];

        return $attr;
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidget::__invoke
     * @dataProvider providerTestInvokeWithRender
     */
    public function testInvokeWithRender($option, $expect)
    {
        /**
         * @var $viewModel ViewModel
         */
        $viewModel = null;

        $this->view->expects(static::at(0))
             ->method('render')
             ->willReturnCallback(
                 static function ($vm) use (&$viewModel) {
                     $viewModel = $vm;
                     return 'test';
                 }
             );

        $result = $this->helper->__invoke($option);

        static::assertNotInstanceOf(ViewModel::class, $result);
        static::assertIsString($result);

        static::assertInstanceOf(ViewModel::class, $viewModel);
        foreach ($expect as $name => $value) {
            static::assertEquals($value, $viewModel->getVariable($name, 'testDefault'));
        }
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidget::__invoke
     */
    public function testInvokeWithoutRender()
    {
        $result = $this->helper->__invoke(
            [
            'render' => false,
            'redirect' => 'zfcUser'
            ]
        );

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertEquals('zfcUser', $result->redirect);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidget::setLoginForm
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidget::getLoginForm
     */
    public function testSetGetLoginForm()
    {
        $loginForm = $this->getMockBuilder(LoginForm::class)->disableOriginalConstructor()->getMock();

        $this->helper->setLoginForm($loginForm);
        static::assertInstanceOf(LoginForm::class, $this->helper->getLoginForm());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidget::setViewTemplate
     */
    public function testSetViewTemplate()
    {
        $this->helper->setViewTemplate('zfcUser');

        $reflectionClass = new ReflectionClass(ViewHelper::class);
        $reflectionProperty = $reflectionClass->getProperty('viewTemplate');
        $reflectionProperty->setAccessible(true);

        static::assertEquals('zfcUser', $reflectionProperty->getValue($this->helper));
    }
}
