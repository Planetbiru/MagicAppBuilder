<?php

namespace AppBuilder;

use MagicApp\AppDto\MocroServices\PicoObjectToString;

/**
 * Represents a data item for a message or notification feed displayed at the top of the page.
 *
 * This class holds the details of a feed item, which could represent either a message or a notification.
 * It includes the following properties:
 * - A unique identifier for the feed item.
 * - An optional link associated with the feed item.
 * - The title of the message or notification.
 * - The time when the feed item was created or posted.
 * - A timestamp for sorting, tracking, or internal purposes.
 */
class AppFeedData extends PicoObjectToString
{
    /**
     * The unique identifier for the feed item.
     *
     * This ID is used to uniquely identify each feed item.
     *
     * @var string
     */
    protected $id;

    /**
     * The URL link associated with the feed item, if any.
     *
     * This link could point to more details related to the feed item (e.g., a message or notification detail page).
     *
     * @var string
     */
    protected $link;

    /**
     * The title of the message or notification.
     *
     * This is the main title or subject of the feed item.
     *
     * @var string
     */
    protected $title;

    /**
     * The time when the feed item was posted or created.
     *
     * This time could be the creation or posting time of the message or notification.
     *
     * @var string
     */
    protected $time;

    /**
     * The timestamp for the feed item, typically used for sorting or tracking.
     *
     * The timestamp is often used to order feed items chronologically or to facilitate tracking.
     *
     * @var int
     */
    protected $timestamp;

    /**
     * Constructor to initialize feed data properties.
     *
     * This constructor accepts values for each property of the feed item, allowing for initialization of
     * the `AppFeedData` object with the necessary details such as ID, link, title, time, and timestamp.
     *
     * @param string $id The unique identifier for the feed item.
     * @param string $link The URL link associated with the feed item (optional).
     * @param string $title The title of the message or notification.
     * @param string $time The time when the feed item was posted or created.
     * @param int $timestamp The timestamp for the feed item, typically used for sorting or tracking.
     */
    public function __construct($id, $link, $title, $time, $timestamp)
    {
        $this->id = $id;
        $this->link = $link;
        $this->title = $title;
        $this->time = $time;
        $this->timestamp = $timestamp;
    }
}
