<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\InputFilter\InputFilter;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class ChangePasswordFilter extends InputFilter
{
    public function __construct(AuthenticationOptionsInterface $options)
    {
        $identityParams = [
            'name'       => 'identity',
            'required'   => true,
            'validators' => []
        ];

        $identityFields = $options->getAuthIdentityFields();
        if ($identityFields == ['email']) {
            $validators = ['name' => 'EmailAddress'];
            $identityParams['validators'][] = $validators;
        }

        $this->add($identityParams);

        $this->add(
            [
                'name'       => 'credential',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                        'min' => 6,
                        ],
                    ],
                ],
                'filters'   => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'newCredential',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                        'min' => 6,
                        ],
                    ],
                ],
                'filters'   => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'newCredentialVerify',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                        'min' => 6,
                        ],
                    ],
                    [
                    'name' => 'identical',
                    'options' => [
                        'token' => 'newCredential'
                    ]
                    ],
                ],
                'filters'   => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );
    }
}
