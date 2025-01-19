<?php

namespace AppBuilder;

use MagicApp\AppDto\MocroServices\PicoObjectToString;

/**
 * Represents a data item for a message or notification feed displayed at the top of the page.
 *
 * This class stores the details of a message or notification feed item, including:
 * - A unique identifier for the feed item.
 * - A link associated with the feed item (optional).
 * - The title of the message or notification.
 * - The time when the feed item was created or posted.
 * - A timestamp for sorting, tracking, or internal purposes.
 */
class AppFeedData extends PicoObjectToString
{
    /**
     * The unique identifier for the feed item.
     *
     * @var string
     */
    protected $id;

    /**
     * The URL link associated with the feed item, if any.
     *
     * @var string
     */
    protected $link;

    /**
     * The title of the message or notification.
     *
     * @var string
     */
    protected $title;

    /**
     * The time when the feed item was posted or created.
     *
     * @var string
     */
    protected $time;

    /**
     * The timestamp for the feed item, typically used for sorting or tracking.
     *
     * @var int
     */
    protected $timestamp;

    /**
     * Constructor to initialize feed data properties.
     *
     * @param string $id The unique identifier for the feed item.
     * @param string $link The URL link associated with the feed item (optional).
     * @param string $title The title of the message or notification.
     * @param string $time The time when the feed item was posted or created.
     * @param int $timestamp The timestamp for the feed item.
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
