<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * EntityUserProfile is entity of table user_profile. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="user_profile")
 */
class EntityUserProfile extends MagicObject
{
	/**
	 * User Profile ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_profile_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="User ID")
	 * @var string
	 */
	protected $userProfileId;

    /**
	 * Application User ID
	 * 
	 * @Column(name="application_user_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application User ID")
	 * @var string
	 */
	protected $applicationUserId;
	
	/**
	 * Application User
	 * 
	 * @JoinColumn(name="application_user_id", referenceColumnName="application_user_id")
	 * @Label(content="Application User")
	 * @var string
	 */
	protected $applicationUser;
    
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
	 * @var string
	 */
	protected $profileValue;

    /**
     * User
     *
     * @JoinColumn(name="user_id" referenceColumnName="user_id")
     * @var EntityUser
     */
    protected $user;

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