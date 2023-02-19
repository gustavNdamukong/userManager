<?php

namespace classes;
/**
 * Contains functions for converting between the various representations of dates
 *
 * @author Gustav
 */
class DateConversion
{
    /**
     * Converts a date in DD/MM/YYYY (or DD-MM-YYYY) format into YYYY-MM-DD format
     * suitable for Postgres
     *
     * @param string $date The date to convert
     * @return string The same date in YYYY-MM-DD format
     */
    public static function DDMMYYYYtoYYYYMMDD($date)
    {
        return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
    }


    /**
     * Converts a date from YYYY/MM/DD/ (or YYY-MM-DD) format into DD-MM-YYYY format
     *
     * @param string $date The date to convert
     * @return string The same date in DD-MM-YYYY format
     */
    public static function YYYYMMDDtoDDMMYYYY($date)
    {
        return date('d-m-Y', strtotime(str_replace('/', '-', $date)));
    }


    /**
     * Converts a Postgres/ANSI timestamp to a UK date/time format
     *
     * @param string $timestamp The timestamp to convert
     * @return string A formatted date
     */
    public static function timestampToDateTime($timestamp)
    {
        return date('d/m/Y H:i:s', strtotime($timestamp));
    }
}
