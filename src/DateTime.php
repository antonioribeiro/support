<?php

namespace PragmaRX\Support;

use Carbon\Carbon;

class DateTime extends Carbon
{
    const SEPARATORS = [
        '-',
        '/',
        '\\',
        ' ',
        '',
    ];

    const DATE_FORMATS = [
        'd',
        'm',
        'Y|y',
    ];

    const TIME_FORMATS = [
        'H:i:s',
        'h:i:s',
    ];

    public static function parse($time = null, $tz = null)
    {
//        try
//        {
//            return new static($time, $tz);
//        }
//        catch (\Exception $e) {}

        $time = trim($time);

        $dateFormats = static::permuteDates($time);

        foreach ($dateFormats as $dateFormat)
        {
            foreach (static::TIME_FORMATS as $timeFormat)
            {
                if ($date = static::parseWithFormat($dateFormat, $timeFormat, $time))
                {
                    return $date;
                }
            }
        }
    }

    private static function permuteDates($time)
    {
        $result = [];

        foreach (array_permute(static::DATE_FORMATS) as $item)
        {
            foreach (static::SEPARATORS as $separator)
            {
                if ($separator == '' || strpos($time, $separator) > 0)
                {
                    $formats = static::splitFormats($item);

                    foreach ($formats as $format)
                    {
                        // We should have no year in the middle of a date
                        if ($format[1] !== 'Y' && $format[1] != 'y')
                        {
                            $result[] = join($separator, $format);
                        }
                    }
                }
            }
        }

        return $result;
    }

    private static function splitFormats($item)
    {
        $format1 = [];
        $format2 = [];

        foreach ($item as $format)
        {
            $chars = explode('|', $format);

            $format1[] = $chars[0];
            $format2[] = isset($chars[1]) ? $chars[1] : $chars[0];
        }

        return [$format1, $format2];
    }

    private static function parseWithFormat($dateFormat, $timeFormat, $value)
    {
        if (! $date = static::tryParsingDateAndTimeWithFormat($dateFormat, $value))
        {
            if (! $date = static::tryParsingDateAndTimeWithFormat($dateFormat.' '.$timeFormat, $value))
            {
                return null;
            }
        }

        $test = $date->format($dateFormat);

        if ($test !== $value)
        {
            return null;
        }

        if (strlen($dateFormat) < 5 && $date->day < 12)
        {
            throw new \Exception('Value cannot be safely parsed.');
        }

        return $date;
    }

    private static function tryParsingDateAndTimeWithFormat($format, $value)
    {
        try {
            return Carbon::createFromFormat($format, $value);
        }
        catch (\Exception $e)
        {
            return null;
        }
    }
}
