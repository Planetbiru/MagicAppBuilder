<?php

namespace MagicApp;

use MagicObject\SecretObject;

/**
 * Class EntityInfo
 *
 * Represents detailed information about an entity, including its attributes 
 * such as name, sorting order, status keys (active, draft), and metadata 
 * related to creation, editing, and approval processes.
 * 
 * This class extends `SecretObject`, implying that it handles sensitive 
 * or controlled data, which may include actions and IP addresses 
 * associated with entity creation and editing.
 */
class EntityInfo extends SecretObject
{
    /**
     * The name of the entity.
     *
     * This property stores the name of the entity, which can be used for display
     * purposes or for identification within the system.
     *
     * @var string
     */
    protected $name;
    
    /**
     * The sort order for the entity.
     *
     * This property defines the order in which the entity should be sorted in 
     * lists or views within the application.
     *
     * @var string
     */
    protected $sortOrder;
    
    /**
     * Indicates whether the entity is active.
     *
     * This property stores a key or value representing whether the entity is 
     * currently active. It may be used to filter active entities in queries or 
     * lists.
     *
     * @var string
     */
    protected $active;
    
    /**
     * Indicates whether the entity is in draft state.
     *
     * This property stores a key or value indicating whether the entity is 
     * currently in draft status, which might affect whether it is shown publicly 
     * or is editable.
     *
     * @var string
     */
    protected $draft;
    
    /**
     * Indicates if the entity was created by an admin.
     *
     * This property stores a key or value that marks whether the entity was 
     * created by an admin, which may influence the permissions or actions 
     * available for this entity.
     *
     * @var string
     */
    protected $adminCreate;
    
    /**
     * Indicates if the entity was edited by an admin.
     *
     * This property stores a key or value that marks whether the entity was 
     * edited by an admin, providing insight into the entity's history and 
     * changes made by admin users.
     *
     * @var string
     */
    protected $adminEdit;
    
    /**
     * The ID of the admin who requested the edit for the entity.
     *
     * This property stores the ID of the admin user who made a request for the entity 
     * to be edited. This can be used to track which admin initiated the request 
     * for an edit, providing insight into workflows or permissions.
     *
     * @var string
     */
    protected $adminAskEdit;
    
    /**
     * The IP address from which the entity was created.
     *
     * This property stores the IP address used during the creation of the entity.
     * It could be used for auditing, logging, or security purposes.
     *
     * @var string
     */
    protected $ipCreate;
    
    /**
     * The IP address from which the entity was last edited.
     *
     * This property stores the IP address used when editing the entity.
     * Similar to `ipCreate`, it provides tracking of user actions on the entity.
     *
     * @var string
     */
    protected $ipEdit;
    
    /**
     * The IP address from which an edit request was made.
     *
     * This property tracks the IP address from which a request was made to edit 
     * the entity, which could be useful for auditing or monitoring purposes.
     *
     * @var string
     */
    protected $ipAskEdit;
    
    /**
     * The timestamp of when the entity was created.
     *
     * This property stores the exact time the entity was created in the system.
     * It is generally used for logging, tracking, or sorting entities by their 
     * creation time.
     *
     * @var string
     */
    protected $timeCreate;
    
    /**
     * The timestamp of when the entity was last edited.
     *
     * This property records the timestamp when the entity was last modified.
     * It is useful for tracking changes and determining the recency of updates.
     *
     * @var string
     */
    protected $timeEdit;
    
    /**
     * The timestamp of when an edit request was made.
     *
     * This property stores the timestamp when an admin requested an edit to 
     * the entity, allowing for tracking of workflows and approval timelines.
     *
     * @var string
     */
    protected $timeAskEdit;
    
    /**
     * The key or value representing a pending action.
     *
     * This property stores a key or value that indicates whether there is 
     * something waiting for the entity, such as approval or processing.
     *
     * @var string
     */
    protected $waitingFor;
    
    /**
     * The approval ID associated with the entity.
     *
     * This property stores the ID of the approval process associated with the 
     * entity, helping to track the state of the entity within an approval workflow.
     *
     * @var string
     */
    protected $approvalId;
}
