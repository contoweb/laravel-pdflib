<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Pdf;
use PHPUnit\Framework\Attributes\Test;

class PdflibServiceProviderTest extends TestCase
{
    #[Test]
    public function is_bound()
    {
        $this->assertTrue($this->app->bound('pdf'));
    }

    #[Test]
    public function is_aliased()
    {
        $this->assertTrue($this->app->isAlias(Pdf::class));
        $this->assertEquals('pdf', $this->app->getAlias(Pdf::class));
    }
}
