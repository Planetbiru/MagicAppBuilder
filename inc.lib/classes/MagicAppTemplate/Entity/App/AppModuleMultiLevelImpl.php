<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppModuleMultiLevelImpl class represents an entity in the "module" table.
 *
 * This entity maps to the "module" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="module")
 * @package MagicAppTemplate\Entity\App
 */
class AppModuleMultiLevelImpl extends MagicObject
{
	/**
	 * Module ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="module_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Module ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $moduleId;
	
	/**
	 * Name
	 * 
	 * @NotNull
	 * @Column(name="name", type="varchar(255)", length=255, default_value=NULL, nullable=true)
	 * @Label(content="Name")
	 * @MaxLength(value=255)
	 * @var string
	 */
	protected $name;
	
	/**
	 * Module Code
	 * 
	 * @NotNull
	 * @Column(name="module_code", type="varchar(255)", length=255, default_value=NULL, nullable=true)
	 * @Label(content="Module Code")
	 * @MaxLength(value=255)
	 * @var string
	 */
	protected $moduleCode;

	/**
	 * Module Group ID
	 * 
	 * @NotNull
	 * @Column(name="module_group_id", type="varchar(40)", length=40, default_value=NULL, nullable=true)
	 * @Label(content="Module Group ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $moduleGroupId;
	
	/**
	 * Parent Module ID
	 * 
	 * @NotNull
	 * @Column(name="parent_module_id", type="varchar(40)", length=40, default_value=NULL, nullable=true)
	 * @Label(content="Parent Module")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $parentModuleId;
	
	/**
	 * Parent Module
	 * 
	 * @NotNull
	 * @JoinColumn(name="parent_module_id", referenceColumnName="module_id", referenceTableName="module")
	 * @Label(content="Parent Module")
	 * @var AppModuleMinImpl
	 */
	protected $parentModule;

	/**
	 * URL
	 * 
	 * @NotNull
	 * @Column(name="url", type="longtext", nullable=true)
	 * @Label(content="URL")
	 * @var string
	 */
	protected $url;
	
	/**
	 * Target
	 * 
	 * @NotNull
	 * @Column(name="target", type="varchar(20)", length=20, default_value=NULL, nullable=true)
	 * @Label(content="Target")
	 * @MaxLength(value=20)
	 * @var string
	 */
	protected $target;
	
	/**
	 * Icon
	 * 
	 * @NotNull
	 * @Column(name="icon", type="varchar(40)", length=40, default_value=NULL, nullable=true)
	 * @Label(content="Icon")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $icon;
	
	/**
	 * Menu
	 * 
	 * @NotNull
	 * @Column(name="menu", type="tinyint(1)", length=1, default_value=FALSE, nullable=true)
	 * @Label(content="Menu")
	 * @var bool
	 */
	protected $menu;

	/**
	 * Special Access
	 * 
	 * @NotNull
	 * @Column(name="special_access", type="tinyint(1)", length=1, default_value=FALSE, nullable=true)
	 * @Label(content="Special Access")
	 * @var bool
	 */
	protected $specialAccess;

	/**
	 * Sort Order
	 * 
	 * @NotNull
	 * @Column(name="sort_order", type="int(11)", length=1, default_value="0", nullable=true)
	 * @Label(content="Sort Order")
	 * @var int
	 */
	protected $sortOrder;

	/**
	 * Default Data
	 * 
	 * @NotNull
	 * @Column(name="default_data", type="tinyint(1)", length=1, default_value=FALSE, nullable=true)
	 * @Label(content="Default Data")
	 * @var bool
	 */
	protected $defaultData;
	
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
	 * Creator
	 * 
	 * @JoinColumn(name="admin_create", referenceColumnName="admin_id", referenceTableName="admin")
	 * @Label(content="Creator")
	 * @var AppAdminMinImpl
	 */
	protected $creator;

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
	 * Editor
	 * 
	 * @JoinColumn(name="admin_edit", referenceColumnName="admin_id", referenceTableName="admin")
	 * @Label(content="Editor")
	 * @var AppAdminMinImpl
	 */
	protected $editor;

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
	 * @NotNull
	 * @Column(name="active", type="tinyint(1)", length=1, default_value=TRUE, nullable=true)
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;
	

}
