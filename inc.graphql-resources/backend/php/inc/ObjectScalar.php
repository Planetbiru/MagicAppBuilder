<?php

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

/**
 * Class ObjectScalar
 *
 * A custom GraphQL scalar type that represents any JSON-like primitive value.
 *
 * This scalar allows GraphQL fields to accept and return flexible data types
 * such as:
 * - string
 * - integer
 * - float
 * - boolean
 * - null
 *
 * It is useful for situations where the schema requires supporting dynamic
 * values without enforcing strict type definitions. Complex structures such as
 * lists and objects can be supported with additional parsing logic if required.
 */
class ObjectScalar extends ScalarType
{
    /** @var string The name of the custom scalar. */
    public $name = 'Object';

    /** @var string Description of the scalar behavior and purpose. */
    public $description = 'A custom scalar that can represent any JSON-like value: string, int, float, boolean, or null.';

    /**
     * Serializes internal PHP values to be sent to the client.
     *
     * This method returns the value unchanged, allowing any primitive
     * JSON-compatible type to pass through.
     *
     * @param mixed $value The PHP value to serialize.
     * @return mixed The serialized value sent back to the client.
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Parses externally provided variable values into the internal
     * representation used by the application.
     *
     * This implementation returns the value without modification.
     *
     * @param mixed $value The input value from GraphQL variables.
     * @return mixed The parsed value.
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * Parses GraphQL literal values (inline values in queries)
     * into PHP types.
     *
     * Supported types include:
     * - StringValueNode → string
     * - IntValueNode → integer
     * - FloatValueNode → float
     * - BooleanValueNode → boolean
     * - NullValueNode → null (implicitly handled)
     *
     * Parsing of complex types such as lists and objects is not implemented
     * here but can be added if needed.
     *
     * @param Node $valueNode The AST node representing the literal value.
     * @param array|null $variables Variables passed alongside the query (unused).
     * @return mixed The converted PHP value.
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        $result = null;

        if ($valueNode instanceof StringValueNode) {
            $result = $valueNode->value;
        } elseif ($valueNode instanceof IntValueNode) {
            $result = (int) $valueNode->value;
        } elseif ($valueNode instanceof FloatValueNode) {
            $result = (float) $valueNode->value;
        } elseif ($valueNode instanceof BooleanValueNode) {
            $result = (bool) $valueNode->value;
        }
        // For NullValueNode, $result remains null.
        // Parsing ListValueNode or ObjectValueNode would require additional logic.

        return $result;
    }
}
