<?php
namespace Abs\BasicPkg\Helpers;

class AbsStringHelper
{
	public static function format_minutes_hours($minutes, $minified = false)
	{
		$seconds = $minutes * 60;

		$ret = "";

		/*** get the days ***/
		$days = intval(intval($seconds) / (3600 * 24));
		if ($days > 0) {
			$ret .= $days;
			if ($minified) {
				$ret .= 'd ';
			} else {
				$ret .= ($days == 1) ? ' day ' : ' days ';
			}

		}

		/*** get the hours ***/
		$hours = (intval($seconds) / 3600) % 24;
		if ($hours > 0) {
			$ret .= $hours;
			if ($minified) {
				$ret .= 'h ';
			} else {
				$ret .= ($hours == 1) ? ' hour ' : ' hours ';
			}

		}

		/*** get the minutes ***/
		$minutes = (intval($seconds) / 60) % 60;
		if ($minutes > 0) {
			$ret .= $minutes;
			if ($minified) {
				$ret .= 'm ';
			} else {
				$ret .= ($minutes == 1) ? ' min ' : ' mins  ';
			}

		}

		if ($ret == '') {
			$ret = '0m';
		}

		return trim($ret);
	}

	public static function implode_natural(array $list, $conjunction = 'and')
	{
		$last = array_pop($list);
		if ($list) {
			return implode(', ', $list).' '.$conjunction.' '.$last;
		}

		return $last;
	}

	public static function upper_camel_case($value)
	{
		return ucfirst(camel_case($value));
	}

	/**
	 * Return the number part of a string
	 *
	 * Assumes numbers use , as thousand separator and . as decimal marker.
	 *
	 * @param $str
	 * @param  string  $thousandSeparator
	 * @param  string  $decimalMark
	 *
	 * @return mixed|null
	 */
	public static function str_number_only($str, $thousandSeparator = ',', $decimalMark = '.')
	{
		if (preg_match("#(?:[-][ ]*)?[0-9{$thousandSeparator}]+(?:[{$decimalMark}][0-9]+)?#", $str, $matches)) {
			// Remove thousand separators
			$number = str_replace($thousandSeparator, '', $matches[0]);
			// Convert decimals markers to '.'
			$number = str_replace($decimalMark, '.', $number);

			return $number;
		} else {
			return null;
		}
	}
}
