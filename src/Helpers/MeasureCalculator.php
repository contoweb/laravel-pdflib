<?php

namespace Contoweb\Pdflib\Helpers;

use Contoweb\Pdflib\Exceptions\MeasureException;

class MeasureCalculator
{
    const mm = 2.83465;

    /**
     * @param $measure
     * @param $unit
     * @return float
     * @throws MeasureException
     */
    public static function calculateToPt($measure, $unit = null) {
        if($unit == null) {
            $unit = config('pdf.measurement.unit', 'pt');
        }

        if($unit == 'mm') {
            $measure = $measure * self::mm;
        } else if ($unit != 'pt') {
            throw new MeasureException();
        }

        return $measure;
    }
}