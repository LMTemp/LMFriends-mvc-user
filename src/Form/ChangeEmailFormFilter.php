<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\ValidatorInterface;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

/**
 * Class ChangeEmailFormFilter
 */
class ChangeEmailFormFilter extends InputFilter
{
    protected ValidatorInterface $emailValidator;

    /**
     * ChangeEmailFormFilter constructor.
     *
     * @param AuthenticationOptionsInterface $options
     * @param ValidatorInterface             $emailValidator
     */
    public function __construct(AuthenticationOptionsInterface $options, ValidatorInterface $emailValidator)
    {
        $this->emailValidator = $emailValidator;

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
                'name'       => 'newIdentity',
                'required'   => true,
                'validators' => [
                    [
                    'name' => 'EmailAddress'
                    ],
                    $this->emailValidator
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'newIdentityVerify',
                'required'   => true,
                'validators' => [
                    [
                    'name' => 'identical',
                    'options' => [
                        'token' => 'newIdentity'
                    ]
                    ],
                ],
            ]
        );
    }
}
