<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppNotificationImpl class represents an entity in the "notification" table.
 *
 * This entity maps to the "notification" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="notification")
 * @package MagicAppTemplate\Entity\App
 */
class AppNotificationImpl extends MagicObject
{
	/**
	 * Notification ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="notification_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Notification ID")
	 * @MaxLength(value=40)
	 * @var int
	 */
	protected $notificationId;

	/**
	 * Notification Type
	 * 
	 * @Column(name="notification_type", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Notification Type")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $notificationType;

	/**
	 * Admin Group
	 * 
	 * @Column(name="admin_group", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Group")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminGroup;

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
	 * Icon
	 * 
	 * @Column(name="icon", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Icon")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $icon;

	/**
	 * Subject
	 * 
	 * @Column(name="subject", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Subject")
	 * @MaxLength(value=255)
	 * @var string
	 */
	protected $subject;

	/**
	 * Content
	 * 
	 * @Column(name="content", type="text", nullable=true)
	 * @Label(content="Content")
	 * @var string
	 */
	protected $content;

	/**
	 * Link
	 * 
	 * @Column(name="link", type="text", nullable=true)
	 * @Label(content="Link")
	 * @var string
	 */
	protected $link;

	/**
	 * Is Read
	 * 
	 * @Column(name="is_read", type="int(11)", length=11, nullable=true)
	 * @Label(content="Is Read")
	 * @var int
	 */
	protected $isRead;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

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
	 * Time Read
	 * 
	 * @Column(name="time_read", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Read")
	 * @var string
	 */
	protected $timeRead;

	/**
	 * IP Read
	 * 
	 * @Column(name="ip_read", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Read")
	 * @var string
	 */
	protected $ipRead;

}