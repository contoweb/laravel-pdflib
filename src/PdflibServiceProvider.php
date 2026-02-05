<?php

namespace Contoweb\Pdflib;

use Contoweb\Pdflib\Commands\DocumentMakeCommand;
use Contoweb\Pdflib\Exceptions\PdfWriterException;
use Contoweb\Pdflib\Writers\PdflibPdfWriter;
use Contoweb\Pdflib\Writers\PdfWriter;
use Illuminate\Support\ServiceProvider;

class PdflibServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     *
     * @throws PdfWriterException
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'pdf'
        );

        $writerClass = config('pdf.writer') ?? PdflibPdfWriter::class;

        $this->app->bindIf(PdfWriter::class, function () use ($writerClass) {
            $writer = new $writerClass(
                $this->app['config']->get('pdf.license'),
                $this->app['config']->get('pdf.creator')
            );

            if ($writer instanceof PdfWriter !== true) {
                throw new PdfWriterException('Writer must implement PdfWriter interface');
            }

            return $writer;
        });

        $this->app->bind('pdf', function () {
            return new Pdf(
                $this->app->make(PdfWriter::class)
            );
        });

        $this->app->alias('pdf', Pdf::class);

        $this->commands([DocumentMakeCommand::class]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /* Todo: Lumen setup */

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigFile() => config_path('pdf.php'),
            ], 'config');
        }
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'pdf.php';
    }
}
