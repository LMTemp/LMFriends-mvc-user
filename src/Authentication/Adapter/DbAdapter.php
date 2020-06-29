<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Adapter;

use Laminas\Authentication\Result as AuthenticationResult;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Crypt\Password\Exception\RuntimeException;
use Laminas\Session\Container as SessionContainer;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;
use LaminasFriends\Mvc\User\Options\UserServiceOptionsInterface;

/**
 * Class DbAdapter
 */
class DbAdapter extends AbstractAdapter
{
    protected UserMapperInterface $mapper;
    protected UserServiceOptionsInterface $options;

    /**
     * @var callable
     */
    protected $credentialPreprocessor;

    /**
     * DbAdapter constructor.
     *
     * @param UserMapperInterface         $userMapper
     * @param UserServiceOptionsInterface $options
     */
    public function __construct(UserMapperInterface $userMapper, UserServiceOptionsInterface $options)
    {
        $this->mapper = $userMapper;
        $this->options = $options;
    }

    /**
     * Called when user id logged out
     * @param AdapterChainEvent $e
     * @return void
     */
    public function logout(AdapterChainEvent $e): void
    {
        $this->getStorage()->clear();
    }

    /**
     * @param AdapterChainEvent $e
     * @return bool
     */
    public function authenticate(AdapterChainEvent $e)
    {
        if ($this->isSatisfied()) {
            $storage = $this->getStorage()->read();
            $e->setIdentity($storage['identity'])
              ->setCode(AuthenticationResult::SUCCESS)
              ->setMessages(['Authentication successful.']);
            return;
        }

        $identity   = $e->getRequest()->getPost()->get('identity');
        $credential = $e->getRequest()->getPost()->get('credential');
        $credential = $this->preProcessCredential($credential);
        /** @var UserEntityInterface|null $userObject */
        $userObject = null;

        // Cycle through the configured identity sources and test each
        $fields = $this->options->getAuthIdentityFields();
        while (!is_object($userObject) && count($fields) > 0) {
            $mode = array_shift($fields);
            switch ($mode) {
                case 'username':
                    $userObject = $this->mapper->findByUsername($identity);
                    break;
                case 'email':
                    $userObject = $this->mapper->findByEmail($identity);
                    break;
            }
        }

        if (!$userObject) {
            $e->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
              ->setMessages(['A record with the supplied identity could not be found.']);
            $this->setSatisfied(false);
            return false;
        }

        if ($this->options->getEnableUserState()) {
            // Don't allow user to login if state is not in allowed list
            if (!in_array($userObject->getState(), $this->options->getAllowedLoginStates())) {
                $e->setCode(AuthenticationResult::FAILURE_UNCATEGORIZED)
                  ->setMessages(['A record with the supplied identity is not active.']);
                $this->setSatisfied(false);
                return false;
            }
        }

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());
        if (!$bcrypt->verify($credential, $userObject->getPassword())) {
            // Password does not match
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
              ->setMessages(['Supplied credential is invalid.']);
            $this->setSatisfied(false);
            return false;
        }

        // regen the id
        $session = new SessionContainer($this->getStorage()->getNamespace());
        $session->getManager()->regenerateId();

        // Success!
        $e->setIdentity($userObject->getId());
        // Update user's password hash if the cost parameter has changed
        $this->updateUserPasswordHash($userObject, $credential, $bcrypt);
        $this->setSatisfied();
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)
          ->setMessages(['Authentication successful.']);
    }

    /**
     * @param UserEntityInterface $userObject
     * @param                     $password
     * @param Bcrypt              $bcrypt
     *
     * @throws RuntimeException
     */
    protected function updateUserPasswordHash(UserEntityInterface $userObject, $password, Bcrypt $bcrypt): void
    {
        $hash = explode('$', $userObject->getPassword());
        if ($hash[2] === $bcrypt->getCost()) {
            return;
        }
        $userObject->setPassword($bcrypt->create($password));
        $this->mapper->update($userObject);
    }

    public function preProcessCredential($credential)
    {
        $processor = $this->getCredentialPreprocessor();
        if (is_callable($processor)) {
            return $processor($credential);
        }

        return $credential;
    }

    /**
     * Get credentialPreprocessor.
     *
     * @return callable
     */
    public function getCredentialPreprocessor()
    {
        return $this->credentialPreprocessor;
    }

    /**
     * Set credentialPreprocessor.
     *
     * @param callable $credentialPreprocessor
     * @return $this
     */
    public function setCredentialPreprocessor($credentialPreprocessor)
    {
        $this->credentialPreprocessor = $credentialPreprocessor;
        return $this;
    }
}
