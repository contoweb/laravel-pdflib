<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Pdf;

class PdflibServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function is_bound()
    {
        $this->assertTrue($this->app->bound('pdf'));
    }
    /**
     * @test
     */
    public function is_aliased()
    {
        $this->assertTrue($this->app->isAlias(Pdf::class));
        $this->assertEquals('pdf', $this->app->getAlias(Pdf::class));
    }
}