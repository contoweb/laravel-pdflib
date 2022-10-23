<?php

namespace Contoweb\Pdflib\Concerns;

interface DifferentExportLocation
{
    /**
     * Provide a different location to store exports.
     * Array has the same structure as in the config.
     *
     * @return array
     */
    public function exportLocation(): array;
}
