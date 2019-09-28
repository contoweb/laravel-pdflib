<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Commands\DocumentMakeCommand;
use Symfony\Component\HttpKernel\Tests\TestKernel;

class MakeDocumentCommandTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_document_from_stub()
    {
        $this->artisan('make:document TestDocument')
            ->assertExitCode(0);
    }
}
