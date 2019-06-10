<?php

namespace spec\Doyo\PhpSpec\CodeCoverage\Listener;

use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\PhpSpec\CodeCoverage\Event\CoverageEvent;
use Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener;
use Doyo\Symfony\Bridge\EventDispatcher\EventDispatcher;
use Doyo\Symfony\Bridge\EventDispatcher\EventDispatcherInterface;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Loader\Node\SpecificationNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageListenerSpec extends ObjectBehavior
{
    function let(
        ProcessorInterface $processor,
        EventDispatcher $dispatcher,
        ConsoleIO $consoleIO
    )
    {
        $this->beConstructedWith($dispatcher, $processor, $consoleIO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageListener::class);
    }

    function it_should_subscribe_to_phpspec_events()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey('beforeExample');
        $this->getSubscribedEvents()->shouldHaveKey('afterExample');
        $this->getSubscribedEvents()->shouldHaveKey('afterSuite');
    }

    private function decorateBeforeExampleEvent(
        ExampleEvent $exampleEvent,
        ExampleNode $example,
        SpecificationNode $specification,
        \ReflectionFunctionAbstract $reflection
    )
    {
        $exampleEvent
            ->getExample()
            ->shouldBeCalledOnce()
            ->willReturn($example);

        $example->getSpecification()->willReturn($specification);
        $specification->getTitle()->willReturn('title');

        $example->getFunctionReflection()->willReturn($reflection);
        $reflection->getName()->willReturn('function');
    }

    function it_should_handle_before_example_event(
        ExampleEvent $exampleEvent,
        ExampleNode $example,
        SpecificationNode $specification,
        \ReflectionFunctionAbstract $reflection,
        ProcessorInterface $processor
    )
    {
        $this->decorateBeforeExampleEvent($exampleEvent, $example,$specification,$reflection);

        $processor->setCurrentTestCase(Argument::type(TestCase::class))
            ->shouldBeCalledOnce();
        $processor->start(Argument::type(TestCase::class))
            ->shouldBeCalledOnce();

        $this->beforeExample($exampleEvent);
    }

    public function it_should_handle_after_example_event(
        ExampleEvent $exampleEvent,
        ProcessorInterface $processor,
        TestCase $testCase
    )
    {
        $exampleEvent
            ->getResult()
            ->shouldBeCalledOnce()
            ->willReturn(0);

        $testCase->setResult(0)
            ->shouldBeCalledOnce();

        $processor
            ->getCurrentTestCase()
            ->willReturn($testCase)
        ;
        $processor->stop(Argument::cetera())
            ->shouldBeCalledOnce();

        $processor->addTestCase($testCase)
            ->shouldBeCalledOnce();

        $this->afterExample($exampleEvent);
    }

    function it_should_handle_after_suite_event(
        SuiteEvent $suiteEvent,
        EventDispatcher $dispatcher
    )
    {
        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::REPORT)
            ->shouldBeCalledOnce();

        $this->afterSuite($suiteEvent);
    }

}
