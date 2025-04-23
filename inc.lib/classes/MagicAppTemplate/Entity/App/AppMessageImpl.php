<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="message")
 * @package MagicAppTemplate\Entity\App
 */
class AppMessageImpl extends MagicObject
{
	/**
	 * Message ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="message_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Message ID")
	 * @var int
	 */
	protected $messageId;

	/**
	 * Message Direction
	 * 
	 * @Column(name="message_direction", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Message Direction")
	 * @var string
	 */
	protected $messageDirection;

	/**
	 * Sender ID
	 * 
	 * @Column(name="sender_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Sender ID")
	 * @var string
	 */
	protected $senderId;

	/**
	 * Sender
	 * 
	 * @JoinColumn(name="sender_id", referenceColumnName="admin_id")
	 * @Label(content="Sender")
	 * @var AppAdminMinImpl
	 */
	protected $sender;
	
	/**
	 * Receiver ID
	 * 
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
	 * @var AppAdminMinImpl
	 */
	protected $receiver;
	
	/**
	 * Message Folder ID
	 * 
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
	 * @var AppMessageFolderMinImpl
	 */
	protected $messageFolder;
	
	/**
	 * Icon
	 * 
	 * @Column(name="icon", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="Icon")
	 * @var string
	 */
	protected $icon;

	/**
	 * Subject
	 * 
	 * @Column(name="subject", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Subject")
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
	 * Read
	 * 
	 * @Column(name="read", type="int(11)", length=11, nullable=true)
	 * @Label(content="Read")
	 * @var int
	 */
	protected $read;

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