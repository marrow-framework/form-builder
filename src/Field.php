<?php

declare(strict_types=1);

namespace Marrow\FormBuilder;

/**
 * Base class for every form field type. A concrete field (see Fields/)
 * doubles as both a plain object and a PHP attribute — the same class you
 * put in #[EmailField(...)] on a Form property is the object Form collects
 * at runtime and renders. Each concrete subclass carries its own
 * #[\Attribute(...)] marker; this abstract base does not, since it's never
 * used as an attribute directly.
 *
 * Validation is not reimplemented here — $rules is the exact same
 * pipe-separated rule string Validation\ValidatorInstance already
 * understands (see docs/validation.md), so every existing rule ("required",
 * "email", "min:8", "confirmed", "unique:table,column", ...) works
 * unchanged. Form::isValid() hands the collected fields' rules straight to
 * ValidatorFactory.
 */
abstract class Field
{
    /** Set by Form::collectFields() from the property name the attribute was declared on. */
    public string $name = '';

    /** Set by Form from the bound request/array data for this field's name. */
    public mixed $value = null;

    /** @var string[] Populated by Form::isValid() after a failed validation. */
    public array $errors = [];

    public function __construct(
        public readonly string $label = '',
        public readonly string $rules = '',
        public readonly string $help = '',
        public readonly ?string $placeholder = null,
    ) {
    }

    /** Render the field's <input>/<select>/<textarea> element itself (no label/wrapper). */
    abstract protected function renderInput(string $id): string;

    /**
     * Render the full field: wrapper, label, input, help text, and errors.
     * Override entirely (rather than just renderInput()) for a field with
     * no label, like HiddenField.
     *
     * Semantic class names only (form-field, form-label, form-input,
     * form-help, form-error, has-error) — no framework-specific (Tailwind,
     * Bootstrap, ...) styling is baked in here on purpose, so this package
     * doesn't force a CSS choice on the app. Style those classes yourself,
     * or override render()/renderInput() per field for full control.
     */
    public function render(): string
    {
        $id = 'field_' . $this->name;
        $hasError = !empty($this->errors);

        $errorHtml = $hasError
            ? '<div class="form-error">' . implode('<br>', array_map('htmlspecialchars', $this->errors)) . '</div>'
            : '';

        $helpHtml = $this->help !== ''
            ? '<div class="form-help">' . htmlspecialchars($this->help) . '</div>'
            : '';

        $requiredMark = $this->isRequired() ? ' <span class="form-required">*</span>' : '';

        return sprintf(
            '<div class="form-field%s">' . "\n"
            . '    <label class="form-label" for="%s">%s%s</label>' . "\n"
            . '    %s' . "\n"
            . '    %s%s' . "\n"
            . '</div>',
            $hasError ? ' has-error' : '',
            $id,
            htmlspecialchars($this->label !== '' ? $this->label : ucfirst($this->name)),
            $requiredMark,
            $this->renderInput($id),
            $helpHtml,
            $errorHtml
        );
    }

    public function isRequired(): bool
    {
        return str_contains($this->rules, 'required');
    }

    protected function attr(string $name, string $value): string
    {
        return sprintf(' %s="%s"', $name, htmlspecialchars($value));
    }
}
