<?php

namespace AppBuilder\Entity;

use MagicObject\MagicObject;

/**
 * The EntityMessage class represents an entity in the "message" table.
 *
 * This entity maps to the "message" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="message")
 */
class EntityMessage extends MagicObject
{
	/**
	 * Message ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="message_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Message ID")
	 * @var string
	 */
	protected $messageId;

	/**
	 * Subject
	 * 
	 * @NotNull
	 * @Column(name="subject", type="text", nullable=true)
	 * @Label(content="Subject")
	 * @var string
	 */
	protected $subject;

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
	 * Sender ID
	 * 
	 * @NotNull
	 * @Column(name="sender_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Sender")
	 * @var string
	 */
	protected $senderId;

	/**
	 * Sender
	 * 
	 * @JoinColumn(name="sender", referenceColumnName="admin_id")
	 * @Label(content="Sender")
	 * @var AdminMin
	 */
	protected $sender;

	/**
	 * Receiver ID
	 * 
	 * @NotNull
	 * @Column(name="receiver_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Receiver")
	 * @var string
	 */
	protected $receiverId;

	/**
	 * Receiver
	 * 
	 * @JoinColumn(name="receiver", referenceColumnName="admin_min")
	 * @Label(content="Receiver")
	 * @var AdminMin
	 */
	protected $receiver;

	/**
	 * Message Folder ID
	 * 
	 * @NotNull
	 * @Column(name="message_folder_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Message Folder ID")
	 * @var string
	 */
	protected $messageFolderId;

	/**
	 * Message Folder
	 * 
	 * @JoinColumn(name="message_folder_id", referenceColumnName="message_folder_id")
	 * @Label(content="Message Folder")
	 * @var MessageFolderMin
	 */
	protected $messageFolder;

	/**
	 * Is Copy
	 * 
	 * @NotNull
	 * @Column(name="is_copy", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Is Copy")
	 * @var bool
	 */
	protected $isCopy;

	/**
	 * Time Create
	 * 
	 * @NotNull
	 * @Column(name="time_create", type="timestamp", length=19, nullable=false, updatable=false)
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
	 * @Column(name="time_open", type="timestamp", length=19, nullable=true)
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