<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spec\Doyo\PhpSpec\CodeCoverage\Listener;

use Doyo\Bridge\CodeCoverage\CodeCoverageInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Loader\Node\SpecificationNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class CoverageListenerSpec.
 *
 * @covers \Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener
 */
class CoverageListenerSpec extends ObjectBehavior
{
    public function let(CodeCoverageInterface $coverage)
    {
        $this->beConstructedWith($coverage);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CoverageListener::class);
    }

    public function its_beforeExample_should_start_coverage(
        ExampleEvent $exampleEvent,
        SpecificationNode $specification,
        ExampleNode $example,
        CodeCoverageInterface $coverage
    ) {
        $exampleEvent->getExample()->willReturn($example);
        $example->getSpecification()->willReturn($specification);
        $specification->getTitle()->willReturn('spec')->shouldBeCalledOnce();
        $example->getTitle()->willReturn('title')->shouldBeCalledOnce();

        $coverage->start(Argument::type(TestCase::class))
            ->shouldBeCalled();
        $this->beforeExample($exampleEvent);
    }

    public function its_afterExample_should_stop_coverage(
        ExampleEvent $exampleEvent,
        CodeCoverageInterface $coverage
    ) {
        $exampleEvent->getResult()->shouldBeCalled()->willReturn(0);

        $coverage->setResult(0)->shouldBeCalled();
        $coverage->stop()->shouldBeCalled();

        $this->afterExample($exampleEvent);
    }

    public function its_afterSuite_should_complete_coverage(
        CodeCoverageInterface $coverage
    ) {
        $coverage->complete()->shouldBeCalledOnce();
        $this->afterSuite();
    }
}
