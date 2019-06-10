<?php


namespace Doyo\PhpSpec\CodeCoverage\Context;


use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PhpSpec\Console\Command\RunCommand;
use PhpSpec\Exception\Configuration\InvalidConfigurationException;
use PhpSpec\Extension;
use PhpSpec\Matcher\Matcher;
use PhpSpec\ServiceContainer;
use PhpSpec\ServiceContainer\IndexedServiceContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class ServiceContext implements Context
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @Then service :name should exist
     *
     * @param string $name
     */
    public function serviceShouldExist($name)
    {
        $container = $this->app->getContainer();
        Assert::true($container->has($name));
        Assert::true(is_object($container->get($name)));
    }

    /**
     * @Given I configure phpspec with:
     *
     * @param PyStringNode $node
     */
    public function iConfigurePhpSpecWith(PyStringNode $node)
    {
        $config = Yaml::parse($node->getRaw());
        $app = new Application($config);
        $this->app = $app;
    }
}