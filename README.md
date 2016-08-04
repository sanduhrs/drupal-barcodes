# README

## Install

Install dependencies with composer from your webroot:
> composer require tecnickcom/tc-lib-barcode:^1.9

## Configure

* Add a field of the the types email, link, string, telephone, text
* Choose Barcode as formatter
* Adjust the settings like type, color and dimensions to your liking

## Warning

Please keep in mind that not any type of Barcode can contain any type of content.
Some Barcode types can contain numbers only, some even need a fixed count of characters.
Others are more versatile, but we do not check for validity.
