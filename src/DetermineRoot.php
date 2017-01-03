<?php

namespace DigipolisGent\Robo\Task\General;

use DrupalFinder\DrupalFinder;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;

/**
 * Determines the root of a Drupal project and adds it to the config.
 * Config is available for classes implementing
 * \Robo\Contract\ConfigAwareInterface and using \Robo\Common\ConfigAwareTrait.
 *
 * ``` php
 * $this->taskDetermineRoot()
 *     ->dir(getcwd())
 *     ->run();
 * $this->getConfig()->get('digipolis.root.project');
 * ```
 *
 */
class DetermineRoot extends BaseTask
{
    /**
     * The directory in which to search for the project root.
     *
     * @var string
     */
    protected $dir;

    /**
     * The Drupal finder to use to find the project root.
     *
     * @var \DrupalFinder\DrupalFinder
     */
    protected $finder;

    /**
     * Creates a DetermineRoot object.
     *
     * @param string $dir
     *   The directory in which to search for the project root.
     */
    public function __construct($dir = null)
    {
        $this->dir = is_null($dir) ? getcwd() : realpath($dir);
        $this->finder = new DrupalFinder();
    }

    /**
     * Sets the directory in which to search for the project root.
     *
     * @param string $dir
     *   The directory to set.
     *
     * @return $this
     */
    public function dir($dir)
    {
        $this->dir = realpath($dir);

        return $this;
    }

    public function setDrupalFinder(DrupalFinder $finder) {
      $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!$this->finder->locateRoot($this->dir)) {
            return Result::error($this, 'Could not find the project root in ' . $this->dir . '.');
        }
        $root = $this->finder->getComposerRoot();
        $this->getConfig()->set('digipolis.root.project', $root);

        return Result::success($this, 'Found project root at ' . $root . '.');
    }
}
