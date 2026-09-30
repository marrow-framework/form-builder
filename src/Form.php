<?php

declare(strict_types=1);

namespace Marrow\FormBuilder;

use Marrow\Http\Request;
use Marrow\Validation\ValidatorFactory;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Django-style declarative form. Subclass and declare one typed property
 * per field, each carrying a field attribute:
 *
 *   class RegisterForm extends Form
 *   {
 *       #[CharField(label: 'Full name', rules: 'required|string|max:255')]
 *       public string $name;
 *
 *       #[EmailField(label: 'Email', rules: 'required|email|unique:users,email')]
 *       public string $email;
 *
 *       #[PasswordField(label: 'Password', rules: 'required|min:8|confirmed')]
 *       public string $password;
 *   }
 *
 * In a controller:
 *
 *   $form = RegisterForm::fromRequest($request);
 *   if ($form->isValid()) {
 *       User::create($form->cleanedData());
 *   } else {
 *       return $this->view('register', ['form' => $form]);
 *   }
 *
 * In Twig:
 *
 *   <form method="POST">
 *       {{ csrf_field() }}
 *       {{ form.render()|raw }}
 *       <button type="submit">Submit</button>
 *   </form>
 *
 * The declared property's own value is never read or written — it exists
 * purely as an attribute-carrying anchor Reflection can enumerate; the
 * actual bound value lives on the collected Field object instead
 * (accessible via field()/fields(), or through render()).
 *
 * Validation is not reimplemented: rules are the same pipe-separated
 * strings Validation\ValidatorInstance already runs, so every existing
 * rule works as-is — see docs/validation.md.
 */
abstract class Form
{
    /** @var array<string, Field> */
    private array $fields = [];

    private bool $validated = false;
    private bool $valid = false;

    /** @var array<string, string[]> */
    private array $errors = [];

    public function __construct(private readonly array $data = [])
    {
        $this->collectFields();
    }

    public static function fromRequest(Request $request): static
    {
        return new static($request->all());
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    // ── Validation ───────────────────────────────────────────────────

    public function isValid(): bool
    {
        if ($this->validated) {
            return $this->valid;
        }
        $this->validated = true;

        $rules = [];
        foreach ($this->fields as $name => $field) {
            if ($field->rules !== '') {
                $rules[$name] = $field->rules;
            }
        }

        $validator = (new ValidatorFactory())->make($this->data, $rules);
        $this->valid = $validator->passes();

        if (!$this->valid) {
            $this->errors = $validator->errors();
            foreach ($this->errors as $name => $messages) {
                if (isset($this->fields[$name])) {
                    $this->fields[$name]->errors = $messages;
                }
            }
        }

        return $this->valid;
    }

    /** @return array<string, string[]> Field name => error messages. Empty until isValid() has run. */
    public function errors(): array
    {
        $this->isValid();
        return $this->errors;
    }

    /**
     * The submitted values for every declared field, once validation has
     * passed. Calling this without checking isValid() first still runs
     * validation (and returns the raw submitted data regardless of the
     * result) — always check isValid() before trusting this for a write.
     *
     * @return array<string, mixed>
     */
    public function cleanedData(): array
    {
        $this->isValid();

        $out = [];
        foreach ($this->fields as $name => $field) {
            $out[$name] = $this->data[$name] ?? null;
        }
        return $out;
    }

    // ── Field access ─────────────────────────────────────────────────

    public function field(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
    }

    /** @return array<string, Field> */
    public function fields(): array
    {
        return $this->fields;
    }

    // ── Rendering ────────────────────────────────────────────────────

    /** Render every field in declaration order. Use field($name)->render() to place fields individually instead. */
    public function render(): string
    {
        $html = '';
        foreach ($this->fields as $field) {
            $html .= $field->render() . "\n";
        }
        return $html;
    }

    // ── Internal ─────────────────────────────────────────────────────

    private function collectFields(): void
    {
        $ref = new ReflectionClass($this);

        foreach ($ref->getProperties() as $prop) {
            $attributes = $prop->getAttributes(Field::class, ReflectionAttribute::IS_INSTANCEOF);
            if (empty($attributes)) {
                continue;
            }

            /** @var Field $field */
            $field = $attributes[0]->newInstance();
            $field->name = $prop->getName();
            $field->value = $this->data[$prop->getName()] ?? null;

            $this->fields[$prop->getName()] = $field;
        }
    }
}
