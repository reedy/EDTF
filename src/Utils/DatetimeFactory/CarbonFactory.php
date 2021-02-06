<?php

namespace EDTF\Utils\DatetimeFactory;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

/**
 * This factory allows us to avoid calling Carbon static methods (i.e. Carbon::create(...)) from code.
 * Thus we can mock Carbon instances in unit tests.
 *
 * @package EDTF\Utils
 */
class CarbonFactory implements DatetimeFactoryInterface
{
    /**
     * @throws DatetimeFactoryException
     */
    public function create(int $year = 0, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0, int $second = 0, $tz = null)
    {
        try {
            $c = Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
            return $c;
        } catch (InvalidFormatException $exception) {
            throw new DatetimeFactoryException($exception->getMessage());
        }
    }
}