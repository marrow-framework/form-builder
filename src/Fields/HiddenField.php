<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/** An <input type="hidden"> — no label, no wrapper, no error/help display. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class HiddenField extends Field
{
    public function render(): string
    {
        return $this->renderInput('field_' . $this->name);
    }

    protected function renderInput(string $id): string
    {
        return '<input type="hidden" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '" value="' . htmlspecialchars((string) $this->value) . '">';
    }
}
