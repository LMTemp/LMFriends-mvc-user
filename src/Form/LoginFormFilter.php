<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\InputFilter\InputFilter;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class LoginFormFilter extends InputFilter implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

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
    }
}
