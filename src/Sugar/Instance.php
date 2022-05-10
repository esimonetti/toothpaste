<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar;

class Instance
{
    protected static $filesToExist = [
        'index.php',
        'config.php',
        'sugar_version.php',
        'include/entryPoint.php',
        'data/SugarBean.php',
    ];

    protected static $dirToExist = [
        'cache',
        'include',
        'modules',
        'vendor',
        'custom',
        'data',
        'api',
        'ModuleInstall',
        'metadata',
        'jssource',
        'clients',
        'sidecar',
    ];

    protected static $dirMustBeWritable = [
        'cache',
        'custom',
        'modules',
    ];

    public static function validate($path, $output = null)
    {
        if (!empty($path) && is_dir($path)) {
            chdir($path);
            foreach (self::$dirToExist as $dir) {
                if (!is_dir($dir) || !is_readable($dir) || (!is_writable($dir) && in_array($dir, self::$dirMustBeWritable))) {
                    if (is_object($output)) {
                        $output->writeln('Sugar\'s directory ' . $dir . ' either does not exist, it is not readable or it is not writable. ' .
                            'The instance located on ' . $path . ' is invalid');
                    }
                    return false;
                }
            }

            foreach (self::$filesToExist as $file) {
                if (!file_exists($file) || !is_readable($file)) {
                    if (is_object($output)) {
                        $output->writeln('Sugar\'s file ' . $file . ' either does not exist or it is not readable. ' .
                            'The instance located on ' . $path . ' is invalid');
                    }
                    return false;
                }
            }
            return $path;
        }

        return false;
    }

    public static function setup($loadUser = true)
    {
        define('sugarEntry', true);

        require_once('config.php');
        if (file_exists('config_override.php')) {
            require_once('config_override.php');
        }
        $GLOBALS['sugar_config'] = $sugar_config;

        require_once('include/entryPoint.php');

        // set all the vars from entryPoint.php that are not part of globals yet
        foreach (get_defined_vars() as $name => $value) {
            if (empty($GLOBALS[$name])) {
                $GLOBALS[$name] = $value;
            }
        }

        if (empty($GLOBALS['current_language'])) {
            $GLOBALS['current_language'] = $sugar_config['default_language'];
        }
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Administration');

        if ($loadUser) {
            self::loadSystemUser();
        }
    }

    public static function loadSystemUser()
    {
        $u = \BeanFactory::newBean('Users');
        $GLOBALS['current_user'] = $u->getSystemUser();
    }

    public static function clearCache()
    {
        if (\SugarCache::instance()->useBackend()) {
            // clear cache
            \SugarCache::instance()->reset();
            \SugarCache::instance()->resetFull();
        }

        \SugarCache::cleanOpcodes();

        // clear opcache before #79804
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }        
    }

    public static function buildAutoloaderCache()
    {
        \SugarAutoLoader::buildCache();
    }

    public static function basicWarmUp()
    {
        // rebuild some stuff
        self::buildAutoloaderCache();

        // quick load of all beans
        global $beanList, $current_language;
        // load app strings
        $app_list_strings = return_app_list_strings_language($current_language);
        $app_strings = return_application_language($current_language);
        $full_module_list = array_merge($beanList, $app_list_strings['moduleList']);
        foreach ($full_module_list as $module => $label) {
            $bean = \BeanFactory::newBean($module);
            // load language too
            \LanguageManager::createLanguageFile($module, ['default'], true);
            $mod_strings = return_module_language($current_language, $module);
        }

        // load api
        $sd = new \ServiceDictionary();
        $sd->buildAllDictionaries();
    }
}
