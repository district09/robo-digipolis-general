<?php

namespace DigipolisGent\Robo\Task\General;

trait Tasks
{
    /**
     * Creates a DetermineProjectRoot task.
     *
     * @param string $dir
     *    The directory in which to search for the project root.
     * @param int $depth
     *   The maximum depth to traverse directories.
     *
     * @return \DigipolisGent\Robo\Task\General\DetermineProjectRoot
     *   The determine root task.
     */
    protected function taskDetermineProjectRoot($dir = null, $depth = 2)
    {
        return $this->task(DetermineProjectRoot::class, $dir, $depth);
    }

    /**
     * Creates a DetermineWebRoot task.
     *
     * @param string $dir
     *    The directory in which to search for the web root.
     * @param int $depth
     *   The maximum depth to traverse directories.
     *
     * @return \DigipolisGent\Robo\Task\General\DetermineWebRoot
     *   The determine web root task.
     */
    protected function taskDetermineWebRoot($dir = null, $depth = 2)
    {
        return $this->task(DetermineWebRoot::class, $dir, $depth);
    }

    /**
     * Creates a ReadProperties task.
     *
     * @param string|array $dirs
     *   A directory path or an array of directories in which to search for
     *   config files.
     *
     * @return \DigipolisGent\Robo\Task\General\ReadProperties
     *   The read properties task.
     */
    protected function taskReadProperties($dirs = [])
    {
        return $this->task(ReadProperties::class, $dirs);
    }
}
