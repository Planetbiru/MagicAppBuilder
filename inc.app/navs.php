<?php

use AppBuilder\AppNav;
use AppBuilder\AppNavs;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$appNavs = (new AppNavs())
    ->add(new AppNav('builder', 'Builder'))
    ->add(new AppNav('define-application', 'Application'))
    ->add(new AppNav('define-module', 'Define Module', true))
    ->add(new AppNav('define-column', 'Define Column'))
    ->add(new AppNav('module-file', 'Generated Module'))
    ->add(new AppNav('entity-file', 'Generated Entity'))
    ->add(new AppNav('entity-relationship', 'Entity Relationship'))
    ->add(new AppNav('entity-query', 'Genenerated Query'))
    ->add(new AppNav('translate-entity', 'Translate Entity'))
    ->add(new AppNav('translate-application', 'Translate App'))
    ->add(new AppNav('docs', 'Docs'))
;