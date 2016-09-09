<?php
/**
 * Helper class to autoload ChipVN package.
 */
class ChipVN_ClassLoader_Loader
{
    /**
     * Class prefixes.
     *
     * @var array
     */
    protected static $classPrefixes = array();

    /**
     * Point a class prefix to a path for autoloading.
     *
     * @param  string|array $classPrefix Use array for prefix-path pairs.
     * @param  string       $path
     * @return void
     */
    public static function addPrefixes($classPrefix, $path = null)
    {
        if (is_array($classPrefix)) {
            foreach ($classPrefix as $prefix => $path) {
                self::addPrefixes($prefix, $path);
            }
        } elseif ($path = realpath($path)) {
            self::$classPrefixes[$classPrefix] = $path . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Register autoload.
     *
     * @return void
     */
    public static function registerAutoload(array $prefixes = array())
    {
        self::addPrefixes($prefixes + array(
            'ChipVN' => dirname(dirname(__FILE__))
        ));

        spl_autoload_register(array(__CLASS__ , 'autoLoad'));
    }

    /**
     * Automatic load class.
     *
     * @param  string $class
     * @return void
     */
    public static function autoLoad($class)
    {
        foreach (self::$classPrefixes as $prefix => $path) {
            if (stripos($class, $prefix) === 0) {
                $file = strtr($class, array(
                    $prefix => '',
                    '_'     => '/')
                );
                $file = $path . trim($file, '/') . '.php';

                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }
    }
}
