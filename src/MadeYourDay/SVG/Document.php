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
 * SVG Document
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class Document{

	/**
	 * @var SimpleXMLElement XML document element
	 */
	protected $xmlDocument;

	/**
	 * @var array conversion table between several units and pixels
	 */
	protected $unitInPixels = array(
		'px' => 1,
		'pt' => 1.25,
		'mm' => 3.543307096633,
		'pc' => 15,
		'cm' => 35.43307096633,
		'in' => 90,
	);

	/**
	 * creates a Document instance from a xml string
	 *
	 * @param string $svgString XML SVG content
	 */
	public function __construct($svgString){

		$this->xmlDocument = new SimpleXMLElement($svgString);

	}

	/**
	 * creates a Document instance from a simple SVG path definition
	 *
	 * @param  string  $path   SVG path definition
	 * @param  integer $width  document width (default 512)
	 * @param  integer $height document height (default 512)
	 * @return static          Document instance
	 */
	public static function createFromPath($path, $width = 512, $height = 512){

		return new static('<?xml version="1.0" encoding="utf-8"?>'.
			'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.
			'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="'.$width.'" height="'.$height.'">'.
			'<path d="'.$path.'"/>'.
			'</svg>');

	}

	/**
	 * returns an viewbox array containing x/y offsets and with/height
	 *
	 * @return array|null array with x,y,width,height values
	 */
	public function getViewBox(){

		if(!empty($this->xmlDocument['viewBox'])){

			$viewBox = explode(' ', trim(preg_replace('([\\s,]+)', ' ', $this->xmlDocument['viewBox'])));
			if(count($viewBox) !== 4){
				return null;
			}
			return array(
				'x' => $viewBox[0]*1,
				'y' => $viewBox[1]*1,
				'width' => $viewBox[2]*1,
				'height' => $viewBox[3]*1,
			);

		}

		if(!empty($this->xmlDocument['width']) && !empty($this->xmlDocument['height'])){

			$width = trim($this->xmlDocument['width']);
			$height = trim($this->xmlDocument['height']);
			if(isset($this->unitInPixels[substr($width, -2)])){
				$width = $width*$this->unitInPixels[substr($width, -2)];
			}
			if(isset($this->unitInPixels[substr($height, -2)])){
				$height = $height*$this->unitInPixels[substr($height, -2)];
			}
			return array(
				'x' => 0,
				'y' => 0,
				'width' => $width*1,
				'height' => $height*1,
			);

		}

		return null;

	}

	/**
	 * returns one single SVG path definition for all elements in the document
	 *
	 * @param  float   $scale          a positive number how much the path should be scaled (1 means 100%)
	 * @param  integer $roundPrecision number of decimal digits to round to or null to disable rounding
	 * @param  string  $flip           'none', 'horizontal' or 'vertical' (requires a valid view box)
	 * @param  boolean $onlyFilled     ignore non filled objects
	 * @param  integer $xOffset        x offset
	 * @param  integer $yOffset        y offset
	 * @return string                  SVG path definition
	 */
	public function getPath($scale = 1, $roundPrecision = null, $flip = 'none', $onlyFilled = true, $xOffset = 0, $yOffset = 0){

		$path = $this->getPathPart($this->xmlDocument, $onlyFilled);

		if($scale !== 1 || $roundPrecision !== null || $flip !== 'none' || $xOffset !== 0 || $yOffset !== 0){
			$path = $this->transformPath($path, $scale, $roundPrecision, $flip, $xOffset / $scale, $yOffset / $scale);
		}

		return trim($path);

	}

	/**
	 * returns one single SVG path definition for all elements in the specified element
	 *
	 * @param  SimpleXMLElement $xmlElement group or svg element
	 * @param  boolean          $onlyFilled ignore non filled objects
	 * @return string                       SVG path definition
	 */
	protected function getPathPart(SimpleXMLElement $xmlElement, $onlyFilled){

		$path = '';

		if($xmlElement === null){
			$xmlElement = $this->xmlDocument;
		}

		foreach($xmlElement->children() as $child){
			$childName = $child->getName();
			if(!empty($child['transform'])){
				throw new \Exception('Transforms are currently not supported!');
			}
			if($childName === 'g'){
				$path .= ' '.$this->getPathPart($child, $onlyFilled);
			}
			else{
				if($onlyFilled && (string)$child['fill'] === 'none'){
					continue;
				}
				if($childName === 'polygon'){
					$path .= ' '.$this->getPathFromPolygon($child);
				}
				elseif($childName === 'rect'){
					$path .= ' '.$this->getPathFromRect($child);
				}
				elseif($childName === 'circle'){
					$path .= ' '.$this->getPathFromCircle($child);
				}
				elseif($childName === 'ellipse'){
					$path .= ' '.$this->getPathFromEllipse($child);
				}
				elseif($childName === 'path'){
					$pathPart = trim(preg_replace('([\\s,]+)', ' ', $child['d']));
					if(substr($pathPart, 0, 1) === 'm'){
						$pathPart = 'M'.substr($pathPart, 1);
					}
					$path .= ' '.$pathPart;
				}
			}
		}

		return trim($path);

	}

	/**
	 * transforms a SVG path definition by the given parameters
	 *
	 * @param  string  $path           SVG path definition
	 * @param  float   $scale          a positive number how much the path should be scaled (1 means 100%)
	 * @param  integer $roundPrecision number of decimal digits to round to or null to disable rounding
	 * @param  string  $flip           'none', 'horizontal' or 'vertical' (requires a valid view box)
	 * @param  integer $xOffset        x offset
	 * @param  integer $yOffset        y offset
	 * @return string                  SVG path definition
	 */
	protected function transformPath($path, $scale, $roundPrecision, $flip, $xOffset, $yOffset){

		if($flip === 'horizontal' || $flip === 'vertical'){
			$viewBox = $this->getViewBox();
		}

		return preg_replace_callback('([m,l,h,v,c,s,q,t,a,z]\\s*(?:\\s*-?\\d+(?:\\.\\d+)?)*)i', function($maches) use ($scale, $roundPrecision, $flip, $xOffset, $yOffset, $viewBox){

			$command = substr($maches[0], 0, 1);
			$absoluteCommand = strtoupper($command) === $command;
			$xyCommand = in_array(strtolower($command), array('m','l','c','s','q','t'));
			$xCommand = strtolower($command) === 'h';
			$yCommand = strtolower($command) === 'v';
			if(strtolower($command) === 'z'){
				return $command;
			}
			if(strtolower($command) === 'a'){
				throw new \Exception('Path command "A" is currently not supportet!');
			}
			$values = explode(' ', trim(preg_replace(array('(-)', '([\\s,]+)'), array(' -', ' '), substr($maches[0], 1))));

			foreach($values as $key => $value) {

				if(
					$flip === 'horizontal' &&
					((!($key%2) && $xyCommand) || $xCommand)
				){
					$values[$key] *= -1;
					if($absoluteCommand){
						$values[$key] += $viewBox['width'];
					}
				}
				if(
					$flip === 'vertical' &&
					(($key%2 && $xyCommand) || $yCommand)
				){
					$values[$key] *= -1;
					if($absoluteCommand){
						$values[$key] += $viewBox['height'];
					}
				}
				if(
					$absoluteCommand &&
					((!($key%2) && $xyCommand) || $xCommand)
				){
					$values[$key] += $xOffset;
				}
				if(
					$absoluteCommand &&
					(($key%2 && $xyCommand) || $yCommand)
				){
					$values[$key] += $yOffset;
				}
				$values[$key] *= $scale;
				if($roundPrecision !== null){
					$values[$key] = round($values[$key], $roundPrecision);
				}

			}

			return $command.implode(' ', $values);

		}, $path);

	}

	/**
	 * converts a polygon object to a SVG path definition
	 *
	 * @param  SimpleXMLElement $polygon polygon element
	 * @return string                    SVG path definition
	 */
	protected function getPathFromPolygon(SimpleXMLElement $polygon){

		$points = explode(' ', trim(preg_replace('([\\s,]+)', ' ', $polygon['points'])));
		$path = 'M'.array_shift($points).' '.array_shift($points);
		while(count($points)){
			$path .= 'L'.array_shift($points).' '.array_shift($points);
		}
		return $path.'Z';

	}

	/**
	 * converts a rect object to a SVG path definition
	 *
	 * @param  SimpleXMLElement $rect rect element
	 * @return string                 SVG path definition
	 */
	protected function getPathFromRect(SimpleXMLElement $rect){

		if(empty($rect['width']) || $rect['width'] < 0 || empty($rect['height']) || $rect['height'] < 0){
			return '';
		}
		if(empty($rect['x'])){
			$rect['x'] = 0;
		}
		if(empty($rect['y'])){
			$rect['y'] = 0;
		}
		return 'M'.$rect['x'].' '.$rect['y'].'l'.$rect['width'].' 0l0 '.$rect['height'].'l'.(-$rect['width']).' 0Z';

	}

	/**
	 * converts a circle object to a SVG path definition
	 *
	 * @param  SimpleXMLElement $circle circle element
	 * @return string                   SVG path definition
	 */
	protected function getPathFromCircle(SimpleXMLElement $circle){

		$mult = 0.55228475;
		return
			'M'.($circle['cx']-$circle['r']).' '.$circle['cy'].
			'C'.($circle['cx']-$circle['r']).' '.($circle['cy']-$circle['r']*$mult).' '.($circle['cx']-$circle['r']*$mult).' '.($circle['cy']-$circle['r']).' '.$circle['cx'].' '.($circle['cy']-$circle['r']).
			'C'.($circle['cx']+$circle['r']*$mult).' '.($circle['cy']-$circle['r']).' '.($circle['cx']+$circle['r']).' '.($circle['cy']-$circle['r']*$mult).' '.($circle['cx']+$circle['r']).' '.$circle['cy'].
			'C'.($circle['cx']+$circle['r']).' '.($circle['cy']+$circle['r']*$mult).' '.($circle['cx']+$circle['r']*$mult).' '.($circle['cy']+$circle['r']).' '.$circle['cx'].' '.($circle['cy']+$circle['r']).
			'C'.($circle['cx']-$circle['r']*$mult).' '.($circle['cy']+$circle['r']).' '.($circle['cx']-$circle['r']).' '.($circle['cy']+$circle['r']*$mult).' '.($circle['cx']-$circle['r']).' '.$circle['cy'].
			'Z';

	}

	/**
	 * converts a ellipse object to a SVG path definition
	 *
	 * @param  SimpleXMLElement $ellipse ellipse element
	 * @return string                    SVG path definition
	 */
	protected function getPathFromEllipse(SimpleXMLElement $ellipse){

		$mult = 0.55228475;
		return
			'M'.($ellipse['cx']-$ellipse['rx']).' '.$ellipse['cy'].
			'C'.($ellipse['cx']-$ellipse['rx']).' '.($ellipse['cy']-$ellipse['ry']*$mult).' '.($ellipse['cx']-$ellipse['rx']*$mult).' '.($ellipse['cy']-$ellipse['ry']).' '.$ellipse['cx'].' '.($ellipse['cy']-$ellipse['ry']).
			'C'.($ellipse['cx']+$ellipse['rx']*$mult).' '.($ellipse['cy']-$ellipse['ry']).' '.($ellipse['cx']+$ellipse['rx']).' '.($ellipse['cy']-$ellipse['ry']*$mult).' '.($ellipse['cx']+$ellipse['rx']).' '.$ellipse['cy'].
			'C'.($ellipse['cx']+$ellipse['rx']).' '.($ellipse['cy']+$ellipse['ry']*$mult).' '.($ellipse['cx']+$ellipse['rx']*$mult).' '.($ellipse['cy']+$ellipse['ry']).' '.$ellipse['cx'].' '.($ellipse['cy']+$ellipse['ry']).
			'C'.($ellipse['cx']-$ellipse['rx']*$mult).' '.($ellipse['cy']+$ellipse['ry']).' '.($ellipse['cx']-$ellipse['rx']).' '.($ellipse['cy']+$ellipse['ry']*$mult).' '.($ellipse['cx']-$ellipse['rx']).' '.$ellipse['cy'].
			'Z';

	}

}
