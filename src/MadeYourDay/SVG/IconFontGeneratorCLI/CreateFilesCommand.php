<?php
/*
 * Copyright MADE/YOUR/DAY <mail@madeyourday.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\SVG\IconFontGeneratorCLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use MadeYourDay\SVG\IconFontGenerator;
use MadeYourDay\SVG\Font;

/**
 * create-files command
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class CreateFilesCommand extends Command{

	/**
	 * configures the create-files command
	 *
	 * @return void
	 */
	protected function configure(){
		$this
			->setName('create-files')
			->setDescription('Creates single SVG files out of a SVG font and saves them to the specified directory')
			->addArgument('font-file', InputArgument::REQUIRED, 'path to the SVG font file')
			->addArgument('output-directory', InputArgument::REQUIRED, 'path to the output directory')
		;
	}

	/**
	 * creates single SVG files out of a SVG font and saves them to the specified directory
	 *
	 * @param  InputInterface  $input  input
	 * @param  OutputInterface $output output
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output){

		$fontFile = realpath($input->getArgument('font-file'));
		if($fontFile === false || !file_exists($fontFile)){
			throw new \InvalidArgumentException('"'.$input->getArgument('font-file').'" does not exist');
		}

		$outputDirectory = realpath($input->getArgument('output-directory'));
		if($outputDirectory === false || !file_exists($outputDirectory) || !is_dir($outputDirectory)){
			throw new \InvalidArgumentException('"'.$input->getArgument('output-directory').'" is no directory');
		}

		$generator = new IconFontGenerator;

		$output->writeln('reading font file from "'.$fontFile.'" ...');
		$generator->generateFromFont(new Font(array(), file_get_contents($fontFile)));

		$output->writeln('writing SVG files to "'.$outputDirectory.'" ...');
		$generator->saveGlyphsToDir($outputDirectory);

		$output->getFormatter()->setStyle('success', new OutputFormatterStyle(null, null, array('bold', 'reverse')));

		$output->writeln('<success>created SVG files successfully</success>');

	}

}
