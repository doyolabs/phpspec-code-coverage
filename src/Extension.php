<?php


namespace Doyo\PhpSpec\CodeCoverage;

use Doyo\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener;
use Doyo\Symfony\Bridge\EventDispatcher\EventDispatcher;
use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;
use SebastianBergmann\CodeCoverage\Driver\PHPDBG;
use SebastianBergmann\CodeCoverage\Driver\Xdebug;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Html\Facade;
use SebastianBergmann\CodeCoverage\Report\PHP;
use SebastianBergmann\Environment\Runtime;
use Symfony\Component\Console\Input\InputOption;
use Doyo\PhpSpec\CodeCoverage\Processor;

class Extension implements BaseExtension
{
    public function load(ServiceContainer $container, array $params)
    {
        $this->addCoverageOptions($container);
        $this->loadCoverageListener($container, $params);
    }

    public function addCoverageOptions(ServiceContainer $container)
    {
        /* @var \PhpSpec\Console\Command\RunCommand $command */
        $command = $container->get('console.commands.run');
        $command->addOption(
            'coverage',
            null,
            InputOption::VALUE_NONE,
            'Run phpspec with code coverage'
        );
    }

    public function loadCoverageListener(ServiceContainer $container, array $params)
    {
        /* @var \Symfony\Component\Console\Input\InputInterface $input */
        $input = $container->get('console.input');

        if(false === $input->hasParameterOption(['--coverage'])){
            return;
        }

        if(static::getDriverClass() === Dummy::class){
            /* @var \PhpSpec\Console\ConsoleIO $output */
            $output = $container->get('console.io');
            $output->writeln('<error>No code coverage driver available</error>');
            return;
        }

        $this->loadDriver($container, $params);
        $this->loadFilter($container, $params);
        $this->loadProcessor($container, $params);
        $this->loadReports($container, $params);
        $container->define('doyo.coverage.dispatcher', function($container){
            $dispatcher = new EventDispatcher();

            return $dispatcher;
        });

        $container->define('doyo.coverage.listener',function($container){
            $dispatcher = $container->get('doyo.coverage.dispatcher');
            $consoleIO = $container->get('console.io');
            $processor = $container->get('doyo.coverage.processor');
            return new CoverageListener($dispatcher, $processor, $consoleIO);
        }, ['event_dispatcher.listeners']);

        $reports = $container->getByTag('doyo.coverage.reports');
        $dispatcher = $container->get('doyo.coverage.dispatcher');
        foreach($reports as $report){
            $dispatcher->addSubscriber($report);
        }
    }

    public static function getDriverClass()
    {
        static $runtime;
        if(!$runtime instanceof Runtime){
            $runtime = new Runtime();
        }

        $driverClass = Dummy::class;
        if($runtime->canCollectCodeCoverage()){
            if($runtime->isPHPDBG()){
                $driverClass = PHPDBG::class;
            }else{
                $driverClass = Xdebug::class;
            }
        }

        return $driverClass;
    }

    private function loadDriver(ServiceContainer $container, array $params)
    {
        $driverClass = static::getDriverClass();
        $container->define('doyo.coverage.driver', function() use ($params, $driverClass){
            return new $driverClass;
        });
    }

    private function loadFilter(ServiceContainer $container, array $params)
    {
        $container->define('doyo.coverage.filter', function() use ($params){
            $filters = $params['filters'] ?:[];
            $whitelist = isset($filters['whitelist']) ? $filters['whitelist']:[];
            $blacklist = isset($filters['blacklist']) ? $filters['blacklist']:[];
            $filter = new Filter();

            foreach($whitelist as $dir){
                $filter->addDirectoryToWhitelist($dir);
            }

            foreach($blacklist as $dir){
                $filter->removeDirectoryFromWhitelist($dir);
            }

            return $filter;
        });
    }

    private function loadProcessor(ServiceContainer $container, array $params)
    {
        $container->define('doyo.coverage.processor', function($container) use ($params){
            $driver = $container->get('doyo.coverage.driver');
            $filter = $container->get('doyo.coverage.filter');
            $processor = new Processor($driver, $filter);

            return $processor;
        });
    }

    private function loadReports(ServiceContainer $container, array $params)
    {
        $reports = [
            'html' => Facade::class,
            'php' => PHP::class
        ];

        $reportConfig = isset($params['reports']) ? $params['reports']:[];
        foreach($reports as $type => $class){
            $this->configureReport($container, $reportConfig, $type, $class);
        }
    }

    private function configureReport(ServiceContainer $container, array $reportConfig, $type, $class)
    {
        if(!isset($reportConfig[$type])){
            return;
        }

        $dirTypes = ['html'];
        $fsType = in_array($type, $dirTypes) ? 'dir':'file';
        $options = array();
        $test = $reportConfig[$type];

        if(is_string($test)){
            $options['target'] = $test;
        }else{
            $options = $reportConfig[$type];
        }

        $options['type'] = $fsType;
        $r = new \ReflectionClass($class);
        $constructorParams = [];

        if(!is_null($r->getConstructor())){
            foreach($r->getConstructor()->getParameters() as $parameter){
                $name = $parameter->getName();
                if($parameter->isDefaultValueAvailable()){
                    break;
                }
                $default = $parameter->getDefaultValue();
                $constructorParams[] = isset($options[$name]) ? $options[$name]:$default;
            }
        }

        $id = 'doyo.coverage.reports.'.$type;
        $container->define($id, function($container) use ($class, $constructorParams, $options){
            $r = new \ReflectionClass($class);
            $processor = $r->newInstanceArgs($constructorParams);
            $report = new Report($processor, $options);
            return $report;
        },['doyo.coverage.reports']);
    }
}