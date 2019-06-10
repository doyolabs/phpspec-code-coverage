<?php


namespace Doyo\PhpSpec\CodeCoverage\Listener;


use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\PhpSpec\CodeCoverage\Event\CoverageEvent;
use Doyo\Symfony\Bridge\EventDispatcher\EventDispatcher;
use Doyo\Symfony\Bridge\EventDispatcher\EventDispatcherInterface;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageListener implements EventSubscriberInterface
{
    /**
     * @var ProcessorInterface $processor
     */
    private $processor;

    /**
     * @var ConsoleIO
     */
    private $consoleIO;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $canCollectCoverage;

    /**
     * CoverageListener constructor.
     * @param ProcessorInterface $processor
     * @param ConsoleIO $consoleIO
     */
    public function __construct(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor,
        ConsoleIO $consoleIO,
        bool $canCollectCoverage
    )
    {
        $this->dispatcher = $dispatcher;
        $this->processor = $processor;
        $this->consoleIO = $consoleIO;
        $this->canCollectCoverage = $canCollectCoverage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'beforeExample' => ['beforeExample', -10],
            'afterExample' => ['afterExample', -10],
            'afterSuite' => ['afterSuite', -10]
        ];
    }

    public function beforeExample(ExampleEvent $suiteEvent)
    {
        if(!$this->canCollectCoverage){
            return;
        }
        $example = $suiteEvent->getExample();
        $processor = $this->processor;

        $name = strtr('%spec%::%example%', [
            '%spec%'    => $example->getSpecification()->getTitle(),
            '%example%' => $example->getFunctionReflection()->getName(),
        ]);
        $testCase = new TestCase($name);
        $processor->setCurrentTestCase($testCase);
        $processor->start($testCase);
    }

    public function afterExample(ExampleEvent $exampleEvent)
    {
        if(!$this->canCollectCoverage){
            return;
        }
        $processor = $this->processor;
        $result = $exampleEvent->getResult();
        $testCase = $processor->getCurrentTestCase();

        $map = [
            ExampleEvent::PASSED => TestCase::RESULT_PASSED,
            ExampleEvent::SKIPPED => TestCase::RESULT_SKIPPED,
            ExampleEvent::FAILED => TestCase::RESULT_FAILED,
            ExampleEvent::BROKEN => TestCase::RESULT_ERROR,
            ExampleEvent::PENDING => TestCase::RESULT_SKIPPED,
        ];

        $result = $map[$result];
        $testCase->setResult($result);
        $processor->addTestCase($testCase);
        $processor->stop();
    }

    public function afterSuite()
    {
        if(!$this->canCollectCoverage){
            return;
        }
        $processor = $this->processor;
        $consoleIO = $this->consoleIO;
        $dispatcher = $this->dispatcher;
        $event = new CoverageEvent($processor, $consoleIO);

        $dispatcher->dispatch($event, CoverageEvent::REPORT);
    }
}