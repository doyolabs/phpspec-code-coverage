<?php


namespace Doyo\PhpSpec\CodeCoverage;

use Doyo\Bridge\CodeCoverage\Environment\Runtime;
use Doyo\Bridge\CodeCoverage\CodeCoverage;
use Doyo\Bridge\CodeCoverage\Console\Console;
use Doyo\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Bridge\CodeCoverage\Report;
use Doyo\Bridge\CodeCoverage\Report\Html;
use Doyo\Bridge\CodeCoverage\Report\PHP;
use Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener;
use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Console\Input\InputOption;
use Doyo\Bridge\CodeCoverage\Processor;

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

        $this->loadDriver($container);
        $this->loadFilter($container, $params);
        $this->loadProcessor($container, $params);
        $this->loadReports($container, $params);
        $container->define('doyo.coverage.code_coverage', function($container){
            $processor = $container->get('doyo.coverage.processor');
            $input = $container->get('console.input');
            $output = $container->get('console.output');
            $consoleIO = new Console($input, $output);
            $runtime = $container->get('doyo.coverage.runtime');
            $dispatcher = new CodeCoverage($processor, $consoleIO, $runtime);
            return $dispatcher;
        });

        $container->define('doyo.coverage.listener',function($container){
            $coverage = $container->get('doyo.coverage.code_coverage');
            return new CoverageListener($coverage);
        }, ['event_dispatcher.listeners']);

        $container->define('doyo.coverage.report', function($container){

            $coverage = $container->get('doyo.coverage.code_coverage');
            $report = new Report();
            $coverage->addSubscriber($report);

            return $report;
        });


        $reportProcessors = $container->getByTag('doyo.coverage.reports');
        $report = $container->get('doyo.coverage.report');
        foreach($reportProcessors as $processor){
            $report->addProcessor($processor);
        }
    }

    private function loadDriver(ServiceContainer $container)
    {
        $container->define('doyo.coverage.runtime', function(){
            return new Runtime();
        });
        $container->define('doyo.coverage.driver', function($container){
            $driverClass = $container->get('doyo.coverage.runtime')->getDriverClass();
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
            'html' => Html::class,
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

        $options = [];

        if(is_string($reportConfig[$type])){
            $options['target'] = $reportConfig[$type];
        }else{
            $options = $reportConfig[$type];
        }
        $id = 'doyo.coverage.reports.'.$type;
        $container->define($id, function() use ($class, $options){
            return new $class($options);
        },['doyo.coverage.reports']);
    }

    public static function canCollectCodeCoverage()
    {
        return Extension::class !== Dummy::class;
    }
}