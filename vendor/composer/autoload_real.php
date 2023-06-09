<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit7dd76afc04beae1474e5b4e1d1d21788
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit7dd76afc04beae1474e5b4e1d1d21788', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit7dd76afc04beae1474e5b4e1d1d21788', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit7dd76afc04beae1474e5b4e1d1d21788::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
