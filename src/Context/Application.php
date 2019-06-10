<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doyo\PhpSpec\CodeCoverage\Context;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Console\ContainerAssembler;
use PhpSpec\Exception\Configuration\InvalidConfigurationException;
use PhpSpec\Matcher\Matcher;
use PhpSpec\ServiceContainer;
use Symfony\Component\Console\Application as BaseApplication;
use PhpSpec\ServiceContainer\IndexedServiceContainer;
use PhpSpec\Extension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * The command line application entry point
 *
 * @internal
 */
final class Application extends BaseApplication
{
    /**
     * @var IndexedServiceContainer
     */
    private $container;

    public function __construct($config)
    {
        $container = new IndexedServiceContainer();
        $container->set('console.commands.run', new Command());
        $container->set('console.input', new StringInput('run --coverage'));
        $container->set('console.output', new StreamOutput(fopen('php://memory','+w')));
        $container->set('cli.input', new StringInput('run --coverage'));
        $container->set('cli.output', new StreamOutput(fopen('php://memory','+w')));
        $container->set('console.helper_set', $this->getDefaultHelperSet());
        $this->loadConfig($container, $config);

        $assembler = new ContainerAssembler();
        $assembler->build($container);
        $container->set('console.input', new StringInput('run --coverage'));
        $this->container = $container;
    }

    /**
     * Gets the default helper set with the helpers that should always be available.
     *
     * @return HelperSet A HelperSet instance
     */
    protected function getDefaultHelperSet()
    {
        return new HelperSet([
            new FormatterHelper(),
            new DebugFormatterHelper(),
            new ProcessHelper(),
            new QuestionHelper(),
        ]);
    }

    /**
     * @throws \RuntimeException
     */
    protected function loadConfig(IndexedServiceContainer $container, array $config)
    {
        $this->populateContainerParameters($container, $config);

        foreach ($config as $key => $val) {
            if ('extensions' === $key && \is_array($val)) {
                foreach ($val as $class => $extensionConfig) {
                    $this->loadExtension($container, $class, $extensionConfig ?: []);
                }
            }
            elseif ('matchers' === $key && \is_array($val)) {
                $this->registerCustomMatchers($container, $val);
            }
        }
    }

    /**
     * @return IndexedServiceContainer
     */
    public function getContainer(): IndexedServiceContainer
    {
        return $this->container;
    }

    private function registerCustomMatchers(IndexedServiceContainer $container, array $matchersClassnames)
    {
        foreach ($matchersClassnames as $class) {
            $this->ensureIsValidMatcherClass($class);

            $container->define(sprintf('matchers.%s', $class), function () use ($class) {
                return new $class();
            }, ['matchers']);
        }
    }

    private function ensureIsValidMatcherClass(string $class)
    {
        if (!class_exists($class)) {
            throw new InvalidConfigurationException(sprintf('Custom matcher %s does not exist.', $class));
        }

        if (!is_subclass_of($class, Matcher::class)) {
            throw new InvalidConfigurationException(sprintf(
                'Custom matcher %s must implement %s interface, but it does not.',
                $class,
                Matcher::class
            ));
        }
    }

    private function loadExtension(ServiceContainer $container, string $extensionClass, $config)
    {
        if (!class_exists($extensionClass)) {
            throw new InvalidConfigurationException(sprintf('Extension class `%s` does not exist.', $extensionClass));
        }

        if (!\is_array($config)) {
            throw new InvalidConfigurationException('Extension configuration must be an array or null.');
        }

        if (!is_a($extensionClass, Extension::class, true)) {
            throw new InvalidConfigurationException(sprintf('Extension class `%s` must implement Extension interface', $extensionClass));
        }

        (new $extensionClass)->load($container, $config);
    }

    private function populateContainerParameters(IndexedServiceContainer $container, array $config)
    {
        foreach ($config as $key => $val) {
            if ('extensions' !== $key && 'matchers' !== $key) {
                $container->setParam($key, $val);
            }
        }
    }
}
