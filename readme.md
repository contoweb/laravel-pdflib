## Laravel PDFLib

This package is a Laravel wrapper for [PDFLib](https://www.pdflib.com/products/pdflib-family/overview/).
It makes generating professional, print-ready PDFs a breeze.
PDFlib is the leading developer toolbox for generating and manipulating files in the Portable Document Format (PDF).

## Requirements
Since PDFLib is much more powerful than any other PDF generator, it's PHP extension needs to be registered.
You can download the extension file directly from the [PDFLib download](https://www.pdflib.com/download/pdflib-product-family/) page.

If you need further assistance installing PDFLib, check out the [installation guide](https://www.pdflib.com/fileadmin/pdflib/pdf/support/PDFlib-in-PHP-HowTo.pdf).

You also need:
* PHP: `^7.0`
* Laravel: `^5.5`

## Installation

Require this package with composer.

```shell
composer contoweb/laravel-pdflib
```

All basic configuration is done in the `pdf.php` configuration file.
To publish the config, run the vendor publish command:

```
php artisan vendor:publish --provider="Contoweb\Pdflib\PdflibServiceProvider"
```

You can then configure paths, measure unit and so on...

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

### Laravel 5.5+:

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

The new document file can be found in the app/Exports` directory.

> Todo: Working on some example files...

Later, the document can be generated easily with:

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

You can find then your document in your configured export path!
But firstly, let us dive into writing a simple PDF.

### Quick startup
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

You optionally can define the width and height of your document by passing the parameters.

```php
$writer->newPage(210, 297); // A4 portrait format 
```

#### Using a template
In most cases, you want to write dynamic content on a already designed PDF.
To use a PDF template, use the `WithTemplate` concern:

```php
namespace App\Documents;

use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Writers\PdfWriter as Writer;

class MarketingDocument implements WithDraw
{
    public function template(): array {
        return 'template.pdf';
    }
    
    // ...

    public function draw(Writer $writer)
    {
        $writer->newPage()->fromTemplatePage(1);
    }
}
```

Now, your first page is using the page 1 from `template.pdf`. Don't forget to configure your templates location in the configuration file.

##### Preview and print PDF
If you're aware of print-ready PDFs, you know that your print PDF isn't the same as the user finally sees.

![pdf-bleed](https://user-images.githubusercontent.com/13394801/65696401-6e6fdc00-e079-11e9-96fa-86e9d40d6aa1.jpg)

There is a bleed box, crop marks and so on. For this case, you can use `WithPreview` combined with the `FromTemplate` concern.

This requires you to add a `template()`, `previewTemplate()` and `offset()` method.

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
            'y' => 20
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
You can overriding this by passing the name in `->withPreview('othername.pdf')`.

### Navigate on the page
You have to define where your elements should be placed. You have to set the `X` and `Y` position of your "cursor".
```php
$writer->setPosition(10, 100);

// only X axis
$writer->setXPosition(10);

//only Y axis
$writer->setYPosition(100);

```

In the configuration file, you can define in which measure unit you want to set positions. You can choose between `mm` or `pt`.

> **Note**: It may be confusing in the beginning, but PDFLib Y axis are measured from the bottom.
So position 0 0 is in the left bottom corner, no the left top corner.


### Write text
You can use
```php
$writer->writeTextLine('your text')

// or

$writer->writeText('your text')

```

to write standard text. 
Don't forget to firstly set the position and use the right font.

> You only have to use `writeText` when placing two text blocks next to each other.
Behind the scenes, `wirteText()` uses PDFLibs `show()` method, while `wirteTextLine()` uses the mostly used PDFLib method `fit_text_line()`.




