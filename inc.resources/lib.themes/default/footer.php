<?php

use MagicAppTemplate\AppFeedImpl;

?>
</div>

    <!-- Importing JavaScript for Bootstrap and jQuery -->

    <script>
        // Notification data from the server in JSON format
        const notifications = <?php echo AppFeedImpl::getNotifications($database, $currentUser, 5); ?>;

        // Message data from the server in JSON format
        const messages = <?php echo AppFeedImpl::getMessages($database, $currentUser, 5, "message.php"); ?>;

    </script>
</body>

</html>