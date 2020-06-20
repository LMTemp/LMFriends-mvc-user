<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Laminas\Form\Element\Captcha;
use LaminasFriends\Mvc\User\Options\RegistrationOptionsInterface;

class RegisterForm extends Base
{
    protected $captchaElement;

    /**
     * @var RegistrationOptionsInterface
     */
    protected $registrationOptions;

    /**
     * @param string|null $name
     * @param RegistrationOptionsInterface $options
     */
    public function __construct($name, RegistrationOptionsInterface $options)
    {
        $this->setRegistrationOptions($options);

        parent::__construct($name);

        if ($this->getRegistrationOptions()->getUseRegistrationFormCaptcha()) {
            $this->add(
                [
                    'name' => 'captcha',
                    'type' => Captcha::class,
                    'options' => [
                    'label' => 'Please type the following text',
                    'captcha' => $this->getRegistrationOptions()->getFormCaptchaOptions(),
                    ],
                ]
            );
        }

        $this->remove('userId');
        if (!$this->getRegistrationOptions()->getEnableUsername()) {
            $this->remove('username');
        }
        if (!$this->getRegistrationOptions()->getEnableDisplayName()) {
            $this->remove('display_name');
        }
        if ($this->captchaElement && $this->getRegistrationOptions()->getUseRegistrationFormCaptcha()) {
            $this->add($this->captchaElement, ['name' => 'captcha']);
        }
        $this->get('submit')->setLabel('RegisterForm');
    }

    public function setCaptchaElement(Captcha $captchaElement)
    {
        $this->captchaElement= $captchaElement;
    }

    /**
     * Set Registration Options
     *
     * @param RegistrationOptionsInterface $registrationOptions
     *
     * @return RegisterForm
     */
    public function setRegistrationOptions(RegistrationOptionsInterface $registrationOptions)
    {
        $this->registrationOptions = $registrationOptions;
        return $this;
    }

    /**
     * Get Registration Options
     *
     * @return RegistrationOptionsInterface
     */
    public function getRegistrationOptions()
    {
        return $this->registrationOptions;
    }
}
