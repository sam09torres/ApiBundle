<?php

namespace Christiana\ApiBundle\Api\Url;

use Christiana\ApiBundle\Api\UrlBag\QueryBag;

/**
 * 
 */
final class Query
{

	private $QueryBag;
	private $query;

	function __construct($QueryBag)
	{		
		$this->setQueryBag($QueryBag);
	}

	public function __toString()
	{
		return $this->isResolved() ? $this->query : $this->resolve();
	}

	public function getQueryBag() : QueryBag{
		return $this->QueryBag;
	}

	public function setQueryBag($QueryParameters = array()){
		if (($QueryParameters instanceof QueryBag)) {
			$this->QueryBag = $QueryParameters;
		}else if(is_array($QueryParameters)){
			$this->QueryBag = new QueryBag($QueryParameters);
		}else{
			$this->QueryBag = new QueryBag();
		}

		$this->resolvedQuery = null;

		return $this;
	}

	private function isResolved(){
		return $this->query != null;
	}

	public function resolve()
	{
		$i = 0;
		$count = count($this->QueryBag);
		if ($count == 0)
			return null;

		$this->query = "";

		foreach ($this->QueryBag as $parameter => $value) {
			$i++;
			$this->query.= "${parameter}=${value}";
			if ($count != $i) {
				$this->query .='&';
			}
		}

		return $this->query;
	}
}