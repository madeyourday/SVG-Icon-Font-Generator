# SVG-Icon-Font-Generator

Creates a SVG font from a set of SVG files and vice versa. 
The glyph mapping is based on the file names – that makes updating and extending easy and fast.

## Installation

You can create the svg-icon-font-generator.phar by yourself using the following commands:

    git clone https://github.com/madeyourday/SVG-Icon-Font-Generator.git
    cd SVG-Icon-Font-Generator
    php build.php

Or download it here: <https://github.com/madeyourday/SVG-Icon-Font-Generator/downloads>

### System Requirements
PHP 5.3 or higher

## Using

### Create a SVG font from a set of SVG files

    php svg-icon-font-generator.phar create-font /path/to/svg/files your-font.svg

The files should be named like this:
* `arrow-up-x2191.svg` use the correct unicode symbol if possible
* `magnifying-glass-xe000.svg` otherwise use the unicode "Private Use Area"
* `key.svg` this file gets automatically mapped to a unicode "Private Use Area" symbol

The list above generates the class names `icon-arrow-up`, `icon-magnifying-glass` and `icon-key`.

For creating new icons you can use this SVG template: <https://github.com/downloads/madeyourday/SVG-Icon-Font-Generator/icon-template.svg>

### Create a set of SVG files from a SVG font

    php svg-icon-font-generator.phar create-files your-font.svg /path/to/svg/files

### Create a HTML info page from a SVG font

    php svg-icon-font-generator.phar create-info your-font.svg your-font-info.html

### Create a CSS file with icon classes from a SVG font

    php svg-icon-font-generator.phar create-css your-font.svg your-icons.css

The icon class names are based on the `glyph-name`s specified in the SVG file.
