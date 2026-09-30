<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/** A multi-line <textarea>. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class TextareaField extends Field
{
    public function __construct(
        string $label = '',
        string $rules = '',
        string $help = '',
        ?string $placeholder = null,
        public readonly int $rows = 4,
    ) {
        parent::__construct($label, $rules, $help, $placeholder);
    }

    protected function renderInput(string $id): string
    {
        return '<textarea class="form-input" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '" rows="' . $this->rows . '"'
            . ($this->placeholder !== null ? $this->attr('placeholder', $this->placeholder) : '')
            . ($this->isRequired() ? ' required' : '')
            . '>' . htmlspecialchars((string) $this->value) . '</textarea>';
    }
}
