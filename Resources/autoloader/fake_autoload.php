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
class FakeLoader
{
    public function getPrefixes()
    {
        return [
            'Andromeda\\N4S4Arm\\' => [
                __DIR__.'/../src/',
            ],
        ];
    }

    public function getPrefixesPsr4()
    {
        return [
            'MilkyWay\\OrionCygnusArm\\' => [
                __DIR__.'/../src/',
            ],
        ];
    }
}
