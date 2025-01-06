<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * EntityApplicationGroupMember is entity of table user. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="application_group_member")
 */
class EntityApplicationGroupMember extends MagicObject
{
    /**
	 * Application Group Member ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="application_group_member_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Application Group Member ID")
	 * @var string
	 */
	protected $applicationGroupMemberId;

	/**
	 * Application Group ID
	 * 
	 * @Column(name="application_group_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application Group ID")
	 * @var string
	 */
	protected $applicationGroupId;

	/**
	 * Application User ID
	 * 
	 * @Column(name="application_user_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application User ID")
	 * @var string
	 */
	protected $applicationUSserId;

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

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;
}