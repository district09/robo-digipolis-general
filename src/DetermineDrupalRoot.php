<?php

namespace DigipolisGent\Robo\Task\General;

use Robo\Result;

/**
 * Determines the Drupal root of a Drupal project and adds it to the config.
 * Config is available for classes implementing
 * \Robo\Contract\ConfigAwareInterface and using \Robo\Common\ConfigAwareTrait.
 *
 * ``` php
 * $this->taskDetermineDrupalRoot()
 *     ->dir(getcwd())
 *     ->run();
 * $this->getConfig()->get('digipolis.root.drupal');
 * ```
 *
 */
class DetermineDrupalRoot extends DetermineRoot
{

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!$this->finder->locateRoot($this->dir)) {
            return Result::error($this, 'Could not find the Drupal root in ' . $this->dir . '.');
        }
        $root = $this->finder->getDrupalRoot();
        $this->getConfig()->set('digipolis.root.drupal', $root);

        return Result::success($this, 'Found Drupal root at ' . $root . '.');
    }
}
