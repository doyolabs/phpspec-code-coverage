<?php

namespace spec\Doyo\PhpSpec\CodeCoverage;

use Doyo\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\PhpSpec\CodeCoverage\Event\CoverageEvent;
use Doyo\PhpSpec\CodeCoverage\Report;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSpec extends ObjectBehavior
{
    private $targetDir;

    function let(
        TestReportProcessor $reportProcessor
    )
    {
        $this->targetDir = sys_get_temp_dir().'/doyo/report-test/file';
        $options = ['target' => $this->targetDir, 'type' => 'file'];
        $this->beConstructedWith($reportProcessor, $options);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    function it_should_subscribe_to_coverage_event()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::REPORT,'process');
    }

    function it_should_process_code_coverage_report(
        TestReportProcessor $reportProcessor,
        ProcessorInterface $processor,
        CoverageEvent $event,
        ConsoleIO $consoleIO
    )
    {
        $codeCoverage = new CodeCoverage(new Dummy());
        $event->getProcessor()->willReturn($processor);
        $event->getConsoleIO()->willReturn($consoleIO);
        $processor->getCodeCoverage()->willReturn($codeCoverage);
        $consoleIO
            ->writeln(Argument::containingString('Generated code coverage to: '))
            ->shouldBeCalledOnce()
        ;

        $reportProcessor->process($codeCoverage, $this->targetDir)
            ->shouldBeCalled()
        ;
        if(is_dir($this->targetDir)){
            rmdir($this->targetDir);
        }
        $this->process($event);
    }

    public function it_should_handle_error_during_report_process(
        TestReportProcessor $reportProcessor,
        ProcessorInterface $processor,
        CoverageEvent $event,
        ConsoleIO $consoleIO
    )
    {
        $codeCoverage = new CodeCoverage(new Dummy());
        $event->getProcessor()->willReturn($processor);
        $event->getConsoleIO()->willReturn($consoleIO);
        $processor->getCodeCoverage()->willReturn($codeCoverage);
        $consoleIO
            ->writeln(Argument::containingString('some error'))
            ->shouldBeCalledOnce()
        ;

        $e = new \Exception('some error');
        $reportProcessor->process($codeCoverage, $this->targetDir)
            ->shouldBeCalled()
            ->willThrow($e)
        ;
        $dirname = dirname($this->targetDir);
        if(is_dir($this->targetDir)){
            rmdir($dirname);
        }
        $this->process($event);
    }
}
