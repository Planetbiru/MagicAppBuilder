<?php

use AppBuilder\AppFeed;

?>
</div>

    <!-- Importing JavaScript for Bootstrap and jQuery -->
    <script src="<?php echo $themeAssetsPath;?>js/popper.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>js/bootstrap.min.js"></script>
    <script>
        // Notification data from the server in JSON format
        const notifications = <?php echo AppFeed::getNotifications($database, $currentUser, 5); ?>;

        // Message data from the server in JSON format
        const messages = <?php echo AppFeed::getMessages($database, $currentUser, 5); ?>;

    </script>
</body>

</html>