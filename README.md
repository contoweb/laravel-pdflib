## Laravel PDFlib
[![Run tests](https://github.com/contoweb/laravel-pdflib/actions/workflows/run-tests.yml/badge.svg)](https://github.com/contoweb/laravel-pdflib/actions/workflows/run-tests.yml)
[![StyleCI](https://github.styleci.io/repos/210450435/shield?branch=master)](https://github.styleci.io/repos/210450435)
[![Latest Stable Version](https://poser.pugx.org/contoweb/laravel-pdflib/v/stable)](https://packagist.org/packages/contoweb/laravel-pdflib)
[![License](https://poser.pugx.org/contoweb/laravel-pdflib/license)](https://packagist.org/packages/contoweb/laravel-pdflib)

This package is a Laravel wrapper for [PDFlib](https://www.pdflib.com/products/pdflib-family/overview/).
It makes generating high professional (Print-)PDFs with PDFlib a breeze.
PDFlib is the leading developer toolbox for generating and manipulating files in the Portable Document Format (PDF).

PDFlib itself is only free to use for demonstration purposes.
If you want to bring it into production, you need a PDFlib license.

## Documentation

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#sub-items)
	- [Quick start](#quick-start)
	- [Pages](#create-a-page)
	- [Templates](#using-a-template)
	    - [Preview & Print](#preview-and-print-pdf)
	- [Navigation](#navigate-on-the-page)
	- [Text](#write-text)
	    - [Fonts](#fonts)
	    - [Colors](#colors)
	- [Images](#images)
	- [PDFlib functions](#pdflib-functions)
* [Customization](#customization)
* [License](#license)

## Requirements
Since PDFlib is much more powerful than any other PDF generator, it's PHP extension needs to be registered.
You can download the extension file directly from the [PDFlib download](https://www.pdflib.com/download/pdflib-product-family/) page.

If you need further assistance installing PDFlib, check out the [installation guide](https://www.pdflib.com/fileadmin/pdflib/pdf/support/PDFlib-in-PHP-HowTo.pdf).

You also need:
* PHP: `^7.0`
* Laravel: `^6.0`

## Installation

Require this package with composer.

```shell
composer require contoweb/laravel-pdflib
```

All basic configuration is done in the `pdf.php` configuration file.
To publish the config, run the vendor publish command:

```
php artisan vendor:publish --provider="Contoweb\Pdflib\PdflibServiceProvider"
```

You can then configure paths, measure unit and so on...

### Laravel 5.5+:

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.
If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Contoweb\Pdflib\PdflibServiceProvider::class,
```

If you want to use the PDF facade, add this to your facades in app.php:

```php
'Pdf' => Contoweb\Pdflib\Facades\Pdf::class,
```

## Usage

To create a new document, you can use the `make:document` command:
```shell
php artisan make:document MarketingDocument
```

The new document file can be found in the `app/Documents` directory.

> Todo: Working on some example cases...

Ìn a final step, generating a PDF is as easy as:

```php

use App\Documents\MarketingDocument;
use Contoweb\Pdflib\Facades\Pdf;
use App\Http\Controllers\Controller;

class MarketingController extends Controller 
{
    public function storeDocument() 
    {
        return Pdf::store(new MarketingDocument, 'marketing.pdf');
    }
}
```

You can find your document in your configured export path then!
But first, let us take a look how to write a simple PDF.

### Quick start
Within your document file, you have a boilerplated method `draw()`:

```php
public function draw(Writer $writer)
{
    $writer->newPage();
    $writer->useFont('Arial', 12);
    $writer->writeText('Start something great...');
}
```

Here you actually write the document's content. As you can see, a small example is already boilerplated:

1. Create a new page in the document.
2. Use an available font from the `fonts()` method.
3. Write the text. 

### Create a page
To create a new page, you can use
```php
$writer->newPage();
```

You can optionally define the width and height of your document by passing the parameters.

```php
$writer->newPage(210, 297); // A4 portrait format 
```

#### Using a template
In most cases, you want to write dynamic content on a already designed PDF.
To use a PDF template, use the `FromTemplate` concern and define the template PDF in a new `template()` function:

```php
namespace App\Documents;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Writers\PdfWriter as Writer;

class MarketingDocument implements FromTemplate, WithDraw
{
    public function template(): string {
        return 'template.pdf';
    }
    
    // ...

    public function draw(Writer $writer)
    {
        $writer->newPage()->fromTemplatePage(1);
    }
}
```

Now, your first page is using the page 1 from `template.pdf`. 
As you can see, you don't need to define a page size since it's using the template's size.
Don't forget to configure your templates location in the configuration file.

##### Preview and print PDF
If you're aware of (professional) print-ready PDFs, you may know that your print PDF isn't the same as the user finally sees.

![pdf-bleed](https://user-images.githubusercontent.com/13394801/65696401-6e6fdc00-e079-11e9-96fa-86e9d40d6aa1.jpg)

There is a bleed box, crop marks and so on. For this case, you can use `WithPreview` combined with the `FromTemplate` concern.
While your original template includes all the boxes and marks, your preview PDF is a preview of the final document.

This requires you to add a `previewTemplate()` and `offset()` method.

```php
namespace App\Documents;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\WithPreview;
use Contoweb\Pdflib\Writers\PdfWriter as Writer;

class MarketingDocument implements FromTemplate, WithDraw, WithPreview
{
    public function template(): string {
        return 'print.pdf';
    }

    public function previewTemplate(): string
    {
        return 'preview.pdf';
    }

    public function offset(): array
    {
        return [
            'x' => 20,
            'y' => 20,
        ];
    }
    
    //
}
```

The `offset()` method defines the offset from the print PDF to the preview PDF (see image above).

Now you can generate the print and preview PDF with:
```php
return Pdf::store(new MarketingDocument, 'marketing.pdf')->withPreview();
```

The preview PDF will be automatically named to `<<name>>_preview.pdf`. 
You can override this by passing the name in `->withPreview('othername.pdf')`.

### Navigate on the page

To tell PDFlib where your elements should be placed, you have to set the `X` and `Y` position of your "cursor".

```php
$writer->setPosition(10, 100);

// only X axis
$writer->setXPosition(10);

//only Y axis
$writer->setYPosition(100);

```

In the configuration file, you can define which measure unit is used for positioning. You can choose between `mm` or `pt`.

> **Note**: It may be confusing in the beginning, but PDFlib Y axis are measured from the bottom.
So position 0 0 is in the left bottom corner, not the left top corner.

### Write text

To write text, you can simply use:

```php
$writer->writeTextLine('your text');

// or

$writer->writeText('your text');

```

Don't forget to set the cursor position and use the right font before writing text.
Since the package extends PDFlib, you also can pass PDFlib options as a second parameter.

> You only have to use `writeText` when placing two text blocks next to each other.
Behind the scenes, `wirteText()` uses PDFlibs `show()` method, while `wirteTextLine()` uses the mostly used PDFlib method `fit_text_line()`.

If you want to go to the next line, instead of reposition your cursor every time, you can use:
```php
$writer->nextLine();
```
To use a custom line spacing instead of 1.0, just pass it as a parameter.

#### Fonts
The boilerplate document loads `Arial` as an example font, but we don't provide a font file in the fonts folder.
In this case, PDFlib tries to load it from your host fonts.
You may want to use custom fonts and want ensure that your server is able to load it. 
So it's highly recommended to place the font files (currently .ttf and .otf is supported) inside your configured font location (see `pdf.php` configuration).

As a next step, you have to make the fonts available in your document. For TrueType fonts, just use the file name without the extension to auto-load the font:
```php
public function fonts(): array 
{
    return ['OpenSans-Regular'];
}
```

An underlying font file like `OpenSans-Regular.ttf` has to be available in your fonts location.

Now you can use the font in your document by it's name:

```php
public function draw(Writer $writer)
{
    $writer->newPage();
    $writer->useFont('OpenSans-Regular', 12)
           ->writeText('This text is written with Open Sans font...');
}
```

You can also overwrite default font encoding and option list:

```php
public function fonts(): array
    {
        return [
            'OpenSans-Regular' => [
                'encoding' => 'ansi',
                'optlist' => ''
            ],
        ];
    }
```

#### Colors
If you need to colorize your text, you can use the ```WithColor``` concern. This requires you to define custom colors:
```php
public function colors(): array
{
    return [
        'orange-rgb' => ['rgb', 255, 165, 0],
        'blue-cmyk' => ['cmyk', 100, 100, 0, 0],
    ];
}
```

You can use the color with:

```php
$writer->useColor('orange-rgb');
```
or as a parameter when using a font:
```php
$writer->useFont('OpenSans-Regular', 12, 'blue-cmyk');
```

### Tables

To write a table you can follow this example:

```php
$items = [
	['first_name' => 'John', 'last_name' => 'Doe'],
	['first_name' => 'Jane','last_name' => 'Doe'],
];

$table = $writer
	->setPosition(10, 150)
	->newTable($items);

$table
	->addColumn(50)
	->addColumn(50)
	->withHeader(['First name', 'Last name'])
	->place("stroke={ {line=horother linewidth=0}}")
;
```

### Images
You can place images with:
```php
$writer->drawImage('/path/to/the/image.jpeg', 150, 100);
```
This places an image with and resize it to 150x100.

Since loading rounded images is just a pain in PDFlib, you can use the method:
```php
$writer->circleImage('/path/to/the/image.jpeg', 100);
```

### PDFlib functions
Since this package extending PDFlib, you can use the whole PDFlib toolkit.
The [PDFlib Cookbook](https://www.pdflib.com/pdflib-cookbook/) helps a lot, even to understand this package.

## Extending
This package is just a basic beginning of wrapping PDFlib.
Since PDFlib brings so much more functionality, we have to put the focus on the most used functions in the beginning.

You're welcome to PR your ideas!

## Customization
If you want to use a filesystem disk / path other than the config in a specific document, 
you can use the following concerns:

- `Contoweb\Pdflib\Concerns\DifferentExportLocation`: Custom export location
- `Contoweb\Pdflib\Concerns\DifferentFontsLocation`: Custom location for fonts
- `Contoweb\Pdflib\Concerns\DifferentTemplateLocation`: Custom template location

The storage and path are defined the same way as in the config file: 

```php
public function exportLocation(): array
{
	return [
		'disk' => 'other',
		'path' => null,
	];
}

public function fontsLocation(): array
{
	return [
		'disk' => 'other',
		'path' => 'custom-font-directory',
	];
}

public function templateLocation(): array
{
	return [
		'disk' => 'other',
		'path' => 'custom-template-directory',
	];
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
