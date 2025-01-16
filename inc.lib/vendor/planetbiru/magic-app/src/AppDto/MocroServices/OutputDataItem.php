<?php

namespace MagicApp\AppDto\MocroServices;

use MagicObject\MagicDto;

/**
 * Class OutputDataItem
 *
 * Represents an item of output data, typically used in the context of displaying or processing data 
 * in a list or table. This class stores and manages the associated data for the item, where each key 
 * corresponds to a field name, and the value holds the field's data. Additionally, it tracks various flags 
 * indicating the current status of the item, such as whether it is active, draft, or awaiting approval 
 * or other actions.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class OutputDataItem extends DataConstructor
{
    /**
     * Associated data for the item. Each key represents a field name, 
     * and the value corresponds to the data for that field.
     *
     * @var array
     */
    protected $data;

    /**
     * The current status of the data item, typically used to indicate 
     * whether the item is waiting for a specific action, such as approval, 
     * update, or another process. This status is represented by a `FieldWaitingFor` object.
     *
     * @var FieldWaitingFor|null
     */
    protected $waitingFor;

    /**
     * Flag indicating whether the data item is active or inactive. 
     * If `null`, the flag is considered inactive, and the front-end application 
     * should not use it for decision-making.
     *
     * @var bool|null
     */
    protected $active;
    
    /**
     * Flag indicating whether the data item is in draft status. 
     * If `null`, the draft flag is not used by the front-end application.
     *
     * @var bool|null
     */
    protected $draft;
    
    /**
     * Constructor for the OutputDataItem class.
     * Initializes the properties with provided values. If a value is not provided for a property,
     * it will remain uninitialized (null).
     *
     * @param array|null $data The associated data for the item. Each key represents a field, 
     *                          and the value corresponds to the field's data.
     * @param FieldWaitingFor|null $waitingFor The current status of the data item, 
     *                                          typically representing a process awaiting an action.
     * @param bool|null $active Flag indicating if the item is active or inactive.
     * @param bool|null $draft Flag indicating if the item is a draft or final version.
     */
    public function __construct($data = null, $waitingFor = null, $active = null, $draft = null)
    {
        if ($data !== null) {
            $this->data = $data;
        }
        if ($waitingFor !== null) {
            $this->waitingFor = $waitingFor;
        }
        if ($active !== null) {
            $this->active = $active;
        }
        if ($draft !== null) {
            $this->draft = $draft;
        }
    }
}
