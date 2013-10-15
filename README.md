# SVG-Icon-Font-Generator

Creates a SVG font from a set of SVG files and vice versa.  
The glyph mapping is based on the file names – that makes updating and extending easy and fast.

## Installation

You can create the svg-icon-font-generator.phar by yourself using the following commands:
(this requires [composer.phar](http://getcomposer.org/) to be installed)

    git clone https://github.com/madeyourday/SVG-Icon-Font-Generator.git
    cd SVG-Icon-Font-Generator
    php composer.phar install
    php build.php

Or download the latest release here: <https://github.com/madeyourday/SVG-Icon-Font-Generator/releases>

### System Requirements
* PHP 5.3 or higher
* PHP mbstrings needs to be enabled
* phar.readonly must be off.

## Usage

### Create a SVG font from a set of SVG files

    php svg-icon-font-generator.phar create-font /path/to/svg/files your-font.svg --rename-files

The files should be named like this:
* `arrow-up-x2191.svg` use the correct unicode symbol if possible
* `magnifying-glass-xe001.svg` otherwise use the unicode "Private Use Area" (start from xe001, don't use xe000)
* `key.svg` this file gets automatically mapped to a unicode "Private Use Area" symbol, if you use the `--rename-files` option this file will be renamed to something like `key-xe002.svg`

The list above generates the class names `icon-arrow-up`, `icon-magnifying-glass` and `icon-key`.

For creating new icons you can use this SVG template: <https://github.com/downloads/madeyourday/SVG-Icon-Font-Generator/icon-template.svg>

An example set of SVG files can be found here: <https://github.com/madeyourday/RockSolid-Icon-Font/tree/master/svg>

### Create a set of SVG files from a SVG font

    php svg-icon-font-generator.phar create-files your-font.svg /path/to/svg/files

### Create a HTML info page from a SVG font

    php svg-icon-font-generator.phar create-info your-font.svg your-font-info.html

### Create a CSS file with icon classes from a SVG font

    php svg-icon-font-generator.phar create-css your-font.svg your-icons.css

The icon class names are based on the `glyph-name`s specified in the SVG file.
