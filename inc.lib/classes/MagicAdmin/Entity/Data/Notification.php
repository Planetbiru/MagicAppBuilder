<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The Notification class represents an entity in the "notification" table.
 *
 * This entity maps to the "notification" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="notification")
 */
class Notification extends MagicObject
{
	/**
	 * Notification ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="notification_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Notification ID")
	 * @var string
	 */
	protected $notificationId;

	/**
	 * Title
	 * 
	 * @NotNull
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Content
	 * 
	 * @NotNull
	 * @Column(name="content", type="text", nullable=true)
	 * @Label(content="Content")
	 * @var string
	 */
	protected $content;

	/**
	 * Url
	 * 
	 * @NotNull
	 * @Column(name="url", type="text", nullable=true)
	 * @Label(content="Url")
	 * @var string
	 */
	protected $url;

	/**
	 * Receiver ID
	 * 
	 * @NotNull
	 * @Column(name="receiver_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Receiver ID")
	 * @var string
	 */
	protected $receiverId;

	/**
	 * Receiver
	 * 
	 * @JoinColumn(name="receiver_id", referenceColumnName="admin_id")
	 * @Label(content="Receiver")
	 * @var AdminMin
	 */
	protected $receiver;

	/**
	 * Time Create
	 * 
	 * @NotNull
	 * @Column(name="time_create", type="timestamp", length=26, nullable=false, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Is Open
	 * 
	 * @NotNull
	 * @Column(name="is_open", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Is Open")
	 * @var bool
	 */
	protected $isOpen;

	/**
	 * Time Open
	 * 
	 * @NotNull
	 * @Column(name="time_open", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Open")
	 * @var string
	 */
	protected $timeOpen;

	/**
	 * Is Delete
	 * 
	 * @NotNull
	 * @Column(name="is_delete", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Is Delete")
	 * @var bool
	 */
	protected $isDelete;

}