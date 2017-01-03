<?php

namespace DigipolisGent\Tests\Robo\Task\General;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Common\CommandArguments;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class DetermineDrupalRootTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface, ConfigAwareInterface
{

    use \DigipolisGent\Robo\Task\General\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;
    use CommandArguments;
    use \Robo\Task\Base\loadTasks;
    use \Robo\Common\ConfigAwareTrait;

    /**
     * Set up the Robo container so that we can create tasks in our tests.
     */
    public function setUp()
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
        $finderMock = $this->getMockBuilder('\DrupalFinder\DrupalFinder')
          ->getMock();
        $finderMock
          ->expects($this->exactly(2))
          ->method('locateRoot')
          ->will($this->onConsecutiveCalls(true, false));
        $drupalroot = $this->getRandomString();

        $finderMock
          ->expects($this->once())
          ->method('getDrupalRoot')
          ->willReturn($drupalroot);

        // First run, root found.
        $result = $this->taskDetermineDrupalRoot(__DIR__)
          ->setDrupalFinder($finderMock)
          ->run();
        $this->assertEquals($drupalroot, $this->getConfig()->get('digipolis.root.drupal'));
        $this->assertEquals(0, $result->getExitCode());
        $this->assertEquals('Found Drupal root at ' . $drupalroot . '.', $result->getMessage());

        // Reset the config.
        $this->getConfig()->set('digipolis.root.drupal', null);

        // Second run, root not found.
        $result = $this->taskDetermineDrupalRoot(__DIR__)
          ->setDrupalFinder($finderMock)
          ->run();
        $this->assertNull($this->getConfig()->get('digipolis.root.drupal'));
        $this->assertEquals(1, $result->getExitCode());
        $this->assertEquals('Could not find the Drupal root in ' . __DIR__ . '.', $result->getMessage());
    }
}
