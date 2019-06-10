<?php


namespace Doyo\PhpSpec\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface as BaseProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;

interface ProcessorInterface extends BaseProcessorInterface
{
    /**
     * Set current TestCase
     * @param TestCase $testCase
     * @return void
     */
    public function setCurrentTestCase(TestCase $testCase);

    /**
     * Get current TestCase
     * @return TestCase
     */
    public function getCurrentTestCase(): TestCase;
}