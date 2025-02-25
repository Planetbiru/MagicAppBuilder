<?php

namespace AppBuilder;

/**
 * Class InputType
 *
 * Defines constants for different types of HTML form elements.
 * This class provides a centralized way to reference the various
 * element types used in form building, ensuring consistency
 * and reducing the risk of typos.
 */
class InputType
{
    /** @var string Constant for a text input element. */
    const TEXT = "text";

    /** @var string Constant for a textarea element. */
    const TEXTAREA = "textarea";

    /** @var string Constant for a select dropdown element. */
    const SELECT = "select";

    /** @var string Constant for a checkbox input element. */
    const CHECKBOX = "checkbox";
}
