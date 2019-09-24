<?php

namespace Contoweb\Pdflib;

use Contoweb\Pdflib\Commands\DocumentMakeCommand;
use Contoweb\Pdflib\Concerns\Writer;
use Contoweb\Pdflib\Files\FileManager;
use Contoweb\Pdflib\Writers\PdflibWriter;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class LaravelPdflibServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'pdf'
        );

        $this->app->bind(Writer::class, function () {
            return new PdflibWriter(
                config('pdf.license'),
                config('pdf.creator', 'Laravel'),
                FileManager::fontsDirectory()
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
        if ($this->app->runningInConsole()) {
            if ($this->app instanceof LumenApplication) {
                $this->app->configure('pdf');
            } else {
                $this->publishes([
                    $this->getConfigFile() => config_path('pdf.php'),
                ], 'config');
            }
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