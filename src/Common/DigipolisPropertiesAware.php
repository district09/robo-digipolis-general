<?php

namespace DigipolisGent\Robo\Task\General\Common;

trait DigipolisPropertiesAware
{
    /**
     * Boolean flag to indicate if the properties were already read.
     *
     * @var bool
     */
    protected $propertiesRead = false;

    /**
     * Read the properties from the YAML files. Determine project and web root
     * if needed.
     *
     * @param string|null $root
     *   The path to the project root, if null the project root will be
     *   determined by taskDetermineProjectRoot().
     * @param string|null $web
     *   The path to the project web root, if null the web root will be
     *   determined by taskDetermineWebRoot().
     * @param string|null $vendor
     *   The path to the vendor dir, if null defaults to $root/vendor.
     */
    public function readProperties($root = null, $web = null, $vendor = null)
    {
        if (!$this->propertiesRead) {
            if (is_null($root)) {
                if (is_callable([$this, 'taskDetermineProjectRoot'])) {
                    $this->taskDetermineProjectRoot()->run();
                }
                $root = $this->getConfig()->get('digipolis.root.project', getcwd());
            }
            if (is_null($web)) {
                if (is_callable([$this, 'taskDetermineWebRoot'])) {
                    $this->taskDetermineWebRoot()->run();
                }
                $web = $this->getConfig()->get('digipolis.root.web', $root);
            }
            if (is_null($vendor)) {
                $vendor = $root . '/vendor';
            }
            $this->taskReadProperties([$web, $vendor])->run();
            $this->propertiesRead = true;
        }
    }
}
