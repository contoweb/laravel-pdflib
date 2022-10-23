<?php

namespace Contoweb\Pdflib\Commands;

use Illuminate\Console\GeneratorCommand;

class DocumentMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:document';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new document class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Document';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/document.standard.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Documents';
    }
}
