<?php

namespace DigipolisGent\Robo\Task\General;

trait loadTasks
{
    /**
     * Creates a DetermineRoot task.
     *
     * @param string $dir
     *    The directory in which to search for the project root.
     *
     * @return \DigipolisGent\Robo\Task\General\DetermineRoot
     *   The determine root task.
     */
    protected function taskDetermineRoot($dir = null)
    {
        return $this->task(DetermineRoot::class, $dir);
    }

    /**
     * Creates a DetermineDrupalRoot task.
     *
     * @param string $dir
     *    The directory in which to search for the Drupal root.
     *
     * @return \DigipolisGent\Robo\Task\General\DetermineDrupalRoot
     *   The determine Drupal root task.
     */
    protected function taskDetermineDrupalRoot($dir = null)
    {
        return $this->task(DetermineDrupalRoot::class, $dir);
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
