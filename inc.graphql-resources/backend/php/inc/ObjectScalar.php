<?php

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class ObjectScalar extends ScalarType
{
    public $name = 'Object';
    public $description = 'A custom scalar that can represent any JSON-like value: string, int, float, boolean, or null.';

    /**
     * Undocumented function
     *
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        // Return the value as-is
        return $value;
    }

    /**
     * Undocumented function
     *
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value)
    {
        // Return the value as-is from variables
        return $value;
    }

    /**
     * Undocumented function
     *
     * @param Node $valueNode
     * @param mixed $variables
     * @return mixed
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }
        if ($valueNode instanceof IntValueNode) {
            return (int) $valueNode->value;
        }
        if ($valueNode instanceof FloatValueNode) {
            return (float) $valueNode->value;
        }
        if ($valueNode instanceof BooleanValueNode) {
            return (bool) $valueNode->value;
        }
        // For NullValueNode, it will implicitly return null, which is correct.
        // For ListValueNode and ObjectValueNode, you would need more complex logic,
        // but for simple filter values, this is sufficient.
        return null;
    }
}