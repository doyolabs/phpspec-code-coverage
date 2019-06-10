<?php


namespace Doyo\PhpSpec\CodeCoverage\Event;


use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\Symfony\Bridge\EventDispatcher\Event;
use PhpSpec\Console\ConsoleIO;

class CoverageEvent extends Event
{
    const REPORT = 'doyo.coverage.report';

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ConsoleIO
     */
    private $consoleIO;

    /**
     * CoverageEvent constructor.
     * @param ProcessorInterface $processor
     * @param ConsoleIO $consoleIO
     */
    public function __construct(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    )
    {
        $this->processor = $processor;
        $this->consoleIO = $consoleIO;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor(): ProcessorInterface
    {
        return $this->processor;
    }

    /**
     * @return ConsoleIO
     */
    public function getConsoleIO(): ConsoleIO
    {
        return $this->consoleIO;
    }
}