<?php

namespace DigipolisGent\Robo\Task\General;

use Robo\Result;
use Robo\Task\BaseTask;
use Symfony\Component\Finder\Finder;

/**
 * Determines the root of a web project and adds it to the config.
 * Config is available for classes implementing
 * \Robo\Contract\ConfigAwareInterface and using \Robo\Common\ConfigAwareTrait.
 *
 * ``` php
 * $this->taskDetermineProjectRoot()
 *     ->dir(getcwd())
 *     ->run();
 * $this->getConfig()->get('digipolis.root.project');
 * ```
 *
 */
class DetermineProjectRoot extends BaseTask
{
    /**
     * The directory in which to search for the project root.
     *
     * @var string
     */
    protected $dir;

    /**
     * The maximum depth to traverse directories.
     *
     * @var int
     */
    protected $depth;

    /**
     * The Symfony finder to use to find the project root.
     *
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * The file names that determine the root dir.
     *
     * @var array
     */
    protected $searchFiles = ['properties.yml', 'composer.json'];

    /**
     * The config key where the root should be saved.
     *
     * @var string
     */
    protected $configKey = 'digipolis.root.project';

    /**
     * Creates a DetermineProjectRoot object.
     *
     * @param string $dir
     *   The directory in which to search for the project root.
     */
    public function __construct($dir = null, $depth = 2)
    {
        $this->dir = is_null($dir) ? getcwd() : realpath($dir);
        $this->depth = $depth;
        $this->finder = new Finder();
    }

    /**
     * Sets the directory in which to search for the project root.
     *
     * @param string $dir
     *   The directory to set.
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function dir($dir)
    {
        $this->dir = realpath($dir);

        return $this;
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
     * Sets the search files.
     *
     * @param array $searchFiles
     *   The files to search for when searching for the project root.
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function searchFiles(array $searchFiles)
    {
        $this->searchFiles = $searchFiles;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $finder = clone $this->finder;
        $finder->in([$this->dir])->depth('<=' . $this->depth)->files();
        $rootCandidates = [];
        foreach ($this->searchFiles as $searchFile) {
            $fileFinder = clone $finder;
            $fileFinder->name($searchFile);
            foreach ($fileFinder->getIterator() as $file) {
                $rootCandidates[] = dirname($file->getRealPath());
            }
            if ($rootCandidates) {
                break;
            }
        }
        usort(
            $rootCandidates,
            function ($a, $b) {
                return count(explode(DIRECTORY_SEPARATOR, $a)) - count(explode(DIRECTORY_SEPARATOR, $b));
            }
        );
        $root =  $rootCandidates ? reset($rootCandidates) : getcwd();
        $this->getConfig()->set($this->configKey, $root);

        return Result::success($this, 'Found root at ' . $root . '.');
    }
}
