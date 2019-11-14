<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class LogicHooks extends Sugar\BaseLogic
{
    protected $coreApplicationHooksClasses = [
        'PMSELogicHook',
        'ActivityQueueManager',
        '\Sugarcrm\Sugarcrm\SearchEngine\HookHandler',
        'SugarMetric_HookManager',
    ];

    protected function getCustomApplicationHooks() : array
    {
        $res = [];
        $h = \LogicHook::initialize();
        $allHooks = $h->loadHooks('');

        // remove the core known application hooks

        foreach ($allHooks as $event => $eventHooks) {
            if (!empty($eventHooks)) {
                foreach ($eventHooks as $hook) {
                    // remove if it is one of the core application hook classes
                    if (!empty($hook[3]) && in_array($hook[3], $this->coreApplicationHooksClasses)) {
                        // do nothing
                    } else {
                        // if there is a file, and it is in custom
                        if (!empty($hook[2]) && preg_match('/custom\//', $hook[2])) {
                            $res[$event][] = $hook;
                        }
                        // if it uses namespaces, and it contains custom
                        if (!empty($hook[3]) && preg_match('/custom\\\/', $hook[3])) {
                            $res[$event][] = $hook;
                        }
                    }
                }
            }
        }

        return $res;
    }

    protected function getCustomModuleHooks(String $moduleDir) : array
    {
        $res = [];
        $h = \LogicHook::initialize();
        $allHooks = $h->loadHooks($moduleDir);

        foreach ($allHooks as $event => $eventHooks) {
            if (!empty($eventHooks)) {
                foreach ($eventHooks as $hook) {
                    // if there is a file, and it is in custom
                    if (!empty($hook[2]) && preg_match('/custom\//', $hook[2])) {
                        $res[$event][] = $hook;
                    }

                    // if it uses namespaces, and it contains custom
                    if (!empty($hook[3]) && preg_match('/custom\\\/', $hook[3])) {
                        $res[$event][] = $hook;
                    }
                }
            }
        }

        return $res;
    }

    protected function printHooksFromList(array $list)
    {
        if (!empty($list)) {
            foreach ($list as $event => $hooks) {
                if (!empty($hooks)) {
                    $this->writeln($event . ' hooks:');
                    foreach ($hooks as $hook) {
                        /*
                            [0] => 100
                            [1] => pmse
                            [2] => modules/pmse_Inbox/engine/PMSELogicHook.php
                            [3] => PMSELogicHook
                            [4] => after_delete
                        */
                        $this->writeln( (!empty($hook[0]) ? 'order: ' . $hook[0] . ' location: ' : '' ) . (!empty($hook[2]) ? $hook[2] . ' ' : '' ) .
                            (!empty($hook[3]) ? $hook[3] : '' ) . (!empty($hook[4]) ? '->' . $hook[4] . '()' : '' ));
                    }
                }
            }
        }
    }

    public function findHooks()
    {
        $appHooks = $this->getCustomApplicationHooks();

        if (!empty($appHooks)) {
            $this->writeln('Custom application Logic Hooks are in use. See file list below:');
            $this->printHooksFromList($appHooks);
        } else {
            $this->writeln('No Custom application wide Logic Hooks are in use.');
        }

        $moduleHooks = [];

        global $beanList, $app_list_strings;
        $fullModuleList = array_merge($beanList, $app_list_strings['moduleList']);
        asort($fullModuleList);
        foreach ($fullModuleList as $module => $label) {
            $bean = \BeanFactory::newBean($module);
            if ($bean instanceof \SugarBean && !empty($bean->module_dir)) {
                if (!isset($moduleHooks[$bean->module_dir])) {
                    $retrievedHooks = $this->getCustomModuleHooks($bean->module_dir);
                    if (!empty($retrievedHooks)) {
                        $moduleHooks[$bean->module_dir] = $retrievedHooks;
                    }
                }
            }
        }

        if (!empty($moduleHooks)) {
            $this->writeln('Custom module\'s Logic Hooks are in use. See file list below:');
            foreach ($moduleHooks as $module => $hooks) {
                $this->writeln('');
                $this->writeln('Hooks for module: ' . $module);
                $this->printHooksFromList($hooks);
            }
        } else {
            $this->writeln('No custom module\'s Logic Hooks are in use.');
        }
    }
}
