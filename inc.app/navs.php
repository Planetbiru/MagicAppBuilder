<?php

use AppBuilder\AppNav;
use AppBuilder\AppNavs;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$appNavs = (new AppNavs())
    ->add(new AppNav('builder', 'Builder'))
    ->add(new AppNav('define-application', 'Apps'))
    ->add(new AppNav('define-module', 'Select Table', true))
    ->add(new AppNav('define-column', 'Generate Module'))
    ->add(new AppNav('module-file', 'Edit Module'))
    ->add(new AppNav('entity-file', 'Edit Entity'))
    ->add(new AppNav('entity-relationship', 'ERD'))
    ->add(new AppNav('entity-query', 'Query'))
    ->add(new AppNav('translate-entity', 'Translate Entity'))
    ->add(new AppNav('translate-application', 'Translate Module'))
    ->add(new AppNav('docs', 'Docs'))
;