<?php

use AppBuilder\AppFeed;

?>
</div>

    <!-- Importing JavaScript for Bootstrap and jQuery -->
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Notification data from the server in JSON format
        const notifications = <?php echo AppFeed::getNotifications($databaseBuilder, $entityAdmin, 5); ?>;

        // Message data from the server in JSON format
        const messages = <?php echo AppFeed::getMessages($databaseBuilder, $entityAdmin, 5); ?>;

    </script>
</body>

</html>