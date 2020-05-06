<?php

namespace Acquia\Cohesion\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;
use Symfony\Component\Console\Event\ConsoleCommandEvent;


/**
 * Defines commands related to Cohesion.
 */
class CohesionCommands extends BltTasks {

  /**
   * This will be called after the `drupal:update` command is executed.
   *
   * @hook post-command drupal:update
   */
  public function postUpdateCohesionTasks(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    $this->say("postCommandMessage hook: The {$command->getName()} command has run!");
  }


  /**
   * @hook post-command drupal:update
   */
  public function postCommand($result, CommandData $commandData)
  {
    $this->say("postCommandMessage hook: The {$command->getName()} command has run!");
  }

}
