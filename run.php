<?php
/*
 * Copyright MADE/YOUR/DAY <mail@madeyourday.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader;

$loader->registerNamespaces(array(
	'Symfony\\Component\\ClassLoader' => __DIR__.'/vendor/symfony/class-loader',
	'Symfony\\Component\\Console' => __DIR__.'/vendor/symfony/console',
	'MadeYourDay' => __DIR__.'/src',
));

$loader->register();

$cli = new MadeYourDay\SVG\IconFontGeneratorCLI;
$cli->run();
