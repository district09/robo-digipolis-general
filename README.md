# Robo Digipolis General

General Digipolis tasks for Robo Task Runner

[![Latest Stable Version](https://poser.pugx.org/digipolisgent/robo-digipolis-general/v/stable)](https://packagist.org/packages/digipolisgent/robo-digipolis-general)
[![Latest Unstable Version](https://poser.pugx.org/digipolisgent/robo-digipolis-general/v/unstable)](https://packagist.org/packages/digipolisgent/robo-digipolis-general)
[![Total Downloads](https://poser.pugx.org/digipolisgent/robo-digipolis-general/downloads)](https://packagist.org/packages/digipolisgent/robo-digipolis-general)
[![PHP 7 ready](http://php7ready.timesplinter.ch/digipolisgent/robo-digipolis-general/develop/badge.svg)](https://travis-ci.org/digipolisgent/robo-digipolis-general)
[![License](https://poser.pugx.org/digipolisgent/robo-digipolis-general/license)](https://packagist.org/packages/digipolisgent/robo-digipolis-general)

[![Build Status](https://travis-ci.org/digipolisgent/robo-digipolis-general.svg?branch=develop)](https://travis-ci.org/digipolisgent/robo-digipolis-general)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7520a070-4500-494e-80c8-e0b109fb3db6/mini.png)](https://insight.sensiolabs.com/projects/7520a070-4500-494e-80c8-e0b109fb3db6)
[![Code Climate](https://codeclimate.com/github/digipolisgent/robo-digipolis-general/badges/gpa.svg)](https://codeclimate.com/github/digipolisgent/robo-digipolis-general)
[![Test Coverage](https://codeclimate.com/github/digipolisgent/robo-digipolis-general/badges/coverage.svg)](https://codeclimate.com/github/digipolisgent/robo-digipolis-general/coverage)
[![Dependency Status](https://www.versioneye.com/user/projects/58777beb3c8039004dbe5748/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/58777beb3c8039004dbe5748)

## Tasks in this package

### DetermineProjectRoot

Determines the root folder of the project by looking for certain files in the
given folder. By default it looks for a `properties.yml` or a `composer.json`
file. Usually this task is ran before running an other task in the same command
so the config value can be passed to that task.

```php
// Recursively search for a project root folder in the current directory with a
// maximum depth of 2.
$result = $this->taskDetermineProjectRoot(getcwd(), 2)
    // Do not search in the tests and vendor folders.
    ->exclude(['tests', 'vendor'])
    // A folder containing a composer.json is considered a project root.
    ->searchFiles(['composer.json'])
    ->run();
// The project root is stored in the digipolis.root.project config.
$root = $this->getConfig()->get('digipolis.root.project');
```

### DetermineWebRoot

Determines the web root folder of the project by looking for certain files in
the given folder. By default it looks for a `index.php`, `index.html`,
`index.htm`, `home.php`, `home.html` or a `home.htm` file. Usually this task is
ran before running an other task in the same command so the config value can be
passed to that task.

```php
// Recursively search for a web root folder in the current directory with a
// maximum depth of 2.
$result = $this->taskDetermineWebRoot(getcwd(), 2)
    // Do not search in the tests and vendor folders.
    ->exclude(['tests', 'vendor'])
    // A folder containing an index.php is considered a project root.
    ->searchFiles(['index.php'])
    ->run();
// The project root is stored in the digipolis.root.web config.
$root = $this->getConfig()->get('digipolis.root.web');
```

### ReadProperties

Reads values from yaml files (`default.properties.yml` and `properties.yml`) and
stores them in config. Values from `default.properties.yml` will be overridden
if the exist in a `properties.yml`. If a valid path is set for
`digipolis.root.web` in config, and a `properties.yml` file exists in that path,
those values will have top priority.

```php
// Search for default.properties.yml and properties.yml files in the current
// directory.
$result = $this->taskReadProperties([getcwd()])
    ->run();
// Values are stored in config.
$root = $this->getConfig()->get('my.config.value');
```

## Using these tasks in a command

If you want to use these tasks in a command, you can use the
`\DigipolisGent\Robo\Task\General\Common\DigipolisPropertiesAware` trait and
implement the
`\DigipolisGent\Robo\Task\General\Common\DigipolisPropertiesAwareInterface`
interface. This will expose a readProperties method to which you can pass the
paths to the project root, the web root and the vendor folder. If the tasks to
determine the project and web root are available on the class using the trait,
and no project or web root are given as a parameter to the `readProperties`
method, these tasks will be used to determine the paths. They both default to
the current working directory. The vendor folder defaults to the vendor folder
in the web root. Your `RoboFile.php` might look something like this:

```php
class RoboFile extends \Robo\Tasks implements \DigipolisGent\Robo\Task\General\Common\DigipolisPropertiesAwareInterface
{
    use \DigipolisGent\Robo\Task\General\Common\DigipolisPropertiesAware;
    use \DigipolisGent\Robo\Task\General\loadTasks;

    public function myCommand(
        $arg1,
        $arg2,
        $opts = [
            'root|r' => null,
            'webroot|wr' => null,
            'vendor-folder|vf' => null,
        ]
    )
    {
        $this->readProperties(
            $opts['root'],
            $opts['webroot'],
            $opts['vendor-folder']
        );
        // All properties are stored in config now, so execute the command.
        $this->doCommand();
    }
}
