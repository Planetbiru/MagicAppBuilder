jQuery(function($) {
    // Use a more concise selector for both links
    $(document).on('click', '.application-item .card-body .mark-stared, .application-item .card-body .mark-unstared', function(event) {
        // Prevent the default link behavior
        event.preventDefault();

        const clickedElement = $(this);
        const card = clickedElement.closest('.application-item');
        const applicationId = card.attr('data-application-id');

        // Determine the star status based on the current icon class.
        // If the icon is "solid" (fa-solid fa-star), it's currently stared and will be changed to unstared (false).
        const isStared = clickedElement.find('i').hasClass('fa-solid');
        const newStarStatus = !isStared;

        $.ajax({
            url: 'lib.ajax/set-star-application.php',
            type: 'POST',
            data: {
                applicationId: applicationId,
                star: newStarStatus ? 1 : 0 // Sending 1 or 0 is more common and consistent
            },
            success: function(response) {
                loadApplicationList();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Show an error message if the request fails
                console.error("Failed to change star status:", textStatus, errorThrown);
            }
        });
    });

    
    // Use a more concise selector for both links
    $(document).on('click', '.workspace-item .card-body .mark-stared, .workspace-item .card-body .mark-unstared', function(event) {
        // Prevent the default link behavior
        event.preventDefault();

        const clickedElement = $(this);
        const card = clickedElement.closest('.workspace-item');
        const workspaceId = card.attr('data-workspace-id');

        // Determine the star status based on the current icon class.
        // If the icon is "solid" (fa-solid fa-star), it's currently stared and will be changed to unstared (false).
        const isStared = clickedElement.find('i').hasClass('fa-solid');
        const newStarStatus = !isStared;

        $.ajax({
            url: 'lib.ajax/set-star-workspace.php',
            type: 'POST',
            data: {
                workspaceId: workspaceId,
                star: newStarStatus ? 1 : 0 // Sending 1 or 0 is more common and consistent
            },
            success: function(response) {
                // Panggil fungsi untuk memuat ulang daftar workspace
                loadWorkspaceList();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Show an error message if the request fails
                console.error("Failed to change star status:", textStatus, errorThrown);
            }
        });
    });
});