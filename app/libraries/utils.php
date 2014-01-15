<?php

class Utils
{
	public static function fatalError($message = false, $exception = false)
	{
		if (!$message)
		{
			$message = "An error occurred, please try again later";
		}

		static::logError($message . ' ' . $exception);		

		return View::make('error')->with('error', $message);
	}

	public static function logError($error, $context = 'PHP')
	{
		$count = Session::get('error_count', 0);
		Session::put('error_count', ++$count);
		if ($count > 100) return 'logged';

		$data = [
			'context' => $context,
			'user_id' => Auth::check() ? Auth::user()->id : 0,
			'url' => Input::get('url'),
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'ip' => Request::getClientIp(),
			'count' => Session::get('error_count', 0)
		];

		Log::error($error, $data);

		/*
		Mail::queue('emails.error', ['message'=>$error.' '.json_encode($data)], function($message)
		{			
			$message->to($email)->subject($subject);
		});		
		*/
	}

	public static function parseFloat($value)
	{
		$value = preg_replace('/[^0-9\.\-]/', '', $value);
		return floatval($value);
	}

	public static function formatPhoneNumber($phoneNumber) 
	{
	    $phoneNumber = preg_replace('/[^0-9a-zA-Z]/','',$phoneNumber);

	    if (!$phoneNumber) {
	    	return '';
	    }

	    if(strlen($phoneNumber) > 10) {
	        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
	        $areaCode = substr($phoneNumber, -10, 3);
	        $nextThree = substr($phoneNumber, -7, 3);
	        $lastFour = substr($phoneNumber, -4, 4);

	        $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
	    }
	    else if(strlen($phoneNumber) == 10) {
	        $areaCode = substr($phoneNumber, 0, 3);
	        $nextThree = substr($phoneNumber, 3, 3);
	        $lastFour = substr($phoneNumber, 6, 4);

	        $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
	    }
	    else if(strlen($phoneNumber) == 7) {
	        $nextThree = substr($phoneNumber, 0, 3);
	        $lastFour = substr($phoneNumber, 3, 4);

	        $phoneNumber = $nextThree.'-'.$lastFour;
	    }

	    return $phoneNumber;
	}

	public static function formatMoney($value, $currencyId)
	{
		$currency = Currency::remember(DEFAULT_QUERY_CACHE)->find($currencyId);		

		if (!$currency) 
		{
			$currency = Currency::remember(DEFAULT_QUERY_CACHE)->find(1);		
		}
		
		return $currency->symbol . number_format($value, $currency->precision, $currency->decimal_separator, $currency->thousand_separator);
	}

	public static function pluralize($string, $count) 
	{
		$string = str_replace('?', $count, $string);
		return $count == 1 ? $string : $string . 's';
	}

	public static function toArray($data)
	{
		return json_decode(json_encode((array) $data), true);
	}

	public static function toSpaceCase($camelStr)
	{
		return preg_replace('/([a-z])([A-Z])/s','$1 $2', $camelStr);
	}

	public static function timestampToDateTimeString($timestamp) {
		$timezone = Session::get(SESSION_TIMEZONE, DEFAULT_TIMEZONE);
		$format = Session::get(SESSION_DATETIME_FORMAT, DEFAULT_DATETIME_FORMAT);
		return Utils::timestampToString($timestamp, $timezone, $format);		
	}

	public static function timestampToDateString($timestamp) {
		$timezone = Session::get(SESSION_TIMEZONE, DEFAULT_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT, DEFAULT_DATE_FORMAT);
		return Utils::timestampToString($timestamp, $timezone, $format);
	}

	public static function dateToString($date) {		
		$dateTime = new DateTime($date); 		
		$timestamp = $dateTime->getTimestamp();
		$format = Session::get(SESSION_DATE_FORMAT, DEFAULT_DATE_FORMAT);
		return Utils::timestampToString($timestamp, false, $format);
	}

	public static function timestampToString($timestamp, $timezone = false, $format)
	{
		if (!$timestamp) {
			return '';
		}		
		$date = Carbon::createFromTimeStamp($timestamp);
		if ($timezone) {
			$date->tz = $timezone;	
		}
		if ($date->year < 1900) {
			return '';
		}
		return $date->format($format);		
	}	

	public static function toSqlDate($date)
	{
		if (!$date)
		{
			return null;
		}

		$timezone = Session::get(SESSION_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT);

		return DateTime::createFromFormat($format, $date, new DateTimeZone($timezone))->format('Y-m-d');
	}
	
