<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * EntityUserSession is entity of table user_session. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="user")
 */
class EntityUserSession extends MagicObject
{
	/**
	 * User ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_session_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="User ID")
	 * @var string
	 */
	protected $userSessionId;

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