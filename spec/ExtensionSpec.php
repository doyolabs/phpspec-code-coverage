<?php

namespace spec\Doyo\PhpSpec\CodeCoverage;

use Doyo\PhpSpec\CodeCoverage\Extension;
use PhpSpec\ObjectBehavior;
use PhpSpec\ServiceContainer;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class ExtensionSpec extends ObjectBehavior
{
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

        $container->has($serviceId)
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $container->get($serviceId)
            ->shouldBeCalledOnce()
            ->willReturn($command);

        $command
            ->addOption('coverage', null, InputOption::VALUE_NONE, Argument::any())
            ->shouldBeCalledOnce()
        ;

        $this->load($container, []);
    }
}
