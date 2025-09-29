<?php

namespace Opscale\NovaDynamicResources\Models\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use Swaggest\JsonSchema\Exception\InvalidValue;
use Swaggest\JsonSchema\Schema;

class JsonSchemaRule implements ValidationRule
{
    protected string $schemaFile;

    protected ?Schema $schema = null;

    /**
     * Create a new rule instance.
     *
     * @param  string  $schemaFile  The name of the schema file (without .json extension)
     */
    public function __construct(string $schemaFile)
    {
        $this->schemaFile = $schemaFile;
    }

    /**
     * Create a new instance for a specific schema file
     */
    public static function make(string $schemaFile): self
    {
        return new self($schemaFile);
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $schema = $this->getSchema();

            // Convert value to appropriate format for validation
            $data = $this->prepareValue($value);

            // Validate against schema
            $schema->in($data);

        } catch (InvalidValue $e) {
            $fail(sprintf('The %s field does not match the required schema: ', $attribute) . $e->getMessage());
        } catch (Exception $e) {
            $fail(sprintf('The %s field validation failed: ', $attribute) . $e->getMessage());
        }
    }

    /**
     * Get the JSON schema instance
     */
    protected function getSchema(): Schema
    {
        if (! $this->schema instanceof \Swaggest\JsonSchema\Schema) {
            $schemaPath = $this->getSchemaPath();

            if (! file_exists($schemaPath)) {
                throw new InvalidArgumentException('Schema file not found: ' . $schemaPath);
            }

            $schemaContent = file_get_contents($schemaPath);
            if ($schemaContent === false) {
                throw new InvalidArgumentException('Could not read schema file: ' . $schemaPath);
            }

            $schemaData = json_decode($schemaContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON in schema file: ' . json_last_error_msg());
            }

            $this->schema = Schema::import($schemaData);
        }

        return $this->schema;
    }

    /**
     * Get the full path to the schema file
     */
    protected function getSchemaPath(): string
    {
        $packagePath = dirname(__DIR__, 3); // Go up from src/Models/Rules to package root

        return $packagePath . '/resources/schemas/' . $this->schemaFile . '.json';
    }

    /**
     * Prepare the value for validation
     */
    protected function prepareValue(mixed $value): mixed
    {
        // If it's already an array, return as-is
        if (is_array($value)) {
            return $value;
        }

        // If it's a string, try to decode as JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Return the value as-is for other types
        return $value;
    }
}
