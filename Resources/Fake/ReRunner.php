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

namespace Fake;

use PhpSpec\Process\ReRunner as BaseReRunner;

class ReRunner implements BaseReRunner
{
    private $hasBeenReRun = false;

    /**
     * @return bool
     */
    public function isSupported()
    {
        return true;
    }

    public function reRunSuite(): void
    {
        $this->hasBeenReRun = true;
    }

    public function hasBeenReRun()
    {
        return $this->hasBeenReRun;
    }
}
