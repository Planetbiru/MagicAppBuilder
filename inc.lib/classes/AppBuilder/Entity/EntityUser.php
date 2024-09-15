<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * EntityUser is entity of table user. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * Visit https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="user")
 */
class EntityUser extends MagicObject
{
	/**
	 * User ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="User ID")
	 * @var string
	 */
	protected $userId;

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
	 * Validation Code
	 * 
	 * @Column(name="vatidation_code", type="text", nullable=true)
	 * @Label(content="Validation Code")
	 * @var string
	 */
	protected $vatidationCode;
    
    /**
	 * Last Reset Password
	 * 
	 * @Column(name="last_reset_password", type="timestamp", nullable=true)
	 * @Label(content="Last Reset Password")
	 * @var string
	 */
	protected $lastResetPassword;
    
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