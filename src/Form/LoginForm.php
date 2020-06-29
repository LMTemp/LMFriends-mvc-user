<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Element;
use Laminas\Form\Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class LoginForm extends Form implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected AuthenticationOptionsInterface $authOptions;

    public function __construct($name, AuthenticationOptionsInterface $authOptions)
    {
        $this->authOptions = $authOptions;

        parent::__construct($name);

        $this->add(
            [
                'name' => 'identity',
                'options' => [
                'label' => '',
                ],
                'attributes' => [
                'type' => 'text'
                ],
            ]
        );

        $emailElement = $this->get('identity');
        $label = $emailElement->getLabel();
        // @TODO: make translation-friendly
        foreach ($this->getAuthenticationOptions()->getAuthIdentityFields() as $mode) {
            $label = (!empty($label) ? $label . ' or ' : '') . ucfirst($mode);
        }
        $emailElement->setLabel($label);
        //
        $this->add(
            [
                'name' => 'credential',
                'type' => 'password',
                'options' => [
                'label' => 'Password',
                ],
                'attributes' => [
                'type' => 'password',
                ],
            ]
        );

        // @todo: Fix this
        // 1) getValidator() is a protected method
        // 2) i don't believe the login form is actually being validated by the login action
        // (but keep in mind we don't want to show invalid username vs invalid password or
        // anything like that, it should just say "login failed" without any additional info)
        //$csrf = new Element\Csrf('csrf');
        //$csrf->getValidator()->setTimeout($options->getLoginFormTimeout());
        //$this->add($csrf);

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Sign In')
            ->setAttributes(
                [
                'type'  => 'submit',
                ]
            );

        $this->add($submitElement, [
            'priority' => -100,
        ]
        );
    }

    /**
     * Get Authentication-related Options
     *
     * @return AuthenticationOptionsInterface
     */
    public function getAuthenticationOptions(): AuthenticationOptionsInterface
    {
        return $this->authOptions;
    }
}
