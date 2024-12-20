<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * EntityReference is entity of table reference. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="reference")
 */
class EntityReference extends MagicObject
{
	/**
	 * Reference ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="reference_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Reference ID")
	 * @var string
	 */
	protected $referenceId;

    /**
	 * User ID
	 * 
	 * @Column(name="user_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="User ID")
	 * @var string
	 */
	protected $userId;

    /**
     * User
     *
     * @JoinColumn(name="user_id" referenceColumnName="user_id")
     * @var EntityUser
     */
    protected $user;
    
    /**
	 * Application ID
	 * 
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

    /**
     * Application
     *
     * @JoinColumn(name="application_id" referenceColumnName="application_id")
     * @var EntityApplication
     */
    protected $application;

    /**
	 * Column Name
	 * 
	 * @Column(name="column_name", type="varchar(1024)", length=1024, nullable=true)
	 * @Label(content="Column Name")
	 * @var string
	 */
	protected $columnName;

    /**
	 * Reference Key
	 * 
	 * @Column(name="reference_key", type="varchar(1024)", length=1024, nullable=true)
	 * @Label(content="Reference Key")
	 * @var string
	 */
	protected $referenceKey;

    /**
	 * Reference Value
	 * 
	 * @Column(name="reference_value", type="longtext", nullable=true)
	 * @Label(content="Reference Value")
	 * @var string
	 */
	protected $referenceValue;
    
    /**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", nullable=true)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;
    
    /**
	 * Admin Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

    /**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;
    
    /**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;
    
    /**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;
    
    /**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

}