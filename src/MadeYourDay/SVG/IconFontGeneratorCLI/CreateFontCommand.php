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

/**
 * create-font command
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class CreateFontCommand extends Command{

	/**
	 * configures the create-font command
	 *
	 * @return void
	 */
	protected function configure(){
		$this
			->setName('create-font')
			->setDescription('Creates a SVG Font out of SGV files from a directory')
			->addArgument('directory', InputArgument::REQUIRED, 'path to directory containging SVG files')
			->addArgument('output-file', InputArgument::REQUIRED, 'path to the output file')
			->addOption('rename-files', null, InputOption::VALUE_NONE, 'if set, files without mapping information will be renamed to include the mapping information (e.g. my-icon.svg renamed to my-icon-xe001.svg)')
		;
	}

	/**
	 * creates a SVG Font out of SGV files from a directory
	 *
	 * @param  InputInterface  $input  input
	 * @param  OutputInterface $output output
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output){

		$directory = $input->getArgument('directory');
		$outputFile = $input->getArgument('output-file');

		$generator = new IconFontGenerator;

		$output->writeln('reading files from "'.$directory.'" ...');
		$generator->generateFromDir($directory, array(), $input->getOption('rename-files'));

		$output->writeln('writing font to "'.$outputFile.'" ...');
		file_put_contents($outputFile, $generator->getFont()->getXML());

		$output->getFormatter()->setStyle('success', new OutputFormatterStyle(null, null, array('bold', 'reverse')));

		$output->writeln('<success>created '.$outputFile.' successfully</success>');

	}

}
