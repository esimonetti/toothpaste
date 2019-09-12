<?php

namespace Toothpaste\Sugar;

class Instance
{
    public static function validate($path)
    {
        if (!empty($path) && is_dir($path)) {
            chdir($path);
            if (file_exists('config.php') && file_exists('sugar_version.php') && file_exists('include/entryPoint.php')) {
                return $path;
            }
        }

        return false;
    }

    public static function setup()
    {
        echo 'Setting up instance... ' . PHP_EOL;
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

        $u = \BeanFactory::newBean('Users');
        $GLOBALS['current_user'] = $u->getSystemUser();
    }

    public static function clearCache()
    {
        echo 'Clearing cache...' . PHP_EOL;
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
        echo 'Executing basic instance warm-up...' . PHP_EOL;
        // rebuild some stuff
        self::buildAutoloaderCache();

        // quick load of all beans
        global $beanList;
        $full_module_list = array_merge($beanList, $app_list_strings['moduleList']);
        foreach ($full_module_list as $module => $label) {
            $bean = \BeanFactory::newBean($module);
            // load language too
            \LanguageManager::createLanguageFile($module, array('default'), true);
            $mod_strings = return_module_language($current_language, $module);
        }

        // load app strings
        $app_list_strings = return_app_list_strings_language($current_language);
        $app_strings = return_application_language($current_language);

        // load api
        $sd = new \ServiceDictionary();
        $sd->buildAllDictionaries();
    }
}
