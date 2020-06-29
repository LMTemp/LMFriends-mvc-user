<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Validator;

class RecordExists extends AbstractRecord
{
    public function isValid($value): bool
    {
        $valid = true;
        $this->setValue($value);

        $result = $this->query($value);
        if (!$result) {
            $valid = false;
            $this->error(self::ERROR_NO_RECORD_FOUND);
        }

        return $valid;
    }
}
