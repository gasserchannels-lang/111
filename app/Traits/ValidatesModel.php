<?php

declare(strict_types=1);

namespace App\Traits;

trait ValidatesModel
{
    /**
     * Validate the model attributes using Laravel's validator.
     */
    public function validate(): bool
    {
        $validator = validator($this->attributes, $this->getRules());

        if ($validator->fails()) {
            $this->errors = $validator->errors()->toArray();

            return false;
        }

        return true;
    }

    /**
     * Get validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * Check if the model has validation errors.
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
