<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/** An <input type="email">. Pair with the 'email' validation rule for real format checking. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class EmailField extends Field
{
    protected function renderInput(string $id): string
    {
        return '<input type="email" class="form-input" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '" value="' . htmlspecialchars((string) $this->value) . '"'
            . ($this->placeholder !== null ? $this->attr('placeholder', $this->placeholder) : '')
            . ($this->isRequired() ? ' required' : '')
            . '>';
    }
}
