<?php

$pharPath = __DIR__.'/svg-icon-font-generator.phar';

if(file_exists($pharPath)){
	unlink($pharPath);
}

$phar = new Phar($pharPath);

$phar->setStub($phar->createDefaultStub('run.php'));

$files = new AppendIterator;
$files->append(new ArrayIterator(array(
	'run.php' => __DIR__.'/run.php',
)));
$files->append(new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator(__DIR__.'/src')
));
$files->append(new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator(__DIR__.'/vendor')
));

$phar->buildFromIterator($files, __DIR__);
