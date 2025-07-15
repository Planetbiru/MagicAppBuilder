<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppAdminMinImpl class represents an entity in the "admin" table.
 *
 * This entity maps to the "admin" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAppTemplate\Entity\App
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin")
 */
class AppAdminMinImpl extends MagicObject
{
	/**
	 * Admin ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $name;

	/**
	 * Username
	 * 
	 * @Column(name="username", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Username")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $username;

	/**
	 * Password
	 * 
	 * @Column(name="password", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Password")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $password;

	/**
	 * Admin Level ID
	 * 
	 * @Column(name="admin_level_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Level ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminLevelId;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @MaxLength(value=2)
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $email;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Phone")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $phone;
	
	/**
	 * Language ID
	 * 
	 * @Column(name="language_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Language ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $languageId;

	/**
	 * Validation Code
	 * 
	 * @Column(name="validation_code", type="text", nullable=true)
	 * @Label(content="Validation Code")
	 * @var string
	 */
	protected $validationCode;

	/**
	 * Last Reset Password
	 * 
	 * @Column(name="last_reset_password", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Reset Password")
	 * @var string
	 */
	protected $lastResetPassword;
	
	/**
	 * Blocked
	 * 
	 * @Column(name="blocked", type="tinyint(1)", length=1, default_value=false, nullable=true)
	 * @DefaultColumn(value="false")
	 * @Label(content="Blocked")
	 * @var bool
	 */
	protected $blocked;

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
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminEdit;

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
	 * @DefaultColumn(value="true")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}