<?php

namespace DigipolisGent\Tests\Robo\Task\General;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Common\CommandArguments;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class DetermineRootTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface, ConfigAwareInterface
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
        $composerRoot = $this->getRandomString();
        $file1 = $this->getMockBuilder('\Symfony\Component\Finder\SplFileInfo')
          // Does not work in PHP 5.5. See https://github.com/sebastianbergmann/phpunit/issues/1409
          //->disableOriginalConstructor()
          ->setConstructorArgs([__FILE__, __FILE__, __FILE__])
          ->getMock();
        $file1
          ->expects($this->once())
          ->method('getRealPath')
          ->willReturn($composerRoot . '/subir/composer.json');

        $file2 = $this->getMockBuilder('\Symfony\Component\Finder\SplFileInfo')
          // Does not work in PHP 5.5. See https://github.com/sebastianbergmann/phpunit/issues/1409
          //->disableOriginalConstructor()
          ->setConstructorArgs([__FILE__, __FILE__, __FILE__])
          ->getMock();
        $file2
          ->expects($this->once())
          ->method('getRealPath')
          ->willReturn($composerRoot . '/composer.json');

        $finderMock = $this->getMockBuilder('\Symfony\Component\Finder\Finder')
          ->setMethods(['getIterator'])
          ->getMock();
        $finderMock
          ->expects($this->exactly(3))
          ->method('getIterator')
          ->will($this->onConsecutiveCalls([$file1, $file2], [], []));

        // First run, root found.
        $result = $this->taskDetermineRoot(__DIR__)
          ->finder($finderMock)
          ->run();
        $this->assertEquals($composerRoot, $this->getConfig()->get('digipolis.root.project'));
        $this->assertEquals(0, $result->getExitCode());
        $this->assertEquals('Found root at ' . $composerRoot . '.', $result->getMessage());

        // Reset the config.
        $this->getConfig()->set('digipolis.root.project', null);

        // Second run, root not found.
        $result = $this->taskDetermineRoot(__DIR__)
          ->finder($finderMock)
          ->run();
        $cwd = getcwd();
        $this->assertEquals($cwd, $this->getConfig()->get('digipolis.root.project'));
        $this->assertEquals(0, $result->getExitCode());
        $this->assertEquals('Found root at ' . $cwd . '.', $result->getMessage());
    }
}
