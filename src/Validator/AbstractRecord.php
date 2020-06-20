<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Validator;

use Exception;
use Laminas\Validator\AbstractValidator;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;
use LaminasFriends\Mvc\User\Validator\Exception\InvalidArgumentException;

abstract class AbstractRecord extends AbstractValidator
{
    /**
     * Error constants
     */
    public const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    public const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_NO_RECORD_FOUND => 'No record matching the input was found',
        self::ERROR_RECORD_FOUND    => 'A record matching the input was found',
    ];

    /**
     * @var UserMapperInterface
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $key;

    /**
     * Required options are:
     *  - key     Field to use, 'email' or 'username'
     */
    public function __construct(array $options)
    {
        if (!array_key_exists('key', $options)) {
            throw new InvalidArgumentException('No key provided');
        }

        $this->setKey($options['key']);

        parent::__construct($options);
    }

    /**
     * getMapper
     *
     * @return UserMapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * setMapper
     *
     * @param UserMapperInterface $mapper
     *
     * @return AbstractRecord
     */
    public function setMapper(UserMapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Grab the user from the mapper
     *
     * @param string $value
     * @return mixed
     */
    protected function query($value)
    {
        $result = false;

        switch ($this->getKey()) {
            case 'email':
                $result = $this->getMapper()->findByEmail($value);
                break;

            case 'username':
                $result = $this->getMapper()->findByUsername($value);
                break;

            default:
                throw new Exception('Invalid key used in ZfcUser validator');
                break;
        }

        return $result;
    }
}
