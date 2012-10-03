<?php
/*
 * Copyright MADE/YOUR/DAY <mail@madeyourday.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\SVG;

/**
 * SVG Icon Font Generator
 *
 * @author Martin Auswöger <martin@madeyourday.co>
 */
class IconFontGenerator{

	/**
	 * @var Font
	 */
	protected $font;

	/**
	 * @var array mapping information file path and name mapped to glyph character
	 */
	protected $mapping = array();

	/**
	 * generate a font from a directory containing svg files (one file per glyph)
	 * by naming convention the file name can specify which character should be mapped to the glyph
	 * - my-icon.png       => creates a glyph with the name 'my-icon' mapped to a character in the Unicode Private Use Area
	 * - my-icon-x263a.png => creates a glyph with the name 'my-icon' mapped to the unicode character U+263A "☺"
	 *
	 * @param  string  $path              SVG path definition (consider the font coordinate system has an inverted y axis)
	 * @param  array   $fontOptions       font options for the Font object
	 * @param  boolean $renameSourceFiles if true, files without mapping information will be renamed
	 * @return static                     this
	 */
	public function generateFromDir($path, $fontOptions = array(), $renameSourceFiles = false){

		$this->font = new Font($fontOptions);
		$this->mapping = array();

		$fontOptions = $this->font->getOptions();

		$files = scandir($path);
		foreach($files as $fileName){
			if(strtolower(substr($fileName, -4)) === '.svg'){
				if(preg_match('(^(.*)-x([0-9a-f]{2,6})\\.svg$)i', $fileName, $matches)){
					$iconName = strtolower($matches[1]);
					$iconCode = static::hexToUnicode(strtolower($matches[2]));
					if(isset($this->mapping[$iconCode])){
						throw new \Exception('Duplicate glyph '.$iconCode.' '.$iconName);
					}
					$this->mapping[$iconCode] = array(
						'path' => $path.DIRECTORY_SEPARATOR.$fileName,
						'name' => $iconName,
					);
				}
			}
		}
		foreach($files as $fileName){
			if(strtolower(substr($fileName, -4)) === '.svg'){
				if(!preg_match('(^(.*)-x([0-9a-f]{2,6})\\.svg$)i', $fileName)){
					$iconName = strtolower(substr($fileName, 0, -4));
					$code = hexdec('e000');
					while(isset($this->mapping[static::hexToUnicode(dechex($code))])){
						$code++;
					}
					if($renameSourceFiles){
						if(!rename($path.DIRECTORY_SEPARATOR.$fileName, $path.DIRECTORY_SEPARATOR.$iconName.'-x'.dechex($code).'.svg')){
							throw new \Exception('unable to rename "'.$path.DIRECTORY_SEPARATOR.$fileName.'"');
						}
						$fileName = $iconName.'-x'.dechex($code).'.svg';
					}
					$this->mapping[static::hexToUnicode(dechex($code))] = array(
						'path' => $path.DIRECTORY_SEPARATOR.$fileName,
						'name' => $iconName,
					);
				}
			}
		}
		foreach($this->mapping as $code => $icon){
			try{
				$iconDoc = new Document(file_get_contents($icon['path']));
				$viewBox = $iconDoc->getViewBox();
				$this->font->addGlyph(
					$code,
					$iconDoc->getPath($fontOptions['units-per-em']/$viewBox['height'], null, 'vertical', true, 0, $fontOptions['descent']),
					$icon['name'],
					round($viewBox['width']*$fontOptions['units-per-em']/$viewBox['height'])
				);
			}
			catch(\Exception $e){
				throw new \Exception(basename($icon['path']).': '.$e->getMessage());
			}
		}

		return $this;

	}

	/**
	 * generate a font based on a existing SVG font
	 *
	 * @param  Font   $font font object
	 * @return static       this
	 */
	public function generateFromFont(Font $font){

		$this->mapping = array();
		$this->font = $font;
		return $this;

	}

	/**
	 * get the font object
	 *
	 * @return Font font object
	 */
	public function getFont(){
		return $this->font;
	}

	/**
	 * get glyph names
	 *
	 * @return array array with the characters (in hex) as keys and the glyph names as values
	 */
	public function getGlyphNames(){

		$glyphNames = array();
		foreach($this->font->getGlyphs() as $glyph){
			$glyphNames[static::unicodeToHex($glyph['char'])] = empty($glyph['name']) ? null : $glyph['name'];
		}
		return $glyphNames;

	}

