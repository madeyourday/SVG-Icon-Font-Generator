<?php
/*
 * Copyright MADE/YOUR/DAY <mail@madeyourday.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\SVG;

use Symfony\Component\Console\Application;
use MadeYourDay\SVG\IconFontGeneratorCLI\CreateFontCommand;
use MadeYourDay\SVG\IconFontGeneratorCLI\CreateFilesCommand;
use MadeYourDay\SVG\IconFontGeneratorCLI\CreateInfoCommand;
use MadeYourDay\SVG\IconFontGeneratorCLI\CreateCssCommand;

/**
 * SVG Icon Font Generator Command Line Application
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class IconFontGeneratorCLI extends Application{

	/**
	 * constructor, sets up application information and commands
	 */
	public function __construct(){

		parent::__construct('SVG Icon Font Generator', '0.1.3');

		$this->add(new CreateFontCommand);
		$this->add(new CreateFilesCommand);
		$this->add(new CreateInfoCommand);
		$this->add(new CreateCssCommand);

	}

}
