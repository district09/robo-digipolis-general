<?php

namespace DigipolisGent\Tests\Robo\Task\General;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Common\CommandArguments;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class DetermineWebRootTest extends DetermineRootTest
{

    public function testRun() {
        $webRoot = $this->getRandomString();

        $file1 = $this->getMockBuilder('\Symfony\Component\Finder\SplFileInfo')
          ->disableOriginalConstructor()
          ->getMock();
        $file1
          ->expects($this->once())
          ->method('getRealPath')
          ->willReturn($webRoot . '/subir/index.php');

        $file2 = $this->getMockBuilder('\Symfony\Component\Finder\SplFileInfo')
          ->disableOriginalConstructor()
          ->getMock();
        $file2
          ->expects($this->once())
          ->method('getRealPath')
          ->willReturn($webRoot . '/index.php');

        $finderMock = $this->getMockBuilder('\Symfony\Component\Finder\Finder')
          ->setMethods(['getIterator'])
          ->getMock();
        $finderMock
          ->expects($this->exactly(7))
          ->method('getIterator')
          ->will($this->onConsecutiveCalls([$file1, $file2], [], [], [], [], [], []));

        // First run, root found.
        $result = $this->taskDetermineWebRoot(__DIR__)
          ->finder($finderMock)
          ->run();
        $this->assertEquals($webRoot, $this->getConfig()->get('digipolis.root.web'));
        $this->assertEquals(0, $result->getExitCode());
        $this->assertEquals('Found root at ' . $webRoot . '.', $result->getMessage());

        // Reset the config.
        $this->getConfig()->set('digipolis.root.web', null);

        // Second run, root not found.
        $result = $this->taskDetermineWebRoot(__DIR__)
          ->finder($finderMock)
          ->run();
        $cwd = getcwd();
        $this->assertEquals($cwd, $this->getConfig()->get('digipolis.root.project'));
        $this->assertEquals(0, $result->getExitCode());
        $this->assertEquals('Found root at ' . $cwd . '.', $result->getMessage());
    }
}
