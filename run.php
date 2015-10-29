<?php
/*
 * Copyright MADE/YOUR/DAY <mail@madeyourday.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/vendor/autoload.php';

$cli = new MadeYourDay\SVG\IconFontGeneratorCLI;
$cli->run();