	/**
	 * get css definition for icon class names
	 *
	 * @return string css class definitions
	 */
	public function getCss(){

		$css = '';
		foreach($this->getGlyphNames() as $unicode => $name){
			$css .= ".icon-".$name.":before {"."\n";
			$css .= "\tcontent: \"\\".$unicode."\";\n";
			$css .= "}\n";
		}
		return $css;

	}

	/**
	 * save the current font as single svg files in a directory (counterpart of generateFromDir)
	 *
	 * @param  string $dir directory path
	 * @return static      this
	 */
	public function saveGlyphsToDir($dir){

		$fontOptions = $this->font->getOptions();

		$svgTemplate = '<?xml version="1.0" encoding="utf-8"?>'.
			'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.
			'<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="%%%WIDTH%%%px" height="512px" viewBox="0 0 %%%WIDTH%%% 512" enable-background="new 0 0 512 512" xml:space="preserve">'.
			'	<g id="Grid">'.
			'		<rect x="0" fill="none" stroke="#A9CCDB" stroke-miterlimit="10" width="512" height="512"/>';
		for($i = 32; $i < 512; $i += 32){
			$color = 'A9CCDB';
			if($i === 448){
				$color = 'FF0000';
			}
			$svgTemplate .= '<line fill="none" stroke="#'.$color.'" stroke-miterlimit="10" x1="0" y1="'.$i.'" x2="512" y2="'.$i.'"/>';
		}
		for($i = 32; $i < 512; $i += 32){
			$svgTemplate .= '<line fill="none" stroke="#A9CCDB" stroke-miterlimit="10" x1="'.$i.'" y1="0" x2="'.$i.'" y2="512"/>';
		}
		$svgTemplate .= '</g>'.
			'<path d="%%%PATH%%%"/>'.
			'</svg>';

		if(!is_dir($dir)){
			throw new \InvalidArgumentException('$dir must be a writable directory');
		}

		foreach($this->font->getGlyphs() as $glyph){

			$targetPath = $dir.DIRECTORY_SEPARATOR.(empty($glyph['name']) ? 'icon' : preg_replace('([^a-z0-9]+)i', '-', $glyph['name'])).'-x'.static::unicodeToHex($glyph['char']).'.svg';

			if(isset($this->mapping[$glyph['char']])){

				if(!copy($this->mapping[$glyph['char']]['path'], $targetPath)){
					throw new \Exception('unable to copy "'.$this->mapping[$glyph['char']]['path'].'" to "'.$targetPath.'"');
				}

			}
			else{

				$glyphDocument = Document::createFromPath($glyph['path'], $fontOptions['horiz-adv-x'], $fontOptions['units-per-em']);
				if(file_put_contents(
					$targetPath,
					str_replace(array('%%%PATH%%%', '%%%WIDTH%%%'), array(
						$glyphDocument->getPath(512/$fontOptions['units-per-em'], null, 'vertical', true, 0, -64),
						empty($glyph['width']) ? 512 : ($glyph['width']*512/$fontOptions['units-per-em'])
					), $svgTemplate)
				) === false){
					throw new \Exception('unable to write to "'.$targetPath.'"');
				}

			}

		}

		return $this;

	}

	/**
	 * get the unicode hex representation of a unicode character
	 *
	 * @param  string $char single character encoded as UTF-8
	 * @return string       unicode hex representation of the character
	 */
	public static function unicodeToHex($char){

		if(!is_string($char) || mb_strlen($char, 'utf-8') !== 1){
			throw new \InvalidArgumentException('$char must be one single character');
		}

		$unicode = unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'));
		return dechex($unicode[1]);

	}

	/**
	 * get the unicode character of a unicode hex string
	 *
	 * @param  string $char unicode hex representation (e.g. "263a")
	 * @return string       single character encoded as UTF-8
	 */
	protected static function hexToUnicode($char){

		if(!is_string($char) || !preg_match('(^[0-9a-f]{2,6}$)i', $char)){
			throw new \InvalidArgumentException('$char must be one single unicode character as hex string');
		}

		return mb_convert_encoding('&#x'.strtolower($char).';', 'UTF-8', 'HTML-ENTITIES');

	}

}
