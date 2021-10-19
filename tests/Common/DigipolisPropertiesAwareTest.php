<?php

namespace DigipolisGent\Tests\Robo\Task\General\Common;

use DigipolisGent\Robo\Task\General\Common\DigipolisPropertiesAwareInterface;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PHPUnit\Framework\TestCase;
use Robo\Common\CommandArguments;
use Robo\Contract\ConfigAwareInterface;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class DigipolisPropertiesAwareTest extends TestCase implements ContainerAwareInterface, ConfigAwareInterface, DigipolisPropertiesAwareInterface
{
    use \DigipolisGent\Robo\Task\General\Common\DigipolisPropertiesAware;
    use TaskAccessor;
    use ContainerAwareTrait;
    use CommandArguments;
    use \Robo\Task\Base\loadTasks;
    use \Robo\Common\ConfigAwareTrait;

    protected $determineProjectRootCalled = false;
    protected $determineWebRootCalled = false;
    protected $readPropertiesCalled = false;

    /**
     * Set up the Robo container so that we can create tasks in our tests.
     */
    public function setUp(): void
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
        $this->setConfig(Robo::config());
    }


    protected function getRandomString($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Scaffold the collection builder.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The collection builder.
     */
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();

        return $this->getContainer()
            ->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testRun() {
        $this->readProperties();
        $this->assertTrue($this->determineProjectRootCalled);
        $this->assertTrue($this->determineWebRootCalled);
        $this->assertTrue($this->readPropertiesCalled);
    }

    protected function taskDetermineProjectRoot($dir = null, $depth = 2)
    {
        $this->determineProjectRootCalled = true;
        // Assert no parameters were passed.
        $this->assertNull($dir);
        $this->assertEquals(2, $depth);
        return $this
            ->getMockBuilder('\stdClass')
            ->setMethods(['run'])
            ->getMock();
    }

    protected function taskDetermineWebRoot($dir = null, $depth = 2)
    {
        $this->determineWebRootCalled = true;

        // Assert no parameters were passed.
        $this->assertNull($dir);
        $this->assertEquals(2, $depth);
        return $this->getMockBuilder('\stdClass')
            ->setMethods(['run'])
            ->getMock();
    }

    protected function taskReadProperties($dirs = [])
    {
        $this->readPropertiesCalled = true;
        // Assert the root and vendor dir were passed.
        $cwd = getcwd();
        $this->assertEquals([$cwd, $cwd . '/vendor'], $dirs);
        return $this->getMockBuilder('\stdClass')
            ->setMethods(['run'])
            ->getMock();
    }
}
