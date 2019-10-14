<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class MaintenanceMode extends Sugar\BaseLogic
{
    public function changeMaintenanceSetting($newStatus)
    {
        $newStatus = (bool)$newStatus;
        $labelStatus = (empty($newStatus)) ? 'off' : 'on';
        // if already the new status, say so
        if ((empty($GLOBALS['sugar_config']['maintenanceMode']) && empty($newStatus)) ||
            $GLOBALS['sugar_config']['maintenanceMode'] === $newStatus) {
            $this->writeln('The configuration setting maintenanceMode is already set to: ' . $labelStatus);
        } else {
            $configurator = new \Configurator();
            $configurator->config['maintenanceMode'] = $newStatus;
            $configurator->handleOverride();

            $this->writeln('The configuration setting maintenanceMode is now set to: ' . $labelStatus);
        }
        if ($newStatus) {
            $this->writeln('The system is ONLY accessible via the UI by ADMINISTRATOR users');
        } else {
            $this->writeln('The system is accessible via the UI by all users');
        }
    }
}
