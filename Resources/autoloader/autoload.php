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
spl_autoload_register(function ($classname) {
    $classname = __DIR__.'/../src/'.str_replace('\\', '/', trim($classname, '\\')).'.php';
    if (file_exists($classname)) {
        include $classname;
    }
});
if (class_exists('FakeLoader')) {
    return new FakeLoader();
}
