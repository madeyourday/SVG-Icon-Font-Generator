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
 * create-info command
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class CreateInfoCommand extends Command{

	/**
	 * configures the create-info command
	 *
	 * @return void
	 */
	protected function configure(){
		$this
			->setName('create-info')
			->setDescription('Creates a HTML info page out of a SVG font')
			->addArgument('font-file', InputArgument::REQUIRED, 'path to the SVG font file')
			->addArgument('output-file', InputArgument::REQUIRED, 'path where the HTML page should be saved')
			->addOption('as-list', 'l', InputOption::VALUE_NONE, 'if set the generated HTML file will be a simple unordered list instead of a full HTML document')
		;
	}

	/**
	 * creates a HTML info page out of a SVG font
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

		$output->writeln('writing HTML file to "'.$outputFile.'" ...');
		if ($input->getOption('as-list')) {
			$html = $this->getHTMLListFromGenerator($generator, basename($fontFile));
		}
		else {
			$html = $this->getHTMLFromGenerator($generator, basename($fontFile));
		}
		file_put_contents($outputFile, $html);

		$output->getFormatter()->setStyle('success', new OutputFormatterStyle(null, null, array('bold', 'reverse')));

		$output->writeln('<success>created HTML info page successfully</success>');

	}

	/**
	 * creates the HTML for the info page
	 *
	 * @param  IconFontGenerator $generator icon font generator
	 * @param  string            $fontFile  font file name
	 * @return string                       HTML for the info page
	 */
	protected function getHTMLFromGenerator(IconFontGenerator $generator, $fontFile){

		$fontOptions = $generator->getFont()->getOptions();

		$html = '<!doctype html>
			<html>
			<head>
			<title>'.htmlspecialchars($fontOptions['id']).'</title>
			<style>
				@font-face {
					font-family: "'.$fontOptions['id'].'";
					src: url("'.$fontFile.'") format("svg");
					font-weight: normal;
					font-style: normal;
				}
				body {
					font-family: sans-serif;
					color: #444;
					line-height: 1.5;
					font-size: 16px;
					padding: 20px;
				}
				* {
					-moz-box-sizing: border-box;
					-webkit-box-sizing: border-box;
					box-sizing: border-box;
					margin: 0;
					paddin: 0;
				}
				.glyph{
					display: inline-block;
					width: 120px;
					margin: 10px;
					text-align: center;
					vertical-align: top;
					background: #eee;
					border-radius: 10px;
					box-shadow: 1px 1px 5px rgba(0, 0, 0, .2);
				}
				.glyph-icon{
					padding: 10px;
					display: block;
					font-family: "'.$fontOptions['id'].'";
					font-size: 64px;
					line-height: 1;
				}
				.glyph-icon:before{
					content: attr(data-icon);
				}
				.class-name{
					font-size: 12px;
				}
				.glyph > input{
					display: block;
					width: 100px;
					margin: 5px auto;
					text-align: center;
					font-size: 12px;
					cursor: text;
				}
				.glyph > input.icon-input{
					font-family: "'.$fontOptions['id'].'";
					font-size: 16px;
					margin-bottom: 10px;
				}
			</style>
			</head>
			<body>
			<section id="glyphs">';

		$glyphNames = $generator->getGlyphNames();
		asort($glyphNames);

		foreach($glyphNames as $unicode => $glyph){
			$html .= '<div class="glyph">
				<div class="glyph-icon" data-icon="&#x'.$unicode.';"></div>
				<div class="class-name">icon-'.$glyph.'</div>
				<input type="text" readonly="readonly" value="&amp;#x'.$unicode.';" />
				<input type="text" readonly="readonly" value="\\'.$unicode.'" />
				<input type="text" readonly="readonly" value="&#x'.$unicode.';" class="icon-input" />
			</div>';
		}

		$html .= '</section>
			</body>
		</html>';

		return $html;

	}

	/**
	 * creates a HTML list
	 *
	 * @param  IconFontGenerator $generator icon font generator
	 * @param  string            $fontFile  font file name
	 * @return string                       HTML unordered list
	 */
	protected function getHTMLListFromGenerator(IconFontGenerator $generator, $fontFile){

		$fontOptions = $generator->getFont()->getOptions();

		$html = '<ul>';

		$glyphNames = $generator->getGlyphNames();
		asort($glyphNames);

		foreach($glyphNames as $unicode => $glyph){
			$html .= "\n\t" .
				'<li data-icon="&#x'.$unicode.';" title="' .
				htmlspecialchars($glyph) . '">' .
				htmlspecialchars($glyph) . '</li>';
		}

		$html .= "\n" . '</ul>' . "\n";

		return $html;

	}

}
