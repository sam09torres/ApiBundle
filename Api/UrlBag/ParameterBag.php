<?php

namespace Christiana\ApiBundle\Api\UrlBag;

use Christiana\ApiBundle\Api\Exception\ParameterNotFoundException;

/**
 * 
 */
class ParameterBag implements \Countable, \IteratorAggregate
{
	
	protected  $parameters = array();

	function __construct(array $parameters = array())
	{
		$this->parameters = $parameters;
	}

	/*public function __set($property, $value)
	{
		return $this->set($property, $value);
	}

	public function __get($property)
	{
		return $this->get($property);
	}*/

	public function clear($parameter)
	{
		$this->parameters = array();
	}

	public function add($parameter, $value)
	{
		$this->parameters[$parameter] = $value;
	}

	public function all()
	{
		return $this->parameters[$parameter];
	}

	public function get($parameter, $default = null)
	{
		if (!($this->has($parameter)) && $default == null) {
			throw new ParameterNotFoundException(sprintf('The parameter "%s" doesn\'t exist in the parameters bag', $parameter));
		}


		return $this->parameters[$parameter];// ?? $default;
	}

	public function remove ($parameter)
	{
		if (!$this->has($parameter)) {
			throw new ParameterNotFoundException(sprintf('The parameter "%s" doesn\'t exist in the parameters bag', $parameter));
		}

		unset($this->parameters[$parameter]);
	}


	public function has($parameter)
	{
		return array_key_exists($parameter, $this->parameters);
	}

	public function count()
	{
		return count($this->parameters);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->parameters);
	}
}