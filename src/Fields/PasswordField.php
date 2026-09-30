<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/**
 * An <input type="password">. Never re-populates the submitted value on
 * re-render (even after a failed validation) — echoing a password back
 * into the HTML, even into a password input, is an unnecessary risk for
 * essentially no usability gain (nobody expects a password field to
 * survive a validation error the way a text field does).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PasswordField extends Field
{
    protected function renderInput(string $id): string
    {
        return '<input type="password" class="form-input" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '" value=""'
            . ($this->placeholder !== null ? $this->attr('placeholder', $this->placeholder) : '')
            . ($this->isRequired() ? ' required' : '')
            . '>';
    }
}
