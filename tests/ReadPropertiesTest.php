<?php

namespace DigipolisGent\Tests\Robo\Task\General;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PHPUnit\Framework\TestCase;
use Robo\Common\CommandArguments;
use Robo\Contract\ConfigAwareInterface;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class ReadPropertiesTest extends TestCase implements ContainerAwareInterface, ConfigAwareInterface
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
        // Test with root config and overrides.
        $this->getConfig()->set('digipolis.root.project', __DIR__ . '/root');
        $result = $this->taskReadProperties(__DIR__ . '/web')
          ->run();
        $this->assertEquals('default_value', $this->getConfig()->get('default_property'));
        $this->assertEquals('overridden_value', $this->getConfig()->get('overridden_property'));
        $this->assertEquals('custom_value', $this->getConfig()->get('custom_property'));
        $this->assertEquals('root_value', $this->getConfig()->get('overridden_property2'));
        $this->assertEquals('root_value', $this->getConfig()->get('custom_property2'));
        $this->assertEquals(0, $result->getExitCode());
        $this->assertNull($this->getConfig()->get('no_robo'));
        $this->assertEquals('Parsed all config.', $result->getMessage());

        // Test nested values.
        $nested = $this->getConfig()->get('nested');
        $this->assertEquals('default_value', $nested['default_property']);
        $this->assertEquals('overridden_value', $nested['overridden_property']);
        $this->assertEquals('custom_value', $nested['custom_property']);
        $this->assertEquals('root_value', $nested['overridden_property2']);
        $this->assertEquals('root_value', $nested['custom_property2']);
        $this->assertEquals(0, $result->getExitCode());
        $this->assertFalse(isset($nested['no_robo']));

        // Test with invalid yaml.
        $this->getConfig()->set('digipolis.root.project', __DIR__ . '/invalidyml');
        $result = $this->taskReadProperties(__DIR__ . '/web')
          ->run();
        $this->assertEquals(1, $result->getExitCode());
        $this->assertNotFalse(strpos($result->getMessage(), 'Malformed'));
    }
}
