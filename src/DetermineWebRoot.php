<?php

namespace DigipolisGent\Robo\Task\General;

/**
 * Determines the Web root of a Web project and adds it to the config.
 * Config is available for classes implementing
 * \Robo\Contract\ConfigAwareInterface and using \Robo\Common\ConfigAwareTrait.
 *
 * ``` php
 * $this->taskDetermineWebRoot()
 *     ->dir(getcwd())
 *     ->run();
 * $this->getConfig()->get('digipolis.root.web');
 * ```
 *
 */
class DetermineWebRoot extends DetermineProjectRoot
{

    /**
     * {@inheritdoc}
     */
    protected $searchFiles = ['index.php', 'index.html', 'index.htm', 'home.php', 'home.html', 'home.htm'];

    /**
     * {@inheritdoc}
     */
    protected $configKey = 'digipolis.root.web';
}
