<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/** A single-line text input. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class CharField extends Field
{
    protected function renderInput(string $id): string
    {
        return '<input type="text" class="form-input" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '" value="' . htmlspecialchars((string) $this->value) . '"'
            . ($this->placeholder !== null ? $this->attr('placeholder', $this->placeholder) : '')
            . ($this->isRequired() ? ' required' : '')
            . '>';
    }
}
