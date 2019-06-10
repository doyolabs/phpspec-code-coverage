<?php


namespace Doyo\PhpSpec\CodeCoverage;

use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;
use Symfony\Component\Console\Input\InputOption;

class Extension implements BaseExtension
{
    public function load(ServiceContainer $container, array $params)
    {
        $this->addCoverageOptions($container);
    }

    private function addCoverageOptions(ServiceContainer $container)
    {
        $id = 'console.commands.run';
        if(!$container->has($id)){
            return;
        }

        /* @var \PhpSpec\Console\Command\RunCommand $command */

        $command = $container->get($id);
        $command->addOption(
            'coverage',
            null,
            InputOption::VALUE_NONE,
            'Run phpspec with code coverage'
        );
    }
}