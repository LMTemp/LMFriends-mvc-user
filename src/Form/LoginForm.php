<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Element;
use Laminas\Form\Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;
use Laminas\Form\Element\Csrf;

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
                'name'       => 'identity',
                'options'    => [
                    'label' => '',
                ],
                'attributes' => [
                    'type' => 'text',
                ],
            ]
        );

        $emailElement = $this->get('identity');
        $label = $emailElement->getLabel();
        // @TODO: make translation-friendly
        foreach ($this->authOptions->getAuthIdentityFields() as $mode) {
            $label = (!empty($label) ? $label . ' or ' : '') . ucfirst($mode);
        }
        $emailElement->setLabel($label);
        //
        $this->add(
            [
                'name'       => 'credential',
                'type'       => 'password',
                'options'    => [
                    'label' => 'Password',
                ],
                'attributes' => [
                    'type' => 'password',
                ],
            ]
        );

        $this->add(
            [
                'type'    => Csrf::class,
                'name'    => 'security',
                'options' => [
                    'csrf_options' => [
                        'timeout' => $this->authOptions->getLoginFormTimeout(),
                    ],
                ],
            ]
        );

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Sign In')
            ->setAttributes(
                [
                    'type' => 'submit',
                ]
            );

        $this->add(
            $submitElement,
            [
                'priority' => -100,
            ]
        );
    }
}
