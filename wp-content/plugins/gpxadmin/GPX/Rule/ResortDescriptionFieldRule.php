<?php

namespace GPX\Rule;

use GPX\Model\Resort;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;

class ResortDescriptionFieldRule implements DataAwareRule, InvokableRule
{
    protected array $data = [];
    protected string $column;
    protected ?string $field;

    public function __construct(string $column = 'field', string $field = null)
    {
        $this->column = $column;
        $this->field = $field;
    }

    public function __invoke($attribute, $value, $fail): void
    {
        $column = $this->field ?? $this->column;
        if (!isset($this->data[$column])) {
            $fail('Field name not provided.');
        }
        $field = Resort::descriptionFields()->where('name', $this->data[$column])->first();
        if (isset($field['attributes']['maxlength'])) {
            if (mb_strlen($value) > $field['attributes']['maxlength']) {
                $fail(sprintf('%s must be no more than %d characters.', $field['label'], $field['attributes']['maxlength']));
            }
        }
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
