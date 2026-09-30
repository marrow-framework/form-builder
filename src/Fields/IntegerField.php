<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/** An <input type="number">. Pair with 'integer'/'gte:n'/'lte:n' validation rules. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerField extends Field
{
    protected function renderInput(string $id): string
    {
        return '<input type="number" class="form-input" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '" value="' . htmlspecialchars((string) $this->value) . '"'
            . ($this->placeholder !== null ? $this->attr('placeholder', $this->placeholder) : '')
            . ($this->isRequired() ? ' required' : '')
            . '>';
    }
}