	public static function fromSqlDate($date)
	{
		if (!$date || $date == '0000-00-00')
		{
			return '';
		}
		
		$timezone = Session::get(SESSION_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT);
		
		return DateTime::createFromFormat('Y-m-d', $date, new DateTimeZone($timezone))->format($format);
	}

	public static function today($formatResult = true)
	{	
		$timezone = Session::get(SESSION_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT);
		$date = date_create(null, new DateTimeZone($timezone));

		if ($formatResult) 
		{
			return $date->format($format);
		}
		else
		{
			return $date;
		}
	}

	public static function trackViewed($name, $type, $url = false)
	{
		if (!$url)
		{
			$url = Request::url();
		}
		
		$viewed = Session::get(RECENTLY_VIEWED);	
		
		if (!$viewed)
		{
			$viewed = [];
		}

		$object = new stdClass;
		$object->url = $url;
		$object->name = ucwords($type) . ': ' . $name;
		
		for ($i=0; $i<count($viewed); $i++)
		{
			$item = $viewed[$i];
			
			if ($object->url == $item->url)
			{
				array_splice($viewed, $i, 1);
				break;
			}
		}

		array_unshift($viewed, $object);
			
		if (count($viewed) > RECENTLY_VIEWED_LIMIT)
		{
			array_pop($viewed);
		}

		Session::put(RECENTLY_VIEWED, $viewed);
	}

	public static function processVariables($str)
	{
		if (!$str) {
			return '';
		}

		$variables = ['MONTH', 'QUARTER', 'YEAR'];
		for ($i=0; $i<count($variables); $i++)
		{
			$variable = $variables[$i];
			$regExp = '/:' . $variable . '[+-]?[\d]*/';
			preg_match_all($regExp, $str, $matches);
			$matches = $matches[0];
			if (count($matches) == 0) {
				continue;
			}
			foreach ($matches as $match) {
				$offset = 0;
				$addArray = explode('+', $match);
				$minArray = explode('-', $match);
				if (count($addArray) > 1) {
					$offset = intval($addArray[1]);
				} else if (count($minArray) > 1) {
					$offset = intval($minArray[1]) * -1;
				}				

				$val = Utils::getDatePart($variable, $offset);
				$str = str_replace($match, $val, $str);
			}
		}

		return $str;
	}

	private static function getDatePart($part, $offset)
	{
		$offset = intval($offset);
		if ($part == 'MONTH') {
			return Utils::getMonth($offset);
		} else if ($part == 'QUARTER') {
			return Utils::getQuarter($offset);
		} else if ($part == 'YEAR') {
			return Utils::getYear($offset);
		}
	}

	private static function getMonth($offset)
	{
		$months = [ "January", "February", "March", "April", "May", "June",
			"July", "August", "September", "October", "November", "December" ];

		$month = intval(date('n')) - 1;

		$month += $offset;
		$month = $month % 12;

		if ($month < 0)
		{
			$month += 12;
		}
		
		return $months[$month];
	}

	private static function getQuarter($offset)
	{
		$month = intval(date('n')) - 1;
		$quarter = floor(($month + 3) / 3);
		$quarter += $offset;
    	$quarter = $quarter % 4;
    	if ($quarter == 0) {
         	$quarter = 4;   
    	}
    	return 'Q' . $quarter;
	}

	private static function getYear($offset) 
	{
		$year = intval(date('Y'));
		return $year + $offset;
	}

	public static function getEntityName($entityType)
	{
		return ucwords(str_replace('_', ' ', $entityType));
	}

	public static function getClientDisplayName($model)
	{
		if ($model->client_name) 
		{
			return $model->client_name;
		}
		else if ($model->first_name || $model->last_name) 
		{
			return $model->first_name . ' ' . $model->last_name;
		}
		else
		{
			return $model->email;
		}
	}

	public static function encodeActivity($person = null, $action, $entity = null, $otherPerson = null)
	{
		$person = $person ? $person->getDisplayName() : '<i>System</i>';
		$entity = $entity ? '[' . $entity->getActivityKey() . ']' : '';
		$otherPerson = $otherPerson ? 'to ' . $otherPerson->getDisplayName() : '';

		return trim("$person $action $entity $otherPerson");
	}
	
	public static function decodeActivity($message)
	{
		$pattern = '/\[([\w]*):([\d]*):(.*)\]/i';
		preg_match($pattern, $message, $matches);

		if (count($matches) > 0)
		{
			$match = $matches[0];
			$type = $matches[1];
			$publicId = $matches[2];
			$name = $matches[3];

			$link = link_to($type . 's/' . $publicId, $name);
			$message = str_replace($match, "$type $link", $message);
		}

		return $message;
	}	
}