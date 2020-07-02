<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;
use LaminasFriends\Mvc\User\Options\FormOptionsInterface;

class ChangePasswordForm extends Form implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected FormOptionsInterface $formOptions;

    /**
     * ChangePasswordForm constructor.
     *
     * @param                                $name
     * @param FormOptionsInterface $options
     */
    public function __construct($name, FormOptionsInterface $options)
    {
        $this->formOptions = $options;

        parent::__construct($name);

        $this->add(
            [
                'name' => 'identity',
                'options' => [
                'label' => '',
                ],
                'attributes' => [
                'type' => 'hidden'
                ],
            ]
        );

        $this->add(
            [
                'type'    => Csrf::class,
                'name'    => 'security',
                'options' => [
                    'csrf_options' => [
                        'timeout' => $this->formOptions->getChangePasswordFormTimeout(),
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'credential',
                'type' => 'password',
                'options' => [
                'label' => 'Current Password',
                ],
                'attributes' => [
                'type' => 'password',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'newCredential',
                'options' => [
                'label' => 'New Password',
                ],
                'attributes' => [
                'type' => 'password',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'newCredentialVerify',
                'type' => 'password',
                'options' => [
                'label' => 'Verify New Password',
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
