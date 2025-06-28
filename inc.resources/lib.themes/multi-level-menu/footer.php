<?php

use MagicAppTemplate\AppFeedImpl;

?>
    </div>
    <script>
        const notifications = <?php echo AppFeedImpl::getNotifications($database, $currentUser, 5);?>;
        const messages = <?php echo AppFeedImpl::getMessages($database, $currentUser, 5, "message.php");?>;
        document.addEventListener('DOMContentLoaded', () => {
            initNotifications('#notificationMenu', notifications, 'notification.php', '<?php echo $appLanguage->getShowAll();?>');
            initMessages('#messageMenu', messages, 'message.php', '<?php echo $appLanguage->getShowAll();?>');
        });
    </script>
</body>
</html>