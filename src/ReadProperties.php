<?php

namespace DigipolisGent\Robo\Task\General;

use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses default.properties.yml and properties.yml files in to config. Config
 * is available for classes implementing \Robo\Contract\ConfigAwareInterface and
 * using \Robo\Common\ConfigAwareTrait.
 *
 * ``` php
 * $this->taskReadProperties()
 *     ->in(getcwd())
 *     ->followSymlinks()
 *     ->run();
 * $this->getConfig()->get('my.config.setting');
 * ```
 *
 */
class ReadProperties extends BaseTask implements ConfigAwareInterface
{

    use ConfigAwareTrait;

    /**
     * The finder object to scan for files.
     *
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|array $dirs
     *   A directory path or an array of directories in which to search for
     *   config files.
     *
     * @throws \InvalidArgumentException
     *   If one of the directories does not exist.
     */
    public function __construct($dirs = []) {
        $this->finder = new Finder();
        $this->finder->files();
        if ($dirs) {
          $this->finder->in($dirs);
        }
    }

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|array $dirs
     *   A directory path or an array of directories in which to search for
     *   config files.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     *   If one of the directories does not exist.
     */
    public function in($dirs)
    {
        $this->finder->in($dirs);

        return $this;
    }

    /**
     * Forces the following of symlinks.
     *
     * @return $this
     */
    public function followSymlinks()
    {
        $this->finder->followLinks();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run() {
      try {
          $defaults = clone $this->finder;
          $packageOverrides = clone $this->finder;
          $projectConfig = [];

          // Get the property overrides for this project.
          $root = $this->getConfig()->get('digipolis.root.project', false);
          if ($root && file_exists($root . '/properties.yml')) {
              $this->logger()->debug('Parsing config from ' . $root . '/properties.yml.');
              $projectConfig = Yaml::parse(file_get_contents($root . '/properties.yml'));
          }

          // Get the default properties.
          $parsedConfig = $this->parseConfigFiles($defaults->name('default.properties.yml'))
              // Get the property overrides for robo packages.
              + $this->parseConfigFiles($packageOverrides->name('properties.yml'))
              // Add the project overrides last.
              + $projectConfig;

          // Save the settings to config.
          $config = $this->getConfig();
          foreach ($parsedConfig as $key => $value) {
              $config->set($key, $value);
          }

      } catch (\Exception $exception) {
          return Result::fromException($this, $exception);
      }
      return Result::success($this, 'Parsed all config.');
    }

    /**
     * Helper function to parse config files.
     *
     * @param Finder $files
     *   The files to parse.
     *
     * @return array
     *   The parsed config.
     */
    protected function parseConfigFiles(Finder $files) {
        $config = [];
        foreach ($files as $file) {
            // Check if this is part of a Robo package.
            $path = $file->getRealPath();
            if (!file_exists(dirname($path) . '/RoboFile.php')) {
                continue;
            }
            $this->logger()->debug('Parsing config from ' . $path . '.');
            $config += Yaml::parse($path);
        }
        return $config;
    }
}
