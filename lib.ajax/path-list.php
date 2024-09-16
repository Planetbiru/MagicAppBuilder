
<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constSelected = ' selected';

$moduleLocation = $appConfig->getApplication() != null ? $appConfig->getApplication()->getBaseModuleDirectory() : array();
if (!empty($moduleLocation)) {
foreach ($moduleLocation as $key => $value) {
?>
    <option value="<?php echo $value->getPath(); ?>"<?php echo $value->getActive() ? " selected":"";?>><?php echo $value->getName(); ?> - <?php echo $value->getPath(); ?></option>
<?php
}
}
