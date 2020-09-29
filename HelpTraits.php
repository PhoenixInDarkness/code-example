<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use Jenssegers\Date\Date;

trait HelpTraits {
	/**
	* Функция получения человекочитаемой даты на русском языке
	* @param mixed $date
	* @return
	*/
	protected static function getRuHumansDate($date)
	{
		Date::setLocale('ru');
		$ruDate = Date::parse($date)->diffForHumans();

		return $ruDate;
	}

	/**
	 * Функция локализации месяцев
	 *
	 * @param mixed $date
	 * @return
	 */
	protected static function getRuDate($date)
	{
		$arrSearch = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		$arrReplace = ['Янв', 'Фев', 'Мар', 'Апр', 'Мая', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
		$ruDate = str_replace($arrSearch, $arrReplace,$date);
		return $ruDate;
	}

	/**
	 * Функция склонения слов
	 *
	 * @param mixed $digit
	 * @param mixed $expr
	 * @param bool $onlyword
	 * @return
	 */
	protected static function declension($digit,$expr,$onlyword=false)
	{
		if(!is_array($expr)) $expr = array_filter(explode(' ', $expr));
		if(empty($expr[2])) $expr[2]=$expr[1];
		$i=preg_replace('/[^0-9]+/s','',$digit)%100;
		if($onlyword) $digit='';
		if($i>=5 && $i<=20) $res=$digit.' '.$expr[2];
		else
		{
			$i%=10;
			if($i==1) $res=$digit.' '.$expr[0];
			elseif($i>=2 && $i<=4) $res=$digit.' '.$expr[1];
			else $res=$digit.' '.$expr[2];
		}
		return trim($res);
	}

    /**
     * Функция получения превью текста
     * 
     * @param $text
     * @return false|string
     */
	protected function textTrim($text)
	{
		for ($i = 1; $i <= 5; $i++) {
			$text = rtrim($text, '<div><br></div>');
			$text = rtrim($text, '&nbsp; ');
			$text = trim($text);
			$text = rtrim($text, '<div><br></div>');

			$string = substr($text, -5);
			if($string == "<div>") $text = substr($text, 0, -5);
		}

		return $text;
	}

}
