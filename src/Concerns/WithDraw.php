<?php

namespace Contoweb\Pdflib\Concerns;

interface WithDraw
{
    /**
     * Draw the document.
     *
     * @param Writer $writer
     */
    public function draw(Writer $writer);
}