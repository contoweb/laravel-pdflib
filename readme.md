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

To change the configuration, copy the file to your config folder and enable it:

```php
$app->configure('debugbar');
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

1. Make a new page in the document.
2. Define an available font from the `fonts()` method.
3. Write the text. 


### Create a page
To create a new page, you can use
```php
$writer->newPage()
```

You optionally can define the width and height of your document by passing the parameters.

```php
$writer->newPage(210, 297) // A4 portrait format 
```

#### Using templates
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
    public function fonts(): array {
        return ['Arial'];
    }

    public function draw(Writer $writer)
    {
        $writer->newPage()->fromTemplatePage(1);
    }
}
```

Now, your first page is using the page 1 from `template.pdf`. Don't forget to configure your template configuration in the configuration file.