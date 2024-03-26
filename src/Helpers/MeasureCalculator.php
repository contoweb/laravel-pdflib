<?php

namespace Contoweb\Pdflib\Helpers;

use Contoweb\Pdflib\Exceptions\MeasureException;

class MeasureCalculator
{
    const mmToPt = 2.834645669;
    const ptToMm = 0.352777778;

    /**
     * @param  $measure
     * @param  $unit
     * @return float
     *
     * @throws MeasureException
     */
    public static function calculateToPt($measure, $unit = null)
    {
        return self::calculateToUnit($measure, 'pt', $unit);
    }

    /**
     * @param  $measure
     * @param  $unit
     * @return float
     *
     * @throws MeasureException
     */
    public static function calculateToMm($measure, $unit = null)
    {
        return self::calculateToUnit($measure, 'mm', $unit);
    }

    /**
     * @param  float  $measure
     * @param  string  $toUnit
     * @param  string|null  $fromUnit
     * @return float
     *
     * @throws MeasureException
     */
    public static function calculateToUnit($measure, $toUnit, $fromUnit = null)
    {
        if ($fromUnit == null) {
            $fromUnit = config('pdf.measurement.unit', 'pt');
        }

        if ($toUnit == 'mm') {
            if ($fromUnit == 'pt') {
                return $measure * self::ptToMm;
            }

            if ($fromUnit == 'mm') {
                return $measure;
            }
        }

        if ($toUnit == 'pt') {
            if ($fromUnit == 'mm') {
                return $measure * self::mmToPt;
            }

            if ($fromUnit == 'pt') {
                return $measure;
            }
        }

        throw new MeasureException('Unknown measure unit.');
    }
}
