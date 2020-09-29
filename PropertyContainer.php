<?php

namespace App\Classes;

use App\Interfaces\PropertyContainerInterface;

/**
* @package none
*
* @url https://ru.wikipedia.org/wiki/Контейнер_свойств_(шаблон_проектирования)
*/
class PropertyContainer implements PropertyContainerInterface
{
	/**
	* @var
	*/
	private $propertyContainer= [];

	public function add($propertyName, $value)
	{
		$this->propertyContainer[$propertyName] = $value;
	}

	public function set($propertyName, $value)
	{
		if(!isset($this->propertyContainer[$propertyName])) {
			throw new \Exception("Свойство $propertyName не найдено");
		}

		$this->propertyContainer[$propertyName] = $value;
	}

	public function get($propertyName)
	{
		return $this->propertyContainer[$propertyName] ?? null;
	}

    /**
     * @param $propertyName
     * @return mixed|void
     */
	public function delete($propertyName)
	{
		unset($this->propertyContainer[$propertyName]);
	}

	function getAll()
	{
		return $this->propertyContainer;
	}
}
