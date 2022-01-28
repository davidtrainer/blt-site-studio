<?php

namespace Acquia\Cohesion\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Component\Uuid\Php;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Robo\Contract\VerbosityThresholdInterface;
use Acquia\Blt\Robo\Commands\Recipes\ConfigSplitCommand;

/**
 * Defines commands related to Site Studio.
 */
class CohesionCommands extends BltTasks {

  /**
   * An instance of the Php UUID generator used by the Drupal UUID service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuidGenerator;

  /**
   * An instance of the Twig template environment.
   *
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->uuidGenerator = new Php();
    $template_dir = $this->getConfigValue('repo.root') . '/vendor/acquia/blt-site-studio/config';
    $docroot = $this->getConfigValue('docroot');
    $loader = new \Twig_Loader_Filesystem($template_dir);
    $this->twig = new \Twig_Environment($loader);
    $this->configSyncDir = $docroot . '/' . $this->getConfigValue('cm.core.dirs.sync.path');
    $this->siteStudioSyncDir = $this->getConfigValue('repo.root') ."/config/site_studio_sync";
  }


  /**
   * @hook post-command drupal:config:import
   */
  public function postCommand($result, CommandData $commandData)
  {
    $rebuild = $this->getConfigValue('site-studio.rebuild');
    $sync_import = $this->getConfigValue('site-studio.sync-import');
    $package_import = $this->getConfigValue('site-studio.package-import');
    $cohesion_import = $this->getConfigValue('site-studio.cohesion-import');

    // Check if both package_import and sync_import are enabled.
    if (isset($sync_import) && $sync_import && isset($package_import) && $package_import) {
      throw new BltException("Cannot use both sync import and package import simultaneously. Use one or the other.");
    }

    $result = $this->taskDrush()
      ->stopOnFail()
      ->drush("cc drush")
      ->drush("pm:list --filter=\"cohesion_sync\" --status=Enabled --field=status")
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to filter modules!");
    }

    if (trim($result->getMessage()) === "Enabled") {
      // Rebuild cache.
      $result = $this->taskDrush()
        ->stopOnFail()
        ->alias("self")
        ->drush("cr")
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Failed to rebuild drupal caches!");
      }

      if (!isset($cohesion_import) || $cohesion_import == TRUE) {
        // Import Site Studio assets from the API.
        $result = $this->taskDrush()
          ->stopOnFail()
          ->drush("cohesion:import")
          ->run();

        if (!$result->wasSuccessful()) {
          throw new BltException("Failed to execute cohesion:import!");
        }
      } else {
        $this->say("Cohesion Import disabled via blt.yml, skipping.");
      }

      if ($sync_import == TRUE) {
        // Import Site Studio configuration from the sync folder using legacy package management commands.
        $result = $this->taskDrush()
          ->stopOnFail()
          ->drush("sync:import --overwrite-all --force --no-rebuild")
          ->run();

        if (!$result->wasSuccessful()) {
          throw new BltException("Failed to execute sync:import!");
        }
      } else {
        $this->say("Cohesion Sync Import disabled via blt.yml, skipping.");
      }

      if ($package_import == TRUE && !$sync_import) {
        // Import Site Studio configuration using package management commands.
        $result = $this->taskDrush()
          ->stopOnFail()
          ->drush("sitestudio:package:import --diff --yes")
          ->run();

        if (!$result->wasSuccessful()) {
          throw new BltException("Failed to execute sitestudio:package:import!");
        }
      }
      else {
        $this->say("Site Studio Package Import disabled via blt.yml, skipping.");
      }

      if (!isset($rebuild) || $rebuild == TRUE) {
        // Rebuild Site Studio.
        $result = $this->taskDrush()
          ->stopOnFail()
          ->drush("cohesion:rebuild")
          ->run();
        if (!$result->wasSuccessful()) {
          throw new BltException("Failed to execute cohesion:rebuild!");
        }
      } else {
        $this->say("Cohesion Rebuild disabled via blt.yml, skipping.");
      }

