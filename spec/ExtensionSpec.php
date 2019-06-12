<?php

namespace spec\Doyo\PhpSpec\CodeCoverage;

use Doyo\Bridge\CodeCoverage\Report;
use Doyo\PhpSpec\CodeCoverage\Extension;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\ObjectBehavior;
use PhpSpec\ServiceContainer;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doyo\Bridge\CodeCoverage\Report\ReportProcessorInterface;

class ExtensionSpec extends ObjectBehavior
{
    function let(
        ServiceContainer $container,
        InputInterface $input,
        ConsoleIO $consoleIO
    )
    {
        $container->get('console.input')->willReturn($input);
        $container->get('console.io')->willReturn($consoleIO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Extension::class);
    }

    function it_should_add_coverage_options_to_runCommand(
        ServiceContainer $container,
        Command $command
    )
    {
        $serviceId = 'console.commands.run';

        $container->get($serviceId)
            ->shouldBeCalledOnce()
            ->willReturn($command);

        $command
            ->addOption('coverage', null, InputOption::VALUE_NONE, Argument::any())
            ->shouldBeCalledOnce()
        ;

        $this->addCoverageOptions($container, []);
    }

    function it_should_not_load_coverage_listener_without_coverage_options(
        ServiceContainer $container,
        InputInterface $input
    )
    {
        $container
            ->get('console.input')
            ->shouldBeCalledOnce()
            ->willReturn($input);

        $input->hasParameterOption(['--coverage'])
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->loadCoverageListener($container, []);
    }

    function it_should_load_coverage_listener(
        InputInterface $input,
        ServiceContainer $container,
        EventDispatcher $dispatcher,
        Report $report,
        ReportProcessorInterface $reportProcessor
    )
    {
        $input->hasParameterOption(Argument::any())->willReturn(true);
        $container
            ->define('doyo.coverage.driver', Argument::any())
            ->shouldBeCalledOnce();
        $container
            ->define('doyo.coverage.filter', Argument::any())
            ->shouldBeCalledOnce();
        $container
            ->define('doyo.coverage.processor', Argument::any())
            ->shouldBeCalledOnce();
        $container
            ->define('doyo.coverage.listener', Argument::any(), ['event_dispatcher.listeners'])
            ->shouldBeCalledOnce();
        $container
            ->define('doyo.coverage.runtime', Argument::cetera())
            ->shouldBeCalledOnce();
        $container
            ->define('doyo.coverage.code_coverage', Argument::cetera())
            ->shouldBeCalledOnce();
        $container
            ->define('doyo.coverage.report', Argument::cetera())
            ->shouldBeCalledOnce();

        $container->get('doyo.coverage.report')->willReturn($report);

        $container
            ->define(
                'doyo.coverage.reports.html',
                Argument::any(),
                ['doyo.coverage.reports']
            )
            ->shouldBeCalledOnce()
        ;
        $container
            ->define(
                'doyo.coverage.reports.php',
                Argument::any(),
                ['doyo.coverage.reports']
            )
            ->shouldBeCalledOnce()
        ;

        $report->addProcessor($reportProcessor)
            ->shouldBeCalledOnce();

        $container->getByTag('doyo.coverage.reports')
            ->willReturn([$reportProcessor])
            ->shouldBeCalledOnce();
        $container->get('doyo.coverage.report')
            ->willReturn($report);


        $this->loadCoverageListener($container, [
            'reports' => [
                'php' => __DIR__,
                'html' => __DIR__
            ]
        ]);
    }
}
