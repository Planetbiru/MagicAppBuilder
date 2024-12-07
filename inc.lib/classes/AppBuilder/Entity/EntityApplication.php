<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * EntityApplication is entity of table application. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="application")
 */
class EntityApplication extends MagicObject
{
	/**
	 * Application ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=false, extra="auto_increment")
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;
	
	/**
	 * Architecture
	 * 
	 * @Column(name="architecture", type="varchar(40)", length=40, nullable=false, extra="auto_increment")
	 * @Label(content="Architecture")
	 * @var string
	 */
	protected $architecture;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="text", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;
    
    /**
	 * Directory
	 * 
	 * @Column(name="directory", type="text", nullable=true)
	 * @Label(content="Directory")
	 * @var string
	 */
	protected $directory;
    
    /**
	 * Author
	 * 
	 * @Column(name="author", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Author")
	 * @var string
	 */
	protected $author;
    
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