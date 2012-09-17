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
 * create-css command
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class CreateCssCommand extends Command{

	/**
	 * configures the create-css command
	 *
	 * @return void
	 */
	protected function configure(){
		$this
			->setName('create-css')
			->setDescription('Creates a CSS file with icon classes from a SVG font')
			->addArgument('font-file', InputArgument::REQUIRED, 'path to the SVG font file')
			->addArgument('output-file', InputArgument::REQUIRED, 'path where the CSS file should be saved')
		;
	}

	/**
	 * creates a CSS file with icon classes from a SVG font
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

		$outputFile = $input->getArgument('output-file');

		$generator = new IconFontGenerator;

		$output->writeln('reading font file from "'.$fontFile.'" ...');
		$generator->generateFromFont(new Font(array(), file_get_contents($fontFile)));

		$output->writeln('writing CSS file to "'.$outputFile.'" ...');
		file_put_contents($outputFile, $generator->getCss());

		$output->getFormatter()->setStyle('success', new OutputFormatterStyle(null, null, array('bold', 'reverse')));

		$output->writeln('<success>created CSS file successfully</success>');

	}

}
