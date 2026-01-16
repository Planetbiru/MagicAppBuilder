<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppAdminProfileImpl class represents an entity in the "admin_profile" table.
 *
 * This entity maps to the "admin_profile" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAppTemplate\Entity\App
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin_profile")
 */
class AppAdminProfileImpl extends MagicObject
{
	/**
	 * Admin Profile ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.TIMEBASED)
	 * @NotNull
	 * @Column(name="admin_profile_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin Profile ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminProfileId;

	/**
	 * Admin ID
	 * 
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminId;

	/**
	 * Admin
	 * 
	 * @JoinColumn(name="admin_id", referenceColumnName="admin_id", referenceTableName="admin")
	 * @Label(content="Admin")
	 * @var AppAdminMinImpl
	 */
	protected $admin;

	/**
	 * Profile Name
	 * 
	 * @Column(name="profile_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Profile Name")
	 * @var string
	 */
	protected $profileName;

	/**
	 * Profile Value
	 * 
	 * @Column(name="profile_value", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Profile Value")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $profileValue;

	/**
	 * User Agent
	 * 
	 * @Column(name="user_agent", type="text", nullable=true)
	 * @Label(content="User Agent")
	 * @var string
	 */
	protected $userAgent;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value=true, nullable=true)
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}