jQuery(function($) {
    // Handle both Application & Workspace star toggle
    $(document).on('click', '.application-item .card-body .mark-stared, .application-item .card-body .mark-unstared, .workspace-item .card-body .mark-stared, .workspace-item .card-body .mark-unstared',
        function(event) {
            event.preventDefault();

            const clickedElement = $(this);
            const card = clickedElement.closest('.application-item, .workspace-item');

            let type, id, url, reloadCallback;

            if (card.hasClass('application-item')) {
                type = 'application';
                id = card.attr('data-application-id');
                url = 'lib.ajax/set-star-application.php';
                reloadCallback = loadApplicationList;
            } else if (card.hasClass('workspace-item')) {
                type = 'workspace';
                id = card.attr('data-workspace-id');
                url = 'lib.ajax/set-star-workspace.php';
                reloadCallback = loadWorkspaceList;
            }

            const isStared = clickedElement.find('i').hasClass('fa-solid');
            const newStarStatus = !isStared;

            increaseAjaxPending();
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    [`${type}Id`]: id,
                    star: newStarStatus ? 1 : 0
                },
                success: function(response) {
                    decreaseAjaxPending();
                    reloadCallback();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    decreaseAjaxPending();
                    console.error(`Failed to change ${type} star status:`, textStatus, errorThrown);
                }
            });
        }
    );



    $(document).on('click', '.application-item .card-body .mark-hidden, .application-item .card-body .mark-unhidden', function(event) {
        event.preventDefault();

        const clickedElement = $(this);
        const card = clickedElement.closest('.application-item');
        const applicationId = card.attr('data-application-id');

        const isHidden = clickedElement.hasClass('mark-unhidden'); 
        // mark-unhidden = currently visible (eye), next action = hide
        // mark-hidden   = currently hidden (eye-slash), next action = unhide
        const newHiddenStatus = !isHidden;
        if(newHiddenStatus)
        {
            $('.application-item[data-application-id="' + applicationId + '"]').parent().addClass('d-none');
        }

        increaseAjaxPending();
        $.ajax({
            url: 'lib.ajax/set-hidden-application.php',
            type: 'POST',
            data: {
                applicationId: applicationId,
                hidden: newHiddenStatus ? 1 : 0
            },
            success: function(response) {
                decreaseAjaxPending();
                if(!newHiddenStatus)
                {
                    loadApplicationList(true);
                }
                else if(applicationId == $('meta[name="application-id"]').attr('content'))
                {
                    loadAllResource();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                decreaseAjaxPending();
                console.error("Failed to change hidden status:", textStatus, errorThrown);
            }
        });
    });
});