<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;
use LaminasFriends\Mvc\User\Options\FormOptionsInterface;

class ChangeEmailForm extends Form implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected FormOptionsInterface $formOptions;

    public function __construct($name, FormOptionsInterface $authOptions)
    {
        $this->formOptions = $authOptions;

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
                'type'    => Csrf::class,
                'name'    => 'security',
                'options' => [
                    'csrf_options' => [
                        'timeout' => $this->formOptions->getChangeEmailFormTimeout(),
                    ],
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
