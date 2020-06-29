<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Validator;

class NoRecordExists extends AbstractRecord
{
    public function isValid($value): bool
    {
        $valid = true;
        $this->setValue($value);

        $result = $this->query($value);
        if ($result) {
            $valid = false;
            $this->error(self::ERROR_RECORD_FOUND);
        }

        return $valid;
    }
}
