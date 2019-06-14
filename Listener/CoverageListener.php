<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\PhpSpec\CodeCoverage\Listener;

use Doyo\Bridge\CodeCoverage\CodeCoverageInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageListener implements EventSubscriberInterface
{
    /**
     * @var CodeCoverageInterface
     */
    private $coverage;

    /**
     * CoverageListener constructor.
     *
     * @param CodeCoverageInterface $coverage
     */
    public function __construct(CodeCoverageInterface $coverage)
    {
        $this->coverage = $coverage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'beforeExample' => ['beforeExample', -10],
            'afterExample'  => ['afterExample', -10],
            'afterSuite'    => ['afterSuite', -10],
        ];
    }

    public function beforeExample(ExampleEvent $exampleEvent)
    {
        $example = $exampleEvent->getExample();
        $name    = strtr('%spec%:%example%', [
            '%spec%'    => $example->getSpecification()->getTitle(),
            '%example%' => $example->getTitle(),
        ]);
        $testCase = new TestCase($name);

        $this->coverage->start($testCase);
    }

    public function afterExample(ExampleEvent $exampleEvent)
    {
        $result = $exampleEvent->getResult();
        $map    = [
            ExampleEvent::PASSED  => TestCase::RESULT_PASSED,
            ExampleEvent::SKIPPED => TestCase::RESULT_SKIPPED,
            ExampleEvent::FAILED  => TestCase::RESULT_FAILED,
            ExampleEvent::BROKEN  => TestCase::RESULT_ERROR,
            ExampleEvent::PENDING => TestCase::RESULT_SKIPPED,
        ];

        $result = $map[$result];
        $this->coverage->setResult($result);
        $this->coverage->stop();
    }

    public function afterSuite()
    {
        $this->coverage->complete();
    }
}
