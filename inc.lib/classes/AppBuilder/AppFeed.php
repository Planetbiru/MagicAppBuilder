<?php

namespace AppBuilder;

use AppBuilder\Entity\EntityAdmin;
use Exception;
use MagicAdmin\Entity\Data\Message;
use MagicAdmin\Entity\Data\Notification;
use MagicApp\AppDto\MocroServices\PicoObjectToString;
use MagicApp\Field;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;

/**
 * Represents a feed containing multiple message or notification items.
 *
 * This class stores a collection of feed data items (AppFeedData objects) along with the total count of items.
 */
class AppFeed extends PicoObjectToString
{
    /**
     * The total number of data items in the feed.
     *
     * @var int
     */
    protected $totalData;

    /**
     * The array of feed data items (AppFeedData objects).
     *
     * @var AppFeedData[]
     */
    protected $data;

    /**
     * Get a list of notifications for a specific admin.
     *
     * This method fetches notifications from the database based on the provided criteria, including the admin's 
     * ID, the status of the notifications (whether they are open or closed), and the specified limit on the 
     * number of notifications to retrieve. It then creates an instance of the class and populates it with the 
     * retrieved notifications in the form of `AppFeedData` objects.
     *
     * @param PicoDatabase $databaseBuilder The database connection object used to query the notifications.
     * @param EntityAdmin $entityAdmin The admin entity whose notifications are being fetched.
     * @param int $limit The maximum number of notifications to retrieve.
     * @return self An instance of the class containing the fetched notifications.
     */
    public static function getNotifications($databaseBuilder, $entityAdmin, $limit)
    {
        $instance = new self();
        $finder = new Notification(null, $databaseBuilder);
        try
        {
            $specification = PicoSpecification::getInstance()
                ->add([Field::of()->receiverId, $entityAdmin->getAdminId()])
                ->add([Field::of()->isOpen, false])
                ;
            $sortable = PicoSortable::getInstance()
                ->add([Field::of()->timeCreate, PicoSort::ORDER_TYPE_DESC])
                ;
            $page = new PicoPage(1, $limit);
            $pagable = new PicoPageable($page, $sortable);
            $pageData = $finder->findAll($specification, $pagable, $sortable);

            $instance->setTotalData($pageData->getTotalResult());
            foreach ($pageData->getResult() as $record)
            {
                $instance->appendData(
                    new AppFeedData(
                        $record->getNotificationId(),
                        "notification.php?user_action=detail&notification_id=" . $record->getNotificationId(),
                        $record->getTitle(),
                        $record->getTimeCreate(),
                        strtotime($record->getTimeCreate())
                    )
                );
            }
        }
        catch (Exception $e)
        {
            // Do nothing
        }
        return $instance;
    }

    /**
     * Get a list of messages for a specific admin.
     *
     * This method fetches messages from the database based on the provided criteria, including the admin's 
     * ID, the status of the messages (whether they are open or closed), and the specified limit on the 
     * number of messages to retrieve. It then creates an instance of the class and populates it with the 
     * retrieved messages in the form of `AppFeedData` objects.
     *
     * @param PicoDatabase $databaseBuilder The database connection object used to query the messages.
     * @param EntityAdmin $entityAdmin The admin entity whose messages are being fetched.
     * @param int $limit The maximum number of messages to retrieve.
     * @return self An instance of the class containing the fetched messages.
     */
    public static function getMessages($databaseBuilder, $entityAdmin, $limit)
    {
        $instance = new self();
        $finder = new Message(null, $databaseBuilder);
        try
        {
            $specification = PicoSpecification::getInstance()
                ->add([Field::of()->receiverId, $entityAdmin->getAdminId()])
                ->add([Field::of()->isOpen, false])
                ;
            $sortable = PicoSortable::getInstance()
                ->add([Field::of()->timeCreate, PicoSort::ORDER_TYPE_DESC])
                ;
            $page = new PicoPage(1, $limit);
            $pagable = new PicoPageable($page, $sortable);
            $pageData = $finder->findAll($specification, $pagable, $sortable);

            $instance->setTotalData($pageData->getTotalResult());
            foreach ($pageData->getResult() as $record)
            {
                $data = new AppFeedData(
                    $record->getMessageId(),
                    "message.php?user_action=detail&message_id=" . $record->getMessageId(),
                    $record->getSubject(),
                    $record->getTimeCreate(),
                    strtotime($record->getTimeCreate())
                );
                $instance->appendData(
                    $data
                );
            }
        }
        catch (Exception $e)
        {
            // Do nothing
        }
        return $instance;
    }

    /**
     * Appends a new feed data item to the feed.
     *
     * This method adds a new `AppFeedData` object to the feed's data array.
     *
     * @param AppFeedData $appFeedData The feed data item to be added to the feed.
     * @return self The current instance, allowing for method chaining.
     */
    public function appendData($appFeedData)
    {
        if (!isset($this->data)) {
            $this->data = [];
        }
        $this->data[] = $appFeedData;

        return $this;
    }

    /**
     * Get the total number of data items in the feed.
     *
     * This method returns the total count of feed data items stored in the feed.
     *
     * @return int The total number of data items in the feed.
     */
    public function getTotalData()
    {
        return $this->totalData;
    }

    /**
     * Set the total number of data items in the feed.
     *
     * This method sets the total count of feed data items in the feed.
     *
     * @param int $totalData The total number of data items to be set for the feed.
     * @return self The current instance, allowing for method chaining.
     */
    public function setTotalData($totalData)
    {
        $this->totalData = $totalData;

        return $this;
    }
}
