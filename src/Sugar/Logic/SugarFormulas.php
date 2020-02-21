<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class SugarFormulas extends Sugar\BaseLogic
{
    protected $extensionVardefsPatterns = ['/.php/', '/Vardefs/'];
    protected $relatedFormulasPatterns = [
        '/related\(/',
        '/countConditional\(/',
        '/rollupAve\(/',
        '/rollupConditionalSum\(/',
        '/rollupMax\(/',
        '/rollupMin\(/',
        '/rollupSum\(/',
        '/count\(/',
        '/opportunitySalesStage\(/',
        '/rollupConditionalMinDate\(/',
        '/maxRelatedDate\(/',
    ];

    protected function seekExtensionVardefs() : array
    {
        return $this->findFiles('custom/Extension/modules', $this->extensionVardefsPatterns, []);
    }

    protected function findModuleFromVadefsExtension(string $path) : string
    {
        $exploded = explode('/', $path);
        if (!empty($exploded) && !empty($exploded[3])) {
            return $exploded[3];
        }
        return '';
    }

    protected function findDictionaryKeyword(string $module) : string
    {
        return get_singular_bean_name($module);
    }

    public function findFormulaFields() : array
    {
        $modulesWithFormulas = [];
        $modules = $this->getFullModuleList();
        foreach ($modules as $module) {
            $bean = \BeanFactory::newBean($module);
            if (!empty($bean->field_defs)) {
                foreach ($bean->field_defs as $name => $value) {
                    if (!empty($value['formula'])) {
                        $modulesWithFormulas[$module][$name] = $value['formula'];
                    }
                }
            }
        }
        return $modulesWithFormulas;
    }

    public function outputFormulas(array $formulas)
    {
        if (!empty($formulas)) {
            foreach ($formulas as $module => $fields) {
                foreach ($fields as $fieldName => $formula) {
                    if (!empty($formula)) {
                        $this->writeln('Module: ' . $module);
                        $this->writeln('Field: ' . $fieldName);
                        $this->writeln('Formula: ' . $formula);
                        $this->writeln('');
                    }
                }
            }
        }
    }

    public function findFormulas()
    {
        $this->writeln('');
        $this->writeln('Finding all formula fields');
        $this->outputFormulas($this->findFormulaFields());
    }

    public function findRelatedFormulaFields() : array
    {
        $modulesWithFormulas = [];
        $formulas = $this->findFormulaFields();
        foreach ($formulas as $module => $fields) {
            foreach ($fields as $fieldName => $formula) {
                if (!empty($formula)) {
                    foreach ($this->relatedFormulasPatterns as $pattern) {
                        if (preg_match($pattern, $formula)) {
                            $modulesWithFormulas[$module][$fieldName] = $formula;
                            break;
                        }
                    }
                }
            }
        }
        return $modulesWithFormulas;
    }

    public function findRelatedFormulas()
    {
        $this->writeln('');
        $this->writeln('Attempting to identify all related formula fields');
        $this->outputFormulas($this->findRelatedFormulaFields());
    }

    public function findCustomFilesWithFormulas()
    {
        $this->writeln('');
        $this->writeln('Attempting to find all custom extension formula files');
        $data = $this->findFormulasFromExtensions();
        if (!empty($data)) {
            foreach ($data as $module => $files) {
                foreach ($files as $file) {
                    $this->writeln($module . ' ' . $file);
                }
            }
        }
    }

    public function findFormulasFromExtensions() : array
    {
        $vardefs = $this->seekExtensionVardefs();
        $modulesWithFormulas = [];
        if (!empty($vardefs)) {
            foreach ($vardefs as $file) {
                $content = file_get_contents($file);
                if (!empty($content) && preg_match('/formula/', $content)) {
                    $module = $this->findModuleFromVadefsExtension($file);
                    $modulesWithFormulas[$module][] = $file;
                }
            }
        }
        return $modulesWithFormulas;
    }
}
