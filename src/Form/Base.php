<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Element;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;

class Base extends Form implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->add(
            [
                'name' => 'username',
                'options' => [
                'label' => 'Username',
                ],
                'attributes' => [
                'type' => 'text'
                ],
            ]
        );

        $this->add(
            [
                'name' => 'email',
                'options' => [
                'label' => 'Email',
                ],
                'attributes' => [
                'type' => 'text'
                ],
            ]
        );

        $this->add(
            [
                'name' => 'display_name',
                'options' => [
                'label' => 'Display Name',
                ],
                'attributes' => [
                'type' => 'text'
                ],
            ]
        );

        $this->add(
            [
                'name' => 'password',
                'type' => 'password',
                'options' => [
                'label' => 'Password',
                ],
                'attributes' => [
                'type' => 'password'
                ],
            ]
        );

        $this->add(
            [
                'name' => 'passwordVerify',
                'type' => 'password',
                'options' => [
                'label' => 'Password Verify',
                ],
                'attributes' => [
                'type' => 'password'
                ],
            ]
        );

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Submit')
            ->setAttributes(
                [
                'type'  => 'submit',
                ]
            );

        $this->add($submitElement, [
            'priority' => -100,
        ]
        );

        $this->add(
            [
                'name' => 'userId',
                'type' => Hidden::class,
                'attributes' => [
                'type' => 'hidden'
                ],
            ]
        );

        // @TODO: Fix this... getValidator() is a protected method.
        //$csrf = new Element\Csrf('csrf');
        //$csrf->getValidator()->setTimeout($this->getRegistrationOptions()->getUserFormTimeout());
        //$this->add($csrf);
    }

    public function init()
    {
    }
}
