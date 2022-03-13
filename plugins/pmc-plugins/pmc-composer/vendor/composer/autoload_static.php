<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit29c263a14ad334faa82a64fad2a45c3b
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Rollbar\\' => 8,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Rollbar\\' => 
        array (
            0 => __DIR__ . '/..' . '/rollbar/rollbar/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit29c263a14ad334faa82a64fad2a45c3b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit29c263a14ad334faa82a64fad2a45c3b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit29c263a14ad334faa82a64fad2a45c3b::$classMap;

        }, null, ClassLoader::class);
    }
}
