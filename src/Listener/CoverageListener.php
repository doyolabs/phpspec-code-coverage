<?php


namespace Doyo\PhpSpec\CodeCoverage\Listener;


use Doyo\Bridge\CodeCoverage\CodeCoverage;
use Doyo\Bridge\CodeCoverage\TestCase;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageListener implements EventSubscriberInterface
{
    /**
     * @var CodeCoverage
     */
    private $coverage;

    /**
     * CoverageListener constructor.
     *
     * @param CodeCoverage $coverage
     */
    public function __construct(CodeCoverage $coverage)
    {
        $this->coverage = $coverage;
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
        $example = $suiteEvent->getExample();
        $name = strtr('%spec%:%example%', [
            '%spec%'    => $example->getSpecification()->getTitle(),
            '%example%' => $example->getTitle()
        ]);
        $testCase = new TestCase($name);

        $this->coverage->start($testCase);
    }

    public function afterExample(ExampleEvent $exampleEvent)
    {
        $result = $exampleEvent->getResult();
        $map = [
            ExampleEvent::PASSED => TestCase::RESULT_PASSED,
            ExampleEvent::SKIPPED => TestCase::RESULT_SKIPPED,
            ExampleEvent::FAILED => TestCase::RESULT_FAILED,
            ExampleEvent::BROKEN => TestCase::RESULT_ERROR,
            ExampleEvent::PENDING => TestCase::RESULT_SKIPPED,
        ];

        $result = $map[$result];
        $this->coverage->setResult($result);
        $this->coverage->stop();
    }

    public function afterSuite()
    {
        $this->coverage->complete();
    }
}