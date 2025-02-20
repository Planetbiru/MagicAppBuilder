<?php

namespace MagicApp\AppDto\MocroServices;

use MagicObject\MagicObject;

/**
 * Class PicoUserFormOutputDetail
 *
 * Represents a collection of input fields for a user form during an insert operation. 
 * This class manages multiple `OutputFieldDetail` objects, each representing a field 
 * to be inserted into the form. It also includes an array of allowed actions (e.g., 
 * `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`) that define 
 * the possible operations that can be performed on the form fields.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserFormOutputDetail extends PicoEntityData
{

    /**
     * An array of input fields to be inserted into the form.
     * Each field is represented by an `OutputFieldDetail` object.
     *
     * @var OutputFieldDetail[]
     */
    protected $fields;
    
    /**
     * Flag indicating whether the data item is active or inactive. 
     * If `null`, the flag is considered inactive, and the front-end application 
     * should not use it for decision-making.
     *
     * @var bool|null
     */
    protected $active;
    
    /**
     * The current status of the data item, typically used to indicate 
     * whether the item is waiting for a specific action, such as approval, 
     * update, or another process. This status is represented by a `PicoFieldWaitingFor` object.
     *
     * @var PicoFieldWaitingFor|null
     */
    protected $waitingFor;

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
     * @param MagicObject|null $entity Original data entity.
     * @param PicoEntityInfo|null $entityInfo Object containing flags that indicate whether the item is a draft, final version, or has other status.
     */
    public function __construct($entity = null, $entityInfo = null)
    {
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
    
    /**
     * Add an allowed action to the output detail.
     *
     * This method adds an `OutputFieldDetail` object to the list of output that can be performed on the form fields. 
     *
     * @param OutputFieldDetail $output The `OutputFieldDetail` object to be added.
     */
    public function addOutput($output)
    {
        if (!isset($this->fields)) {
            $this->fields = [];
        }
        $this->fields[] = $output;
    }
}
