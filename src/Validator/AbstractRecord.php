<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Validator;

use Exception;
use Laminas\Validator\AbstractValidator;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

/**
 * Class AbstractRecord
 */
abstract class AbstractRecord extends AbstractValidator
{
    /**
     * Error constants
     */
    public const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    public const ERROR_RECORD_FOUND    = 'recordFound';

    protected array $messageTemplates = [
        self::ERROR_NO_RECORD_FOUND => 'No record matching the input was found',
        self::ERROR_RECORD_FOUND    => 'A record matching the input was found',
    ];
    protected UserMapperInterface $mapper;
    protected string $key;

    /**
     * AbstractRecord constructor.
     *
     * @param string              $key
     * @param UserMapperInterface $userMapper
     * @param array               $options
     */
    public function __construct(string $key, UserMapperInterface $userMapper, array $options = [])
    {
        $this->key = $key;
        $this->mapper = $userMapper;

        parent::__construct($options);
    }

    /**
     * Grab the user from the mapper
     *
     * @param string $value
     * @return mixed
     */
    protected function query($value)
    {
        switch ($this->key) {
            case 'email':
                return $this->mapper->findByEmail($value);
            case 'username':
                return $this->mapper->findByUsername($value);
            default:
                throw new Exception('Invalid key used in MvcUser validator');
        }
    }
}
