<?php

namespace App\Interfaces;

/**
*Interface PropertyContainerInterface
*/
interface PropertyContainerInterface
{
	/**
	* @param $propertyName
	* @param $value
	* @return mixed
	*/
	function add($propertyName, $value);

	/**
	* @param $propertyName
	* @param $value
	* @return mixed
	*/
	function set($propertyName, $value);

	/**
	* @param $propertyName
	* @return mixed
	*/
	function get($propertyName);

	/**
	* @param $propertyName
	* @return mixed
	*/
	function delete($propertyName);
}
