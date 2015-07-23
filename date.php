<?php

class Crb_Date {
	public static function get_calendar_days($year = '', $month = '') {
		$calendar = array();

		// Get $year and $month
		extract(Crb_Date::sanitize_year_month($year, $month));

		$month_info = Crb_Date::get_month_info($year, $month);

		$first_visible_day = $month_info['previous_month_last_day'] - $month_info['previous_month_offset'] + 1;
		for ( $day_index = $first_visible_day; $day_index <= $month_info['previous_month_last_day']; $day_index++ ) { 
			$calendar[] = array(
				'day_index' => $day_index,
				'class' => 'gce-day-past',
				'date' => date(CRB_SQL_DATE_FORMAT, strtotime($month_info['previous_month_year'] . '-' . $month_info['previous_month_index'] . '-' . $day_index)),
			);
		};

		for ( $day_index = 1; $day_index <= $month_info['current_month_last_day']; $day_index++ ) { 
			$class = 'gce-day';
			$loop_day = mktime(0, 0, 0, $month, $day_index, $year);
			if ( date(CRB_SQL_DATE_FORMAT) == date(CRB_SQL_DATE_FORMAT, $loop_day) ) {
				$class = 'gce-day-chosen';
			}

			$calendar[] = array(
				'day_index' => $day_index,
				'class' => $class,
				'date' => date(CRB_SQL_DATE_FORMAT, strtotime($year . '-' . $month . '-' . $day_index)),
			);
		};

		for ( $day_index = 1; $day_index <= $month_info['next_month_offset']; $day_index++ ) { 
			$calendar[] = array(
				'day_index' => $day_index,
				'class' => 'gce-day-future',
				'date' => date(CRB_SQL_DATE_FORMAT, strtotime($month_info['next_month_year'] . '-' . $month_info['next_month_index'] . '-' . $day_index)),
			);
		};

		return $calendar;
	}

