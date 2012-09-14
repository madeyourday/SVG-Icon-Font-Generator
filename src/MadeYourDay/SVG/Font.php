<?php
/*
 * Copyright MADE/YOUR/DAY <mail@madeyourday.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\SVG;

use SimpleXMLElement;

/**
 * SVG Font
 *
 * @author ausi <martin@madeyourday.co>
 */
class Font{

	/**
	 * @var SimpleXMLElement XML document
	 */
	protected $xmlDocument;

	/**
	 * @var array font options
	 */
	protected $options = array(
		'id'           => 'SVG Font',
		'units-per-em' => 512,
		'horiz-adv-x'  => 512,
		'ascent'       => 480,
		'descent'      => -32,
		'x-height'     => 240,
		'cap-height'   => 480,
	);

	/**
	 * create a empty font or from a SVG XML string
	 *
	 * @param array  $options   font options
	 * @param string $svgString SVG XML string
	 */
	public function __construct($options = array(), $svgString = null){

		$newFont = false;
		if($svgString === null){
			$newFont = true;
			$svgString = '<?xml version="1.0" standalone="no"?>
				<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd" >
				<svg xmlns="http://www.w3.org/2000/svg"></svg>';
		}

		$this->xmlDocument = new SimpleXMLElement($svgString);

		if(!count($this->xmlDocument->defs)){
			$this->xmlDocument->addChild('defs');
		}
		if(!count($this->xmlDocument->defs[0]->font)){
			$this->xmlDocument->defs[0]->addChild('font');
		}
		if(!count($this->xmlDocument->defs[0]->font[0]->{'font-face'})){
			$this->xmlDocument->defs[0]->font[0]->addChild('font-face');
		}
		if(!count($this->xmlDocument->defs[0]->font[0]->{'missing-glyph'})){
			$this->xmlDocument->defs[0]->font[0]->addChild('missing-glyph');
		}

		if(!$newFont){
			$options = array_merge($this->getOptionsFromXML(), $options);
		}

		$this->setOptions($options);

	}

	/**
	 * set font optinos
	 *
	 * @param array $options font options
	 */
	public function setOptions($options = array()){

		$this->options = array_merge($this->options, $options);

		$this->xmlDocument->defs[0]->font[0]['id'] = $this->options['id'];
		$this->xmlDocument->defs[0]->font[0]['horiz-adv-x'] = $this->options['horiz-adv-x'];
		$this->xmlDocument->defs[0]->font[0]->{'font-face'}[0]['units-per-em'] = $this->options['units-per-em'];
		$this->xmlDocument->defs[0]->font[0]->{'font-face'}[0]['ascent'] = $this->options['ascent'];
		$this->xmlDocument->defs[0]->font[0]->{'font-face'}[0]['descent'] = $this->options['descent'];
		$this->xmlDocument->defs[0]->font[0]->{'font-face'}[0]['x-height'] = $this->options['x-height'];
		$this->xmlDocument->defs[0]->font[0]->{'font-face'}[0]['cap-height'] = $this->options['cap-height'];
		$this->xmlDocument->defs[0]->font[0]->{'missing-glyph'}[0]['horiz-adv-x'] = $this->options['horiz-adv-x'];

	}

	/**
	 * returns font options stored in the XML document
	 *
	 * @return array font options
	 */
	protected function getOptionsFromXML(){

		$options = array();

		foreach(array('id', 'horiz-adv-x') as $key){
			if(isset($this->xmlDocument->defs[0]->font[0][$key])){
				$options[$key] = (string)$this->xmlDocument->defs[0]->font[0][$key];
			}
		}
		foreach(array('units-per-em', 'ascent', 'descent', 'x-height', 'cap-height') as $key){
			if(isset($this->xmlDocument->defs[0]->font[0]->{'font-face'}[0][$key])){
				$options[$key] = (string)$this->xmlDocument->defs[0]->font[0]->{'font-face'}[0][$key];
			}
		}

		return $options;

	}

	/**
	 * get font options
	 *
	 * @return array font options
	 */
	public function getOptions(){
		return $this->options;
	}

	/**
	 * get XML string
	 *
	 * @return string XML SVG string
	 */
	public function getXML(){
		return $this->xmlDocument->asXML();
	}

	/**
	 * add a glyph to the font
	 *
	 * @param string $char  character of the glyph
	 * @param string $path  SVG path definition
	 * @param string $name  name of the glyph
	 * @param float  $width glyph width (horiz-adv-x)
	 */
	public function addGlyph($char, $path, $name = null, $width = null){

		$glyph = $this->xmlDocument->defs[0]->font[0]->addChild('glyph');
		$glyph->addAttribute('unicode', $char);
		if($name !== null){
			$glyph->addAttribute('glyph-name', $name);
		}
		if($width !== null){
			$glyph->addAttribute('horiz-adv-x', $width);
		}
		$glyph->addAttribute('d', $path);

	}

	/**
	 * get all glyphs
	 *
	 * @return array set of glyph arrays containing char, path, name and width (name and with are optional)
	 */
	public function getGlyphs(){

		if(
			!isset($this->xmlDocument->defs[0]->font[0]->glyph) ||
			!count($this->xmlDocument->defs[0]->font[0]->glyph)
		){
			return array();
		}
		$glyphs = array();
		foreach($this->xmlDocument->defs[0]->font[0]->glyph as $xmlGlyph){
			if(isset($xmlGlyph['unicode']) && isset($xmlGlyph['d'])){
				$glyph = array(
					'char' => (string)$xmlGlyph['unicode'],
					'path' => (string)$xmlGlyph['d'],
				);
				if(isset($xmlGlyph['glyph-name'])){
					$glyph['name'] = (string)$xmlGlyph['glyph-name'];
				}
				if(isset($xmlGlyph['horiz-adv-x'])){
					$glyph['width'] = (string)$xmlGlyph['horiz-adv-x'];
				}
				$glyphs[] = $glyph;
			}
		}
		return $glyphs;
	}

}
