<?php

use AppBuilder\Entity\EntityAdmin;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;

require_once __DIR__ . "/inc.app/app.php";
require_once __DIR__ . "/inc.app/database-builder.php";

$inputPost = new InputPost();

if($inputPost->getResetPassword())
{
    $path = __DIR__ . "/inc.cfg/reset-password.yml";

    if(file_exists($path))
    {
        $resetPassword = new MagicObject();
        $resetPassword->loadYamlFile($path, true, true, true);
        if($resetPassword->issetPasswordToReset() && is_array($resetPassword->getPasswordToReset()))
        {
            foreach($resetPassword->getPasswordToReset() as $passwordToReset)
            {
                try
                {
                    $admin = new EntityAdmin(null, $databaseBuilder);
                    $admin->where(PicoSpecification::getInstance()->addAnd(PicoPredicate::getInstance()->equals(Field::of()->username, $passwordToReset->getUsername())))
                    ->setPassword(sha1(sha1($passwordToReset->getPassword())))
                    ->update();
                }
                catch(Exception $e)
                {
                    // Do nothing
                }
            }
            $resetPassword->setPasswordToReset(null);
            file_put_contents($path, $resetPassword->dumpYaml());
        }
    }
}

require_once __DIR__ . "/inc.app/reset-password.php";
exit();
