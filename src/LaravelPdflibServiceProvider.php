<?php

namespace Contoweb\Pdflib;

use Contoweb\Pdflib\Commands\DocumentMakeCommand;
use Contoweb\Pdflib\Concerns\Writer;
use Contoweb\Pdflib\Writers\PdflibWriter;
use Illuminate\Support\ServiceProvider;

class LaravelPdflibServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Writer::class, function () {
            return new PdflibWriter(
                config('pdf.license'),
                config('pdf.creator', 'Laravel'),
                config('pdf.fonts.location', storage_path('app'))
            );
        });

        $this->app->bind('pdf', function () {
            return new Pdf(
                $this->app->make(Writer::class)
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
        //
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'pdflib.php';
    }
}
