<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * The EntityAdmin class represents an entity in the "admin" table.
 *
 * This entity maps to the "admin" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\Entity
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin")
 */
class EntityAdmin extends MagicObject
{
	/**
	 * Admin ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Username
	 * 
	 * @Column(name="username", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Username")
	 * @var string
	 */
	protected $username;

	/**
	 * Password
	 * 
	 * @Column(name="password", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Password")
	 * @var string
	 */
	protected $password;

	/**
	 * Admin Level ID
	 * 
	 * @Column(name="admin_level_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Level ID")
	 * @var string
	 */
	protected $adminLevelId;

	/**
	 * Admin Level
	 * 
	 * @JoinColumn(name="admin_level_id", referenceColumnName="admin_level_id")
	 * @Label(content="Admin Level")
	 * @var EntityAdminLevel
	 */
	protected $adminLevel;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(1)", length=1, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="text", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

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
	 * @JoinColumn(name="application_id", referenceColumnName="application_id")
	 * @Label(content="Application")
	 * @var EntityApplication
	 */
	protected $application;

	/**
	 * Workspace ID
	 * 
	 * @Column(name="workspace_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Workspace ID")
	 * @var string
	 */
	protected $workspaceId;

	/**
	 * Workspace
	 * 
	 * @JoinColumn(name="workspace_id", referenceColumnName="workspace_id")
	 * @Label(content="Workspace")
	 * @var EntityWorkspace
	 */
	protected $workspace;

	/**
	 * Vatidation Code
	 * 
	 * @Column(name="vatidation_code", type="text", nullable=true)
	 * @Label(content="Vatidation Code")
	 * @var string
	 */
	protected $vatidationCode;

	/**
	 * Last Reset Password
	 * 
	 * @Column(name="last_reset_password", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Reset Password")
	 * @var string
	 */
	protected $lastResetPassword;

	/**
	 * Bloked
	 * 
	 * @Column(name="bloked", type="tinyint(1)", length=1, defaultValue="true", nullable=true)
	 * @DefaultColumn(value="false")
	 * @Label(content="Bloked")
	 * @var bool
	 */
	protected $bloked;

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