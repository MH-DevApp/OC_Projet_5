<?php

/**
 * AbstractForm file
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */

declare(strict_types=1);

namespace App\Factory\Form;


use App\Factory\Router\Request;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\Mapper\Mapper;
use App\Service\Container\Container;

/**
 * AbstractForm class
 *
 * Manage forms in the application with sanitize filter,
 * validation and errors.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class AbstractForm
{

    const ERROR_REQUIRED = "Ce champ est requis.";
    const ERROR_LENGTH = "Ce champ doit contenir entre %s et %s caractères.";
    const ERROR_MIN_LENGTH = "Ce champ doit contenir au minimum %s caractères.";
    const ERROR_MAX_LENGTH = "Ce champ doit contenir au maximum %s caractères.";
    const ERROR_BAD_FORMAT = "%s n'est pas valide.";
    const ERROR_CONFIRM = "Les mots de passe doivent être identiques.";
    const ERROR_UNIQUE = "%s existe déjà.";

    /**
     * @var array<string, array<string|int, mixed>> $fields
     */
    protected array $fields = [
        "sanitize" => [],
        "errors" => [],
        "data" => [],
        "validation" => [],
        "mapped" => []
    ];
    protected string $csrfKey;
    protected ?object $entity = null;
    protected Request $request;


    /**
     * Constructor
     *
     * @throws FormException
     */
    public function __construct(?object $entity = null)
    {
        /**
         * @var Request $request
         */
        $request = Container::getService("request");
        $this->request = $request;
        $this->entity = $entity;
        $this->builder();

    }


    /**
     * Build the form with initial verification
     *
     * @throws FormException
     */
    protected function builder(): void
    {
        if (!isset($this->csrfKey) || !$this->csrfKey) {
            throw new FormException(
                "The CSRF token is not set in the construct method. 
                \$this->csrfKey = \"string phrase or word\"."
            );
        }

        $this->fields["errors"]["global"] = "";

    }


    /**
     * Add a field with config sanitize, callable validation,
     * and mapped value.
     *
     * @param string $name
     * @param array<string, mixed> $options
     *
     * @return $this
     *
     * @throws FormException
     */
    protected function addField(string $name, array $options = []): self
    {
        // Default value mapped is true, add to mapped list

        if (!isset($options["mapped"]) || $options["mapped"]) {
            $this->fields["mapped"] = [...$this->fields["mapped"], $name];
        }

        // Initialise the errors for this field name

        $this->fields["errors"][$name] = "";

        // Initialise sanitize for this field name

        if (isset($options["sanitize"])) {

            // An array of sanitize must be to have "filter" and "flags" keys
            // Or sanitize must be numeric value

            if (
                (
                    is_array($options["sanitize"]) &&
                    (
                        !isset($options["sanitize"]["filter"]) ||
                        !isset($options["sanitize"]["flags"])
                    )
                ) || (
                    !is_array($options["sanitize"]) && !is_numeric($options["sanitize"])
                )
            ) {
                throw new FormException(
                    "You need set the keys \"filter\" and \"flags\" 
                    in the array sanitize options or set with a global variable filter."
                );
            }

            $this->fields["sanitize"][$name] = $options["sanitize"];

        } else {
            $this->fields["sanitize"][$name] = FILTER_DEFAULT;

        }

        // Initialise validation callable in the list of validations

        if (isset($options["validation"])) {

            if (!is_callable($options["validation"])) {
                throw new FormException(
                    "The validation option must be callable."
                );
            }

            $this->fields["validation"][$name] = $options["validation"];

        }

        return $this;

    }


    /**
     * Sanitize filter on the values on get in the request, and
     * Map the array values in the entity if it defined.
     *
     * @return $this
     */
    public function handleRequest(): self
    {
        /**
         * @var array<string, string> $data
         */
        $data = $this->request->getPost();

        $this->fields["data"] = filter_var_array(
            $data,
            $this->fields["sanitize"]
        ) ?: [];

        if ($this->request->getPost("_csrf")) {
            $this->fields["data"]["_csrf"] = $this->request->getPost("_csrf");
        }

        if ($this->fields["data"] && $this->entity) {
            $dataWithoutNotMapped = array_filter(
                $this->fields["data"],
                fn ($value, $key) => in_array($key, $this->fields["mapped"]),
                ARRAY_FILTER_USE_BOTH
            );
            $this->entity = Mapper::mapArrayToEntity($dataWithoutNotMapped, $this->entity);

        }

        return $this;

    }


    /**
     * Check if the request method is POST and return
     * true or false.
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->request->getMethod() === "POST";

    }


    /**
     * Check if the all fields in the form are valid, and
     * if valid then return true else false.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $result = true;

        /**
         * @var string $token
         */
        $token = $this->fields["data"]["_csrf"] ?? null;

        if (!$token || !Csrf::isTokenCsrfValid($token, $this->csrfKey)) {
            $this->setError(
                "global",
                sprintf(self::ERROR_BAD_FORMAT, "Le token CSRF")
            );
            $result = false;

        }


        /**
         * @var callable $callable
         */
        foreach ($this->fields["validation"] as $key => $callable) {

            if (!$callable($this->fields["data"][$key])) {
                $result = false;

            }

        }

        return $result;

    }


    // GETTERS // SETTERS

    /**
     * Get value on field or all fields with the name of field.
     *
     * @param string|null $field
     *
     * @return mixed
     */
    public function getData(?string $field = null): mixed
    {
        if ($field && !isset($this->fields["data"][$field])) {
            return null;
        }

        return $field ?
            $this->fields["data"]["$field"] :
            $this->fields["data"];

    }


    /**
     * Get all errors fields.
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        $errors = [];

        foreach ($this->fields["errors"] as $key => $error) {
            if (!empty($error)) {
                $errors[$key] = $error;
            }
        }

        /**
         * @var array<string, string>
         */
        return $errors;

    }


    /**
     * Get message error for a field.
     * Return null if it not exists.
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getError(string $field): ?string
    {
        if (!isset($this->fields["errors"][$field])) {
            return null;
        }

        /**
         * @var string
         */
        return $this->fields["errors"][$field];

    }


    /**
     * Set an error to the field in the list of errors.
     *
     * @param string $field
     * @param string $message
     *
     * @return void
     */
    public function setError(string $field, string $message): void
    {
        $this->fields["errors"][$field] = $message;

    }


}
