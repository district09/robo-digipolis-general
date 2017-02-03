<?php

namespace DigipolisGent\Robo\Task\General\Common;

interface DigipolisPropertiesAwareInterface
{
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
    public function readProperties($root = null, $web = null, $vendor = null);
}
