<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppMenuGroupTranslationImpl class represents an entity in the "menu_group_translation" table.
 *
 * This entity maps to the "menu_group_translation" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAppTemplate\Entity\App
 * @Entity
 * @JSON(propertyNamingStrategy=SNAKE_CASE, prettify=false)
 * @Table(name="menu_group_translation")
 */
class AppMenuGroupTranslationImpl extends MagicObject
{
	/**
	 * Menu Group Translation ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="menu_group_translation_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Menu Group Translation ID")
	 * @var string
	 */
	protected $menuGroupTranslationId;

	/**
	 * Module Group ID
	 * 
	 * @Column(name="module_group_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Module Group ID")
	 * @var string
	 */
	protected $moduleGroupId;

	/**
	 * Language ID
	 * 
	 * @Column(name="language_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Language ID")
	 * @var string
	 */
	protected $languageId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

}