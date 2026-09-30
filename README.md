# Marrow Form Builder

Django-style declarative forms for [Marrow](https://github.com/marrow-framework/core):
define fields on the backend, validate with the framework's existing rule
engine, render on the frontend.

```bash
composer require marrow/form-builder
```

No further setup — there's no module to enable, no config file, nothing to
register. It's a plain library: `use` the classes where you need them.

## Why

- **No new validation engine.** A field's `rules` is the exact same
  pipe-separated string `Marrow\Validation\ValidatorInstance` already
  understands (`required|email|unique:users,email`, ...). Every existing
  rule works unchanged — there's nothing form-specific to learn on the
  validation side.
- **No PHP-fluent HTML builder.** Rendering is deliberately simple,
  semantic HTML (`form-field`, `form-label`, `form-input`, `form-error`
  classes — no framework-specific styling baked in) — style those classes
  yourself, or override rendering per-field for full control. A fluent
  `Form::open()->text(...)->submit()`-style API was considered and
  rejected: that pattern is what Laravel's own form builder looked like
  before being deprecated and removed, precisely because generating HTML
  from chained PHP calls ages badly the moment a form needs anything custom.

## Defining a form

```php
namespace Modules\Blog\Forms;

use Marrow\FormBuilder\Fields\CharField;
use Marrow\FormBuilder\Fields\ChoiceField;
use Marrow\FormBuilder\Fields\EmailField;
use Marrow\FormBuilder\Fields\TextareaField;
use Marrow\FormBuilder\Form;

class ContactForm extends Form
{
    #[CharField(label: 'Your name', rules: 'required|string|max:255')]
    public string $name;

    #[EmailField(label: 'Your email', rules: 'required|email')]
    public string $email;

    #[ChoiceField(
        label: 'Subject',
        rules: 'required|in:support,sales,other',
        choices: ['support' => 'Support', 'sales' => 'Sales', 'other' => 'Other'],
        placeholder: 'Choose one…',
    )]
    public string $subject;

    #[TextareaField(label: 'Message', rules: 'required|string|max:2000', rows: 6)]
    public string $message;
}
```

The declared property (`public string $name;`) is never read or written —
it exists purely so `Form` can enumerate it via reflection and find the
attribute on it. The attribute *is* the field: `CharField`, `EmailField`,
etc. are plain objects that also happen to be valid PHP attributes.

## Using it in a controller

```php
use Modules\Blog\Forms\ContactForm;

class ContactController extends Controller
{
    public function show(): Response
    {
        return $this->view('@blog/contact', ['form' => new ContactForm()]);
    }

    public function submit(Request $request): Response
    {
        $form = ContactForm::fromRequest($request);

        if (!$form->isValid()) {
            return $this->view('@blog/contact', ['form' => $form]); // re-shows with errors
        }

        Mail::send(new ContactMail($form->cleanedData()));
        return $this->view('@blog/contact', ['form' => new ContactForm(), 'sent' => true]);
    }
}
```

`Form::fromRequest($request)` binds `$request->all()`. On a failed
`isValid()`, every field's submitted value is retained (so re-rendering
shows what the visitor typed) **except password fields**, which never
re-populate.

## Rendering in Twig

```twig
<form method="POST" action="{{ route('contact.submit') }}">
    {{ csrf_field() }}
    {{ form.render()|raw }}
    <button type="submit">Send</button>
</form>
```

Or field by field, for custom layout:

```twig
{{ form.field('email').render()|raw }}
{{ form.field('message').render()|raw }}
```

`|raw` is required — `render()` returns HTML, and Twig auto-escapes by
default. The HTML itself already escapes every submitted value and error
message internally (`htmlspecialchars()`), so this is safe.

## The `Form` API

| Method | Returns |
|---|---|
| `new Form($data = [])` | bind an array directly |
| `Form::fromRequest($request)` | bind `$request->all()` |
| `isValid(): bool` | runs validation once (cached), returns pass/fail |
| `errors(): array` | `['field' => ['message', ...], ...]`, empty until checked |
| `cleanedData(): array` | submitted values for every declared field |
| `field(string $name): ?Field` | one field, for custom rendering |
| `fields(): array` | every field, `name => Field`, in declaration order |
| `render(): string` | every field rendered, in declaration order |

## Fields reference

| Field | Renders | Notes |
|---|---|---|
| `CharField` | `<input type="text">` | |
| `EmailField` | `<input type="email">` | pair with the `email` rule for real format validation |
| `PasswordField` | `<input type="password">` | never re-populates on re-render, even after a failed submission |
| `TextareaField` | `<textarea>` | extra `rows: int` param |
| `IntegerField` | `<input type="number">` | pair with `integer`/`gte:n`/`lte:n` rules |
| `BooleanField` | `<input type="checkbox">` | label renders inline, after the input, not above it |
| `ChoiceField` | `<select>` | `choices: array` (value => label); `placeholder` becomes a disabled first `<option>` |
| `HiddenField` | `<input type="hidden">` | no label, no wrapper, no error/help display |

Every field constructor accepts `label`, `rules`, `help`, `placeholder`
(field-specific ones add their own, e.g. `TextareaField`'s `rows` and
`ChoiceField`'s `choices`).

## Writing a custom field

```php
namespace App\Forms\Fields;

use Attribute;
use Marrow\FormBuilder\Field;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ColorField extends Field
{
    protected function renderInput(string $id): string
    {
        return '<input type="color" class="form-input" id="' . htmlspecialchars($id)
            . '" name="' . htmlspecialchars($this->name) . '" value="'
            . htmlspecialchars((string) $this->value) . '">';
    }
}
```

Every concrete field class needs its own `#[Attribute(Attribute::TARGET_PROPERTY)]`
marker — PHP requires it directly on the class actually used in
`#[ColorField(...)]` syntax; inheriting `Field` doesn't carry it over
automatically. Override `render()` entirely instead of just `renderInput()`
for a field whose whole layout differs from the label-above-input default
(see `BooleanField`/`HiddenField`'s source for two real examples).

## Requirements

PHP 8.2+, `marrow/framework`.

## License

MIT.
# form-builder