	public static function get_month_info($year = '', $month = '') {
		// Get $year and $month
		extract(Crb_Date::sanitize_year_month($year, $month));

		$transient_string = 'crb_date_get_month_info_' . $month .  '_' . $year;
		$result = get_transient($transient_string); // Cache boost from this line
		if ( !empty($result) ) {
			return $result;
		}

		$first_day = 1;

		// current_month
		$current_month_first_day_timestamp = mktime(0, 0, 0, $month, $first_day, $year);
		$current_month_first_day_week_index = date('w', $current_month_first_day_timestamp);
		// $current_month_first_day_week_index = Crb_Date::sanitize_week_index($current_month_first_day_week_index);

		$current_month_last_day = date('t', $current_month_first_day_timestamp);
		$current_month_last_day_timestamp = mktime(0, 0, 0, $month, $current_month_last_day, $year);
		$current_month_last_day_week_index = date('w', $current_month_last_day_timestamp);
		// $current_month_last_day_week_index = Crb_Date::sanitize_week_index($current_month_last_day_week_index);

		// previous_month
		$previous_month_first_day_timestamp = strtotime('-1 month', $current_month_first_day_timestamp);
		$previous_month_index = date('n', $previous_month_first_day_timestamp);
		$previous_month_year = date('Y', $previous_month_first_day_timestamp);

		$previous_month_first_day_week_index = date('w', $previous_month_first_day_timestamp);
		// $previous_month_first_day_week_index = Crb_Date::sanitize_week_index($previous_month_first_day_week_index);

		$previous_month_last_day = date('t', $previous_month_first_day_timestamp);
		$previous_month_last_day_timestamp = strtotime('+' . ($previous_month_last_day-1) . ' days', $previous_month_first_day_timestamp);
		$previous_month_last_day_week_index = date('w', $previous_month_last_day_timestamp);
		// $previous_month_last_day_week_index = Crb_Date::sanitize_week_index($current_month_last_day_week_index);;

		// 6 is the total number of days in the week, when counting from 0, so this is the count of previous month shown days
		$previous_month_offset = $current_month_first_day_week_index;

		// next_month
		$next_month_first_day_timestamp = strtotime('+1 month', $current_month_first_day_timestamp);
		$next_month_index = date('n', $next_month_first_day_timestamp);
		$next_month_year = date('Y', $next_month_first_day_timestamp);

		$next_month_first_day_week_index = date('w', $next_month_first_day_timestamp);
		// $next_month_first_day_week_index = Crb_Date::sanitize_week_index($next_month_first_day_week_index);

		$next_month_last_day = date('t', $next_month_first_day_timestamp);
		$next_month_last_day_timestamp = strtotime('+' . ($next_month_last_day-1) . ' days', $next_month_first_day_timestamp);
		$next_month_last_day_week_index = date('w', $next_month_last_day_timestamp);
		// $next_month_last_day_week_index = Crb_Date::sanitize_week_index($next_month_last_day_week_index);

		// 6 is the total number of days in the week, when counting from 0, 
		// so the rest is how many days we need to show from the next month
		$next_month_offset = 6 - $current_month_last_day_week_index;

		$result = array(
			'current_month_first_day' => 1, // 1
			'current_month_first_day_timestamp' => $current_month_first_day_timestamp, // unix timestamp
			'current_month_first_day_week_index' => $current_month_first_day_week_index, // 0 to 6
			'current_month_last_day' => $current_month_last_day, // 28, 29, 30 or 31
			'current_month_last_day_timestamp' => $current_month_last_day_timestamp, // unix timestamp
			'current_month_last_day_week_index' => $current_month_last_day_week_index, // 0 to 6

			'previous_month_index' => $previous_month_index, // 1 to 12
			'previous_month_year' => $previous_month_year, // example: 2015
			'previous_month_first_day' => 1, // 1
			'previous_month_first_day_timestamp' => $previous_month_first_day_timestamp, // unix timestamp
			'previous_month_first_day_week_index' => $previous_month_first_day_week_index, // 0 to 6
			'previous_month_last_day' => $previous_month_last_day, // 28, 29, 30 or 31
			'previous_month_last_day_timestamp' => $previous_month_last_day_timestamp, // unix timestamp
			'previous_month_last_day_week_index' => $previous_month_last_day_week_index, // 0 to 6
			'previous_month_offset' => $previous_month_offset, // how many days will be shown in current month

			'next_month_index' => $next_month_index, // 1 to 12
			'next_month_year' => $next_month_year, // example: 2015
			'next_month_first_day' => 1, // 1
			'next_month_first_day_timestamp' => $next_month_first_day_timestamp, // unix timestamp
			'next_month_first_day_week_index' => $next_month_first_day_week_index, // 0 to 6
			'next_month_last_day' => $next_month_last_day, // 28, 29, 30 or 31
			'next_month_last_day_timestamp' => $next_month_last_day_timestamp, // unix timestamp
			'next_month_last_day_week_index' => $next_month_last_day_week_index, // 0 to 6
			'next_month_offset' => $next_month_offset, // how many days will be shown in current month
		);

		set_transient($transient_string, $result, 0); // Never expires

		return $result;
	}

	public static function sanitize_week_index($index) {
		if ( $index == 0 ) {
			$index = 6;
		}

		return $index;
	}

	public static function sanitize_year_month($year = '', $month = '') {
		if ( empty($year) ) {
			$year = date('Y');
		}
		if ( empty($month) ) {
			$month = date('n');
		}

		return array(
			'year' => $year,
			'month' => $month,
		);
	}

	public static function get_year_month_param($year, $month) {
		$year = crb_request_param($year);
		if ( empty($year) ) {
			$year = date('Y');
		}
		$month = crb_request_param($month);
		if ( empty($month) ) {
			$month = date('n');
		}

		return array(
			'year' => $year,
			'month' => $month,
		);
	}

}