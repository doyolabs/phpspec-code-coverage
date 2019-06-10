<?php


namespace Doyo\PhpSpec\CodeCoverage;

use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;

class Extension implements BaseExtension
{
    public function load(ServiceContainer $container, array $params)
    {
        $this->configureCli($container, $params);
        return;
    }

    private function configureCli(ServiceContainer $container)
    {
        $command = $container->get('run');
    }
}