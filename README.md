# SVG-Icon-Font-Generator

Creates a SVG font from a set of SVG files and vice versa. 
The glyph mapping is based on the file names – that makes updating and extending easy and fast.

## Installation

You can create the svg-icon-font-generator.phar by yourself using the following commands:

    git clone https://github.com/madeyourday/SVG-Icon-Font-Generator.git
    cd SVG-Icon-Font-Generator
    php build.php

Or download it here: <https://github.com/madeyourday/SVG-Icon-Font-Generator/downloads>

## Using

### Create a SVG font from a set of SVG files

    php svg-icon-font-generator.phar create-font /path/to/svg/files your-font.svg

### Create a set of SVG files from a SVG font

    php svg-icon-font-generator.phar create-files your-font.svg /path/to/svg/files

### Create a HTML info page from a SVG font

    php svg-icon-font-generator.phar create-info your-font.svg your-font-info.html
