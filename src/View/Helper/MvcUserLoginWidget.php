<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Laminas\Form\FormInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

/**
 * Class MvcUserLoginWidget
 */
class MvcUserLoginWidget extends AbstractHelper
{
    protected FormInterface $loginForm;
    protected string $viewTemplate;


    public function __construct(FormInterface $loginForm, string $viewTemplate)
    {
        $this->loginForm = $loginForm;
        $this->viewTemplate = $viewTemplate;
    }

    /**
     * @param array $options array of options
     * @return string|ModelInterface
     */
    public function __invoke(array $options = [])
    {
        $render = true;
        if (array_key_exists('render', $options)) {
            $render = $options['render'];
        }

        $redirect = false;
        if (array_key_exists('redirect', $options)) {
            $redirect = $options['redirect'];
        }

        $vm = new ViewModel(
            [
            'loginForm' => $this->loginForm,
            'redirect'  => $redirect,
            ]
        );
        $vm->setTemplate($this->viewTemplate);
        if ($render) {
            return $this->getView()->render($vm);
        }

        return $vm;
    }
}
