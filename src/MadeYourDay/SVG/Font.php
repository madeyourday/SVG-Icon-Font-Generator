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

	protected $xmlDocument;

	protected $options = array(
		'id'           => 'SVG Font',
		'units-per-em' => 512,
		'horiz-adv-x'  => 512,
		'ascent'       => 480,
		'descent'      => -32,
		'x-height'     => 240,
		'cap-height'   => 480,
	);

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

	public function getOptions(){
		return $this->options;
	}

	public function getXML(){
		return $this->xmlDocument->asXML();
	}

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
