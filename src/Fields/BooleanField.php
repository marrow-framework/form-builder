<?php

declare(strict_types=1);

namespace Marrow\FormBuilder\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

/**
 * An <input type="checkbox">. Overrides the whole render() (not just
 * renderInput()) since a checkbox conventionally puts its label *after*
 * the input, inline, rather than above it like every other field type.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BooleanField extends Field
{
    public function render(): string
    {
        $id = 'field_' . $this->name;
        $hasError = !empty($this->errors);
        $checked = $this->value ? ' checked' : '';

        $errorHtml = $hasError
            ? '<div class="form-error">' . implode('<br>', array_map('htmlspecialchars', $this->errors)) . '</div>'
            : '';
        $helpHtml = $this->help !== ''
            ? '<div class="form-help">' . htmlspecialchars($this->help) . '</div>'
            : '';

        return sprintf(
            '<div class="form-field form-field-checkbox%s">' . "\n"
            . '    <label class="form-label-inline" for="%s">'
            . '<input type="checkbox" class="form-checkbox" id="%s" name="%s" value="1"%s> %s</label>' . "\n"
            . '    %s%s' . "\n"
            . '</div>',
            $hasError ? ' has-error' : '',
            htmlspecialchars($id),
            htmlspecialchars($id),
            htmlspecialchars($this->name),
            $checked,
            htmlspecialchars($this->label !== '' ? $this->label : ucfirst($this->name)),
            $helpHtml,
            $errorHtml
        );
    }

    protected function renderInput(string $id): string
    {
        // Unused — render() is fully overridden above.
        return '';
    }
}
