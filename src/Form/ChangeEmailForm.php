<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class ChangeEmailForm extends Form implements EventManagerAwareInterface
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
                'type' => 'hidden',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'newIdentity',
                'options' => [
                'label' => 'New Email',
                ],
                'attributes' => [
                'type' => 'text',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'newIdentityVerify',
                'options' => [
                'label' => 'Verify New Email',
                ],
                'attributes' => [
                'type' => 'text',
                ],
            ]
        );

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

        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                'value' => 'Submit',
                'type'  => 'submit'
                ],
            ]
        );
    }
}
