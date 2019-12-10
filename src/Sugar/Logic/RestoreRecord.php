<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class RestoreRecord extends Sugar\BaseLogic
{
    public function restore(string $moduleName, string $recordId)
    {
        if (empty($moduleName) || empty($recordId)) {
            $this->writeln('To be able to restore a record, the module name and a record id are required');
        }

        $this->writeln('Attempting to restore: "' . $moduleName . '" record with id: "' . $recordId . '" and most of its relationships');
        $this->writeln('');

        $skippedRelationshipsMessages = [];

        $mainBean = \BeanFactory::retrieveBean($moduleName, $recordId, ['deleted' => 0]);
        if (!empty($mainBean->id)) {

            if ($mainBean->deleted) {
                $this->writeln('Restored main record for: "' . $moduleName . '" with id "' . $mainBean->id . '"');
                $mainBean->mark_undeleted($mainBean->id);
            }
            $linkFields = $mainBean->get_linked_fields();

            foreach ($linkFields as $linkFieldData) {
                $linkField = $linkFieldData['name'];

                if ($mainBean->load_relationship($linkField)) {
                    // retrieve deleted relationships
                    $relatedBeans = $mainBean->$linkField->getBeans(['deleted' => 1]);
                    if (!empty($relatedBeans)) {
                        foreach ($relatedBeans as $relatedBean) {
                            if ($relatedBean->deleted) {
                                $relatedBean->mark_undeleted($relatedBean->id);
                                $this->writeln('Restored related record: "' . $relatedBean->getModuleName() . '" with id: "' . $relatedBean->id . '"');
                            }
                            $mainBean->$linkField->add($relatedBean->id);
                            $this->writeln('Updated relationship with related record: "' . $relatedBean->getModuleName() . '" through link field: "' . $linkField .
                                '" with id: "' . $relatedBean->id . '"');
                        }
                    } else {
                        $relObj = $mainBean->$linkField->getRelationshipObject();
                        if (empty($relObj->def['join_table']) && !empty($relObj->def['rhs_key'])) {
                            $skippedRelationshipsMessages[] = 'Module: "' . $relObj->def['rhs_module'] .
                                '" through link field: "' . $linkField . '" stored on field: "' . $relObj->def['rhs_key'] . '"';
                        }
                    }
                }
            }

            if (!empty($skippedRelationshipsMessages)) {
                $this->writeln('');
                $this->writeln('One-to-many relationships that are field-based (without joining table) that have been deleted cannot be restored');
                $this->writeln('The relationships that might contain non-restored records with: "' . $moduleName . '" are listed below');
                $this->writeln('');
                foreach ($skippedRelationshipsMessages as $message) {
                    $this->writeln($message);
                }
            }
        } else {
            $this->writeln('The provided record does not exist');
        }

        $this->writeln('');
    }
}
