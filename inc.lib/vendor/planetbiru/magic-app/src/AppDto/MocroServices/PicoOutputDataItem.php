<?php

namespace MagicApp\AppDto\MocroServices;

use MagicObject\MagicObject;

/**
 * Class PicoOutputDataItem
 *
 * Represents an item of output data, typically used in the context of displaying or processing data 
 * in a list or table. This class stores and manages the associated data for the item, where each key 
 * corresponds to a field name, and the value holds the field's data. Additionally, it tracks various flags 
 * indicating the current status of the item, such as whether it is active, draft, or awaiting approval 
 * or other actions.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoOutputDataItem extends PicoEntityData
{
    /**
     * Associated data for the item. Each key represents a field name, 
     * and the value corresponds to the data for that field.
     *
     * @var array
     */
    protected $data;

    /**
     * Primary key values for the data item.
     *
     * @var PrimaryKeyValue[]
     */
    protected $primaryKeyValue;

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
     * The approval ID associated with the data item, if any.
     *
     * @var string|null
     */
    protected $approvalId;

    /**
     * Constructor for the PicoOutputDataItem class.
     * Initializes the properties with provided values. If a value is not provided for a property,
     * it will remain uninitialized (null).
     *
     * @param array|null $data The associated data for the item. Each key represents a field, 
     *                          and the value corresponds to the field's data.
     * @param MagicObject|null $entity Original data entity.
     * @param string[]|null $primaryKey List of primary keys for the data item.
     * @param PicoEntityInfo|null $entityInfo Object containing flags that indicate whether the item is a draft, final version, or has other status.
     */
    public function __construct($data, $entity = null, $primaryKey = null, $entityInfo = null)
    {
        if ($data !== null) {
            $this->data = $data;
        }

        if ($entity !== null) {
            $this->setPrimaryKeyValueFromEntity($entity, $primaryKey);

            // Set active status if provided in entity info
            if ($entityInfo->getActive() !== null) {
                $this->active = $entity->get($entityInfo->getActive());
            }

            // Set draft status if provided in entity info
            if ($entityInfo->getDraft() !== null) {
                $this->draft = $entity->get($entityInfo->getDraft());
            }

            // Set waitingFor status if provided in entity info
            if ($entityInfo->getWaitingFor() !== null) {
                $this->waitingFor = $entity->get($entityInfo->getWaitingFor());
            }

            // Set approval ID if provided in entity info
            if ($entityInfo->getApprovalId() !== null) {
                $this->approvalId = $entity->get($entityInfo->getApprovalId());
            }
        }
    }

    /**
     * Sets the primary key values from the provided entity.
     *
     * @param MagicObject $entity The entity from which to extract primary key values.
     * @param string[] $primaryKey List of primary keys.
     * @return self
     */
    private function setPrimaryKeyValueFromEntity($entity, $primaryKey)
    {
        if (isset($primaryKey) && is_array($primaryKey)) {
            $this->primaryKeyValue = [];
            foreach ($primaryKey as $pk) {
                if ($entity->get($pk) !== null) {
                    $this->primaryKeyValue[] = $entity->get($pk);
                }
            }
        }
        return $this;
    }
}
