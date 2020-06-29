<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\Form\Element\Captcha;
use LaminasFriends\Mvc\User\Options\RegistrationOptionsInterface;

/**
 * Class RegisterForm
 */
class RegisterForm extends Base
{
    protected $captchaElement;

    /**
     * @var RegistrationOptionsInterface
     */
    protected RegistrationOptionsInterface $registrationOptions;

    /**
     * @param string|null $name
     * @param RegistrationOptionsInterface $options
     */
    public function __construct($name, RegistrationOptionsInterface $options)
    {
        $this->registrationOptions = $options;
        parent::__construct($name);

        if ($this->registrationOptions->getUseRegistrationFormCaptcha()) {
            $this->add(
                [
                    'name' => 'captcha',
                    'type' => Captcha::class,
                    'options' => [
                    'label' => 'Please type the following text',
                    'captcha' => $this->registrationOptions->getFormCaptchaOptions(),
                    ],
                ]
            );
        }

        $this->remove('userId');
        if (!$this->registrationOptions->getEnableUsername()) {
            $this->remove('username');
        }
        if (!$this->registrationOptions->getEnableDisplayName()) {
            $this->remove('display_name');
        }
        if ($this->captchaElement && $this->registrationOptions->getUseRegistrationFormCaptcha()) {
            $this->add($this->captchaElement, ['name' => 'captcha']);
        }
        $this->get('submit')->setLabel('RegisterForm');
    }

    public function setCaptchaElement(Captcha $captchaElement)
    {
        $this->captchaElement = $captchaElement;
    }
}
