</div>

    <!-- Importing JavaScript for Bootstrap and jQuery -->
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>

        // Notification data from the server in JSON format
        const notifications = {
            "totalData": 129,
            "data": [
            { "id": 1, "link": "notifications.html?any=1", "message": "Notification 1", "time": "5 min" },
            { "id": 2, "link": "notifications.html?any=2", "message": "Notification 2", "time": "100 day" }
        ]}
        ;

        // Message data from the server in JSON format
        const messages = {
            "totalData": 88,
            "data": [
            { "id": 1, "link": "messages.html?any=1", "message": "Message 1", "time": "1 min" },
            { "id": 2, "link": "messages.html?any=2", "message": "Message 2", "time": "3 min" }
        ]};

    </script>
    <script src="js/js.js"></script>
</body>

</html>