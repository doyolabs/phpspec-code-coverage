<?php


namespace Doyo\PhpSpec\CodeCoverage;

use Doyo\Bridge\CodeCoverage\Processor as BaseProcessor;
use Doyo\Bridge\CodeCoverage\TestCase;

class Processor extends BaseProcessor implements ProcessorInterface
{
    private $testCase;

    public function setCurrentTestCase(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function getCurrentTestCase(): TestCase
    {
        return $this->testCase;
    }
}