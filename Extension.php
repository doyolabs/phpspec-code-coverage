<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\PhpSpec\CodeCoverage;

use Doyo\Bridge\CodeCoverage\ContainerFactory;
use Doyo\PhpSpec\CodeCoverage\Listener\CoverageListener;
use PhpSpec\Extension as ExtensionInterface;
use PhpSpec\ServiceContainer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class Extension implements ExtensionInterface
{
    public function load(ServiceContainer $container, array $params)
    {
        $this->addCoverageOptions($container);

        /** @var InputInterface $input */
        $input = $container->get('console.input');

        if (false === $input->hasParameterOption(['--coverage'], false)) {
            return;
        }

        $container->define('doyo.coverage.container', function ($container) use ($params) {
            $coverageContainer = (new ContainerFactory($params, true))->getContainer();
            $input = $container->get('console.input');
            $output = $container->get('console.output');
            $coverageContainer->set('console.input', $input);
            $coverageContainer->set('console.output', $output);

            return $coverageContainer;
        });

        $container->define('doyo.coverage.listener', function ($container) {
            $coverageContainer = $container->get('doyo.coverage.container');
            $coverage = $coverageContainer->get('coverage');

            return new CoverageListener($coverage);
        }, ['event_dispatcher.listeners']);
    }

    public function addCoverageOptions(ServiceContainer $container)
    {
        /** @var \PhpSpec\Console\Command\RunCommand $command */
        $command = $container->get('console.commands.run');
        $command->addOption(
            'coverage',
            null,
            InputOption::VALUE_NONE,
            'Run phpspec with code coverage'
        );
    }
}
