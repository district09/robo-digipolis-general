<?php

namespace DigipolisGent\Robo\Task\General;

use Ckr\Util\ArrayMerger;
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
class ReadProperties extends BaseTask
{

    /**
     * The finder object to scan for files.
     *
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * The directories to search in.
     *
     * @var array
     */
    protected $dirs;

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
    public function __construct($dirs = [])
    {
        $this->finder = new Finder();
        $this->dirs = $dirs;
    }

    /**
     * Sets the finder.
     *
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function finder(Finder $finder)
    {
        $this->finder = $finder;

        return $this;
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
     *
     * @codeCoverageIgnore
     */
    public function in($dirs)
    {
        $this->dirs = $dirs;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $this->finder->in($this->dirs)->files();
            $defaults = clone $this->finder;
            $packageOverrides = clone $this->finder;
            $projectConfig = [];

            // Get the property overrides for this project.
            $root = $this->getConfig()->get('digipolis.root.project', false);
            if ($root && file_exists($root . '/properties.yml')) {
                $this->printTaskInfo(
                    sprintf(
                        'Parsing config from %s.',
                        $root . '/properties.yml'
                    )
                );
                $contents = file_get_contents($root . '/properties.yml');
                $this->printTaskDebug(sprintf('Parsing %s', $contents));
                $projectConfig = Yaml::parse($contents);
            }

            $expander = class_exists('\\Grasmash\\YamlExpander\\Expander') ?
                '\\Grasmash\\YamlExpander\\Expander::expandArrayProperties' :
                [new \Grasmash\Expander\Expander(), 'expandArrayProperties'];
            $parsedConfig = call_user_func($expander,
                ArrayMerger::doMerge(
                    ArrayMerger::doMerge(
                        // Get the default properties.
                        $this->parseConfigFiles($defaults->name('default.properties.yml')),
                        // Get the property overrides for robo packages.
                        $this->parseConfigFiles($packageOverrides->name('properties.yml')),
                        ArrayMerger::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS
                    ),
                    // Add the project overrides last.
                    $projectConfig,
                    ArrayMerger::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS
                )
            );

            $this->printTaskDebug(sprintf('Resulted config: %s', print_r($parsedConfig, true)));

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
     * @param \Symfony\Component\Finder\Finder $files
     *   The files to parse.
     *
     * @return array
     *   The parsed config.
     */
    protected function parseConfigFiles(Finder $files)
    {
        $configs = [];
        foreach ($files as $file) {
            // Check if this is part of a Robo package.
            $path = $file->getRealPath();
            if (!file_exists(dirname($path) . '/RoboFile.php')) {
                continue;
            }
            $this->printTaskInfo(sprintf('Parsing config from %s.', $path));
            $contents = file_get_contents($path);
            $this->printTaskDebug(sprintf('Parsing %s', $contents));
            $configs[] = Yaml::parse($contents);
        }

        if (!$configs) {
          return [];
        }

        // Sort by priority, higher priority (lower number) takes precedence.
        usort($configs, function ($a, $b) {
            return ($a['_priority'] ?? 0 ) - ($b['_priority'] ?? 0);
        });

        $result = array_shift($configs);
        foreach ($configs as $config) {
            $result = ArrayMerger::doMerge(
                $config,
                $result,
                ArrayMerger::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS
            );
        }

        return $result;
    }
}
