<div class="modal fade loginModal" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?php echo $appLanguage->getSessionExpired(); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $appLanguage->getButtonClose(); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="ajaxLoginForm" method="post" action="login.php">
                <div class="modal-body">
                    <p><?php echo $appLanguage->getSessionExpiredMessage(); ?></p>

                    <div class="form-group">
                        <label for="loginUsername"><?php echo $appLanguage->getLabelUsername(); ?></label>
                        <input type="text" class="form-control" id="loginUsername" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="loginPassword"><?php echo $appLanguage->getLabelPassword(); ?></label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>

                    <div class="alert alert-danger login-error" role="alert" style="display: none;">
                        <?php echo $appLanguage->getInvalidCredentials(); ?>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="loginAjax()"><?php echo $appLanguage->getButtonLogin(); ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $appLanguage->getButtonCancel(); ?></button>
                </div>
            </form>

        </div>
    </div>
</div>