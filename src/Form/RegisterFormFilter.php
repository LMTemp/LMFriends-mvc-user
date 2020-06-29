<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\ValidatorInterface;
use LaminasFriends\Mvc\User\Options\RegistrationOptionsInterface;

/**
 * Class RegisterFilter
 */
class RegisterFormFilter extends InputFilter implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected ValidatorInterface $emailValidator;
    protected ValidatorInterface $usernameValidator;
    protected RegistrationOptionsInterface $options;

    public function __construct(ValidatorInterface $emailValidator, ValidatorInterface $usernameValidator, RegistrationOptionsInterface $options)
    {
        $this->options = $options;
        $this->emailValidator = $emailValidator;
        $this->usernameValidator = $usernameValidator;

        if ($this->options->getEnableUsername()) {
            $this->add(
                [
                    'name'       => 'username',
                    'required'   => true,
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                            'min' => 3,
                            'max' => 255,
                            ],
                        ],
                        $this->usernameValidator,
                    ],
                ]
            );
        }

        $this->add(
            [
                'name'       => 'email',
                'required'   => true,
                'validators' => [
                    [
                    'name' => 'EmailAddress'
                    ],
                    $this->emailValidator
                ],
            ]
        );

        if ($this->options->getEnableDisplayName()) {
            $this->add(
                [
                    'name'       => 'display_name',
                    'required'   => true,
                    'filters'    => [['name' => 'StringTrim']],
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                            'min' => 3,
                            'max' => 128,
                            ],
                        ],
                    ],
                ]
            );
        }

        $this->add(
            [
                'name'       => 'password',
                'required'   => true,
                'filters'    => [['name' => 'StringTrim']],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                        'min' => 6,
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'passwordVerify',
                'required'   => true,
                'filters'    => [['name' => 'StringTrim']],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                        'min' => 6,
                        ],
                    ],
                    [
                        'name'    => 'Identical',
                        'options' => [
                        'token' => 'password',
                        ],
                    ],
                ],
            ]
        );
    }
}
