<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\View\Helper;

use Laminas\Form\FormInterface;
use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\View\Helper\MvcUserLoginWidget;
use Laminas\View\Model\ViewModel;

/**
 * Class MvcUserLoginWidgetTest
 */
class MvcUserLoginWidgetTest extends TestCase
{
    private $helper;

    private $view;

    private $loginForm;

    protected function setUp(): void
    {
        $this->loginForm = $this->createMock(FormInterface::class);
        $this->view = $this->createMock(RendererInterface::class);
        
        $this->helper = new MvcUserLoginWidget($this->loginForm, 'mvcUser');
        $this->helper->setView($this->view);
    }

    public function providerTestInvokeWithRender()
    {
        $attr = [];
        $attr[] = [
            [
                'render' => true,
                'redirect' => 'mvcUser'
            ],
            [
                'redirect' => 'mvcUser'
            ],
        ];
        $attr[] = [
            [
                'redirect' => 'mvcUser'
            ],
            [
                'redirect' => 'mvcUser'
            ],
        ];
        $attr[] = [
            [
                'render' => true,
            ],
            [
                'redirect' => false
            ],
        ];

        return $attr;
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserLoginWidget::__invoke
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
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserLoginWidget::__invoke
     */
    public function testInvokeWithoutRender()
    {
        $result = $this->helper->__invoke(
            [
            'render' => false,
            'redirect' => 'mvcUser'
            ]
        );

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertEquals('mvcUser', $result->redirect);
    }
}
