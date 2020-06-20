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
    /**
     * @var AuthenticationOptionsInterface
     */
    protected $authOptions;

    public function __construct($name, AuthenticationOptionsInterface $options)
    {
        $this->setAuthenticationOptions($options);

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

    /**
     * Set Authentication-related Options
     *
     * @param AuthenticationOptionsInterface $authOptions
     *
     * @return ChangeEmailForm
     */
    public function setAuthenticationOptions(AuthenticationOptionsInterface $authOptions)
    {
        $this->authOptions = $authOptions;

        return $this;
    }

    /**
     * Get Authentication-related Options
     *
     * @return AuthenticationOptionsInterface
     */
    public function getAuthenticationOptions()
    {
        return $this->authOptions;
    }
}
