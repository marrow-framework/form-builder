<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/**
 * A <select> dropdown. $choices is value => label, e.g.
 * ['draft' => 'Draft', 'published' => 'Published']. Pair with the 'in:a,b,c'
 * validation rule (built from array_keys($choices) automatically if $rules
 * is left empty and the field is otherwise required — see rulesWithChoices()).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ChoiceField extends Field
{
    /** @param array<string, string> $choices */
    public function __construct(
        string $label = '',
        string $rules = '',
        string $help = '',
        ?string $placeholder = null,
        public readonly array $choices = [],
    ) {
        parent::__construct($label, $rules, $help, $placeholder);
    }

    protected function renderInput(string $id): string
    {
        $options = '';
        if ($this->placeholder !== null) {
            $options .= '<option value="">' . htmlspecialchars($this->placeholder) . '</option>';
        }
        foreach ($this->choices as $value => $optionLabel) {
            $selected = ((string) $this->value === (string) $value) ? ' selected' : '';
            $options .= '<option value="' . htmlspecialchars((string) $value) . '"' . $selected . '>'
                . htmlspecialchars($optionLabel) . '</option>';
        }

        return '<select class="form-input" id="' . htmlspecialchars($id) . '" name="'
            . htmlspecialchars($this->name) . '"'
            . ($this->isRequired() ? ' required' : '')
            . '>' . $options . '</select>';
    }
}
