<?php

namespace App\Http\Requests\Concerns;

trait NullifiesEmptyFields
{
    /**
     * @param  list<string>  $fields
     */
    protected function nullify(array $fields): void
    {
        $values = [];

        foreach ($fields as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $values[$field] = null;
            }
        }

        if ($values !== []) {
            $this->merge($values);
        }
    }
}
