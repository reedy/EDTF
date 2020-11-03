# EDTF Parser Library

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/ProfessionalWiki/EDTF/CI)](https://github.com/ProfessionalWiki/EDTF/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/ProfessionalWiki/EDTF/coverage.svg)](https://shepherd.dev/github/ProfessionalWiki/EDTF)

The Extended Date/Time Format (EDTF) was created by the Library of Congress with the participation and support of the bibliographic
community as well as communities with related interests.

It defines features to be supported in a date/time string, features considered useful for a wide variety of applications.

You can find more information about EDTF standart here:

https://www.loc.gov/standards/datetime/

## Usage

We have a working parser that returns start and end date elements in PHP arrays.

We will add those array elements to corresponding value object elements.

An example for current parser would be:

```php
<?php

require 'vendor/autoload.php';

use EDTF\Value\EDTFDateTime as EDTFDateTime;
use EDTF\EDTFParser as EDTFParser;

/*
Some EDTF formatted date-time examples
$dateText = "1985-04";
$dateText = "1985-04-12T21:18:35";
$dateText = "2004-01-01T10:10:10Z";
$dateText = "1985-04-12T21:18:35/2011-07-11T23:51:47+01:32";
$dateText = "1964/2008";
*/

$dateText = "1985-04-12T21:18:35/2011-07-11T23:51:47+01:32";
echo "DATE TEXT:\n$dateText\n";
echo "========\n";
EDTFParser::parseEDTFDate( $dateText );
echo "===> START DATE:\n";
echo EDTFParser::getStartDate();
echo "========\n";
echo "END DATE:\n";
echo EDTFParser::getEndDate();

$dateText = "1985-04-12T23:20:30";
echo "===> DATE TEXT:\n$dateText\n";
echo "========\n";
EDTFParser::parseEDTFDate( $dateText );
echo "ONLY DATE:\n";
echo EDTFParser::getOnlyDate();

$dateText = "1985-04";
echo "===> DATE TEXT:\n$dateText\n";
echo "========\n";
EDTFParser::parseEDTFDate( $dateText );
echo "ONLY DATE:\n";
echo EDTFParser::getOnlyDate();
```

## Installation

To use the EDTF library in your project, simply add a dependency on professional-wiki/edtf
to your project's `composer.json` file. Here is a minimal example of a `composer.json`
file that just defines a dependency on UPDATE_NAME 1.x:

```json
{
    "require": {
        "professional-wiki/edtf": "~1.0"
    }
}
```
## Todo

* Implement parser functionality for EDTF Level 1
* Write and run unit tests for Level 1
* Implement parser functionality for EDTF Level 2
* Write and run unit tests for Level 2

## Development

Start by installing the project dependencies by executing

    composer update

You can run the tests by executing

    make test
    
You can run the style checks by executing

    make cs
    
To run all CI checks, execute

    make ci
    
You can also invoke PHPUnit directly to pass it arguments, as follows

    vendor/bin/phpunit --filter SomeClassNameOrFilter
