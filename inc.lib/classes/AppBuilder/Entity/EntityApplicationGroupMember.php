<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * The EntityApplicationGroupMember class represents an entity in the "application_group_member" table.
 *
 * This entity maps to the "application_group_member" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\Entity
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
	 * Application Group
	 * 
	 * @JoinColumn(name="application_group_id", referenceColumnName="application_group_id")
	 * @Label(content="Application Group")
	 * @var EntityApplicationGroup
	 */
	protected $applicationGroup;

	/**
	 * Admin ID
	 * 
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminId;

	/**
	 * Admin
	 * 
	 * @JoinColumn(name="admin_id", referenceColumnName="admin_id")
	 * @Label(content="Admin")
	 * @var EntityAdmin
	 */
	protected $admin;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
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
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
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
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="true", nullable=true)
	 * @DefaultColumn(value="true")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}