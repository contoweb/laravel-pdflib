<?php

namespace Contoweb\Pdflib\Concerns;

interface FromTemplate
{
    /**
     * Return the original template.
     *
     * @return string
     */
    public function template(): string;
}