      // Is the site in maintenance mode?
      $result = $this->taskDrush()
        ->stopOnFail()
        ->drush("state:get system.maintenance_mode")
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Failed to validate state of maintenance mode!");
      }

      if (trim($result->getMessage()) === '1') {
        // Take the site out of maintenance mode.
        $result = $this->taskDrush()
          ->stopOnFail()
          ->alias("self")
          ->drush("state:set system.maintenance_mode 0 --input-format=integer")
          ->run();

        if (!$result->wasSuccessful()) {
          throw new BltException("Failed to take the site out of maintenance mode!");
        }
      }
    } else {
      $this->say("Site Studio sync is not enabled. Skipping Site Studio import and rebuild.");
    }
  }

  /**
   * Initializes default Site Studio config split configuration for this project.
   *
   * @command recipes:config:init:site-studio
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function generateSiteStudioConfig() {
    $this->say("This command will automatically generate and place configuration and settings files for Site Studio.");
    $result = $this->taskFilesystemStack()
      ->mkdir($this->siteStudioSyncDir)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $this->siteStudioSyncDir.");
    }

    $config_files = [
      'config_ignore.settings',
      'config_split.config_split.site_studio_ignored_config',
    ];

    $this->createConfig($config_files);

    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('repo.root') . '/vendor/acquia/blt-site-studio/config/.htaccess', $this->siteStudioSyncDir . '/.htaccess', TRUE)
      ->copy($this->getConfigValue('repo.root') . '/vendor/acquia/blt-site-studio/config/README.md', $this->siteStudioSyncDir . '/README.md', TRUE)
      ->copy($this->getConfigValue('repo.root') . '/vendor/acquia/blt-site-studio/settings/global.settings.php', $this->getConfigValue('docroot') . '/sites/settings/global.settings.php', TRUE)
      ->copy($this->getConfigValue('repo.root') . '/vendor/acquia/blt-site-studio/settings/site-studio.settings.php', $this->getConfigValue('docroot') . '/sites/settings/site-studio.settings.php', TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize Site Studio configuration.");
    }

    // Sets default values for the project's blt.yml file.
    $project_yml = $this->getConfigValue('blt.config-files.project');
    $this->say("Updating ${project_yml}...");
    $project_config = YamlMunge::parseFile($project_yml);
    $project_config['site-studio']['cohesion-import'] = TRUE;
    $project_config['site-studio']['sync-import'] = TRUE;
    $project_config['site-studio']['rebuild'] = TRUE;

    try {
      YamlMunge::writeFile($project_yml, $project_config);
    }
    catch (\Exception $e) {
      throw new BltException("Unable to update $project_yml.");
    }

    // Automatically add config split and config ignore to project configuration.
    $core_extensions = YamlMunge::parseFile($this->configSyncDir . '/core.extension.yml');
    if (!array_key_exists("config_split", $core_extensions['module'])) {
      $core_extensions['module']['config_split'] = 0;
    }
    if (!array_key_exists("config_filter", $core_extensions['module'])) {
      $core_extensions['module']['config_filter'] = 0;
    }
    if (!array_key_exists("config_ignore", $core_extensions['module'])) {
      $core_extensions['module']['config_ignore'] = 0;
    }

    try {
      ksort($core_extensions['module']);
      YamlMunge::writeFile($this->configSyncDir . '/core.extension.yml', $core_extensions);
    }
    catch (\Exception $e) {
      throw new BltException("Unable to update core.extension.yml");
    }

  }

  /**
   * Create a config_split configuration and directory for the given split.
   *
   * @param string $name
   *   The name of the split to create.
   */
  protected function createConfig($configs) {
    foreach ($configs as $config) {
      $id = strtolower($config);
      $config_file = $this->configSyncDir . "/{$id}.yml";
      if (file_exists($config_file)) {
        $this->say("The config file for $config already exists. Skipping.");
      }
      else {
        $uuid = $this->uuidGenerator->generate();
        $output = $this->twig->render($config . ".yml.twig", [
          'uuid' => $uuid,
        ]);
        $this->writeSplitConfig($config_file, $output);
      }
    }
  }

  /**
   * Write the config_split configuration YAML file in the given directory.
   *
   * @param string $file_path
   *   The path where the file should be written.
   * @param string $config
   *   The config file contents.
   */
  protected function writeSplitConfig($file_path, $config) {
    $result = $this->taskWriteToFile($file_path)
      ->text($config)
      ->run();
    if (!$result) {
      throw new BltException("Unable to write $file_path.");
    }
  }

}
