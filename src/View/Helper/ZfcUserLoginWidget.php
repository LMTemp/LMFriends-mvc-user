<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use LaminasFriends\Mvc\User\Form\LoginForm as LoginForm;
use Laminas\View\Model\ViewModel;

class ZfcUserLoginWidget extends AbstractHelper
{
    /**
     * LoginForm Form
     * @var LoginForm
     */
    protected $loginForm;

    /**
     * $var string template used for view
     */
    protected $viewTemplate;
    /**
     * __invoke
     *
     * @access public
     * @param array $options array of options
     * @return string
     */
    public function __invoke($options = [])
    {
        if (array_key_exists('render', $options)) {
            $render = $options['render'];
        } else {
            $render = true;
        }
        if (array_key_exists('redirect', $options)) {
            $redirect = $options['redirect'];
        } else {
            $redirect = false;
        }

        $vm = new ViewModel(
            [
            'loginForm' => $this->getLoginForm(),
            'redirect'  => $redirect,
            ]
        );
        $vm->setTemplate($this->viewTemplate);
        if ($render) {
            return $this->getView()->render($vm);
        }

        return $vm;
    }

    /**
     * Retrieve LoginForm Form Object
     * @return LoginForm
     */
    public function getLoginForm()
    {
        return $this->loginForm;
    }

    /**
     * Inject LoginForm Form Object
     * @param LoginForm $loginForm
     * @return ZfcUserLoginWidget
     */
    public function setLoginForm(LoginForm $loginForm)
    {
        $this->loginForm = $loginForm;
        return $this;
    }

    /**
     * @param string $viewTemplate
     * @return ZfcUserLoginWidget
     */
    public function setViewTemplate($viewTemplate)
    {
        $this->viewTemplate = $viewTemplate;
        return $this;
    }
}
