<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit98d566506d9bd29ba23d2ee9efda62ae
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Yaml\\' => 23,
        ),
        'M' => 
        array (
            'MagicObject\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
        'MagicObject\\' => 
        array (
            0 => __DIR__ . '/..' . '/planetbiru/magic-object/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'MagicApp\\' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit98d566506d9bd29ba23d2ee9efda62ae::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit98d566506d9bd29ba23d2ee9efda62ae::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit98d566506d9bd29ba23d2ee9efda62ae::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit98d566506d9bd29ba23d2ee9efda62ae::$classMap;

        }, null, ClassLoader::class);
    }
}