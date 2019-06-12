<?php

namespace spec\Doyo\PhpSpec\CodeCoverage\Listener;

use Doyo\Bridge\CodeCoverage\CodeCoverage;
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Loader\Node\SpecificationNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageListenerSpec extends ObjectBehavior
{
    function let(
        CodeCoverage $coverage
    )
    {
        $this->beConstructedWith($coverage);
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
        $specification->getTitle()->willReturn('class');
        $example->getTitle()->willReturn('title');
    }

    function it_should_handle_before_example_event(
        ExampleEvent $exampleEvent,
        ExampleNode $example,
        SpecificationNode $specification,
        \ReflectionFunctionAbstract $reflection,
        CodeCoverage $coverage
    )
    {
        $this->decorateBeforeExampleEvent($exampleEvent, $example,$specification,$reflection);

        $coverage->start(Argument::type(TestCase::class))
            ->shouldBeCalledOnce();

        $this->beforeExample($exampleEvent);
    }

    public function it_should_handle_after_example_event(
        ExampleEvent $exampleEvent,
        CodeCoverage $coverage
    )
    {
        $exampleEvent
            ->getResult()
            ->shouldBeCalledOnce()
            ->willReturn(0);

        $coverage->setResult(0)
            ->shouldBeCalledOnce();

        $coverage->stop()->shouldBeCalledOnce();
        $this->afterExample($exampleEvent);
    }

    function it_should_handle_after_suite_event(
        CodeCoverage $coverage
    )
    {
        $coverage->complete()->shouldBeCalledOnce();
        $this->afterSuite();
    }

}
