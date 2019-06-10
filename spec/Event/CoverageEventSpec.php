<?php

namespace spec\Doyo\PhpSpec\CodeCoverage\Event;

use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\PhpSpec\CodeCoverage\Event\CoverageEvent;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CoverageEventSpec extends ObjectBehavior
{
    function let(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    )
    {
        $this->beConstructedWith($processor, $consoleIO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageEvent::class);
    }

    function its_property_should_be_mutable(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    )
    {
        $this->getConsoleIO()->shouldReturn($consoleIO);
        $this->getProcessor()->shouldReturn($processor);
    }
}
