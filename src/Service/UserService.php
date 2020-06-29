<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Service;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Crypt\Password\Exception\InvalidArgumentException;
use Laminas\Crypt\Password\Exception\RuntimeException;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\FormInterface;
use Laminas\Crypt\Password\Bcrypt;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;
use LaminasFriends\Mvc\User\Options\UserServiceOptionsInterface;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;

/**
 * Class UserService
 */
class UserService implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected UserMapperInterface $userMapper;
    protected AuthenticationServiceInterface $authService;
    protected FormInterface $registerForm;
    protected UserServiceOptionsInterface $options;

    /**
     * UserService constructor.
     *
     * @param UserServiceOptionsInterface    $moduleOptions
     * @param UserMapperInterface            $userMapper
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(
        UserServiceOptionsInterface $moduleOptions,
        UserMapperInterface $userMapper,
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->options = $moduleOptions;
        $this->userMapper = $userMapper;
        $this->authService = $authenticationService;
    }

    /**
     * Register user
     * @param UserEntityInterface $newUser
     *
     * @return UserEntityInterface
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function register(UserEntityInterface $newUser): UserEntityInterface
    {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->getOptions()->getPasswordCost());
        $newUser->setPassword($bcrypt->create($newUser->getPassword()));

        $this->getEventManager()->trigger(__FUNCTION__, $this, ['user' => $newUser]);
        $this->userMapper->insert($newUser);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, ['user' => $newUser]);
        return $newUser;
    }

    /**
     * change the current users password
     *
     * @param array $data
     * @return bool
     */
    public function changePassword(array $data): bool
    {
        $currentUser = $this->getAuthService()->getIdentity();

        $oldPass = $data['credential'];
        $newPass = $data['newCredential'];

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->getOptions()->getPasswordCost());

        if (!$bcrypt->verify($oldPass, $currentUser->getPassword())) {
            return false;
        }

        $pass = $bcrypt->create($newPass);
        $currentUser->setPassword($pass);

        $this->getEventManager()->trigger(__FUNCTION__, $this, ['user' => $currentUser, 'data' => $data]);
        $this->userMapper->update($currentUser);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, ['user' => $currentUser, 'data' => $data]);

        return true;
    }

    public function changeEmail(array $data): bool
    {
        $currentUser = $this->getAuthService()->getIdentity();

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->getOptions()->getPasswordCost());

        if (!$bcrypt->verify($data['credential'], $currentUser->getPassword())) {
            return false;
        }

        $currentUser->setEmail($data['newIdentity']);

        $this->getEventManager()->trigger(__FUNCTION__, $this, ['user' => $currentUser, 'data' => $data]);
        $this->userMapper->update($currentUser);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, ['user' => $currentUser, 'data' => $data]);

        return true;
    }

    /**
     * getAuthService
     *
     * @return AuthenticationServiceInterface
     */
    public function getAuthService(): AuthenticationServiceInterface
    {
        return $this->authService;
    }

    /**
     * get service options
     *
     * @return UserServiceOptionsInterface
     */
    public function getOptions(): UserServiceOptionsInterface
    {
        return $this->options;
    }
}
