<?php

namespace Acquia\Cohesion\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Defines commands related to Site Studio.
 */
class CohesionCommands extends BltTasks {

  /**
   * @hook post-command drupal:config:import
   */
  public function postCommand($result, CommandData $commandData)
  {
    $result = $this->taskDrush()
      ->stopOnFail()
      ->drush("pm:list --filter=\"cohesion_sync\" --status=Enabled --field=status")
      ->run();

    if(trim($result->getMessage()) === "Enabled") {
      // Import cohesion assets from the API.
      $result = $this->taskDrush()
        ->stopOnFail()
        ->drush("cohesion:import")
        ->run();

      // Import Site Studio configuration from the sync folder.
      $result = $this->taskDrush()
        ->stopOnFail()
        ->drush("sync:import --overwrite-all")
        ->run();

      // Rebuild Site Studio.
      $result = $this->taskDrush()
        ->stopOnFail()
        ->drush("cohesion:rebuild")
        ->run();
    } else {
      $this->say("Site Studio sync is not enabled. Skipping Site Studio import and rebuild.");
    }
  }
}
