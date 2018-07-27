<?php

namespace Christiana\ApiBundle\Api\Url;

use Christiana\ApiBundle\Api\Url\EndPoint;
use Christiana\ApiBundle\Api\Url\Query;

/**
 * 
 */
final class Url
{

	private $start = "";
	private $rawUrl = "";
	private $url;
	private $reason;
	private $EndPoint;
	private $Query;

	function __construct(string $start = "", EndPoint $EndPoint = null, Query $Query = null)
	{
		$this->url = $this->start = $start;
		$this->EndPoint = $EndPoint;
		$this->Query = $Query;
	}

	public function __toString()
	{
		return $this->url;
	}

	public function getUrl() : string {
		return $this->$url;
	}

	public function setUrl($url) : string {
		$this->url = $url;
		return $this;
	}

	public function getReason() : string {
		return $this->reason;
	}

	public function setReason(string $reason){
		$this->reason = $reason;
		return $this;
	}

	public function getEndPoint() : Endpoint {
		return $this->EndPoint;
	}

	public function setEndPoint(Endpoint $EndPoint){

		$this->EndPoint = $EndPoint;
		return $this;
	}

	public function getQuery() : Query{
		return $this->Query;
	}

	public function setQuery(Query $Query){
		$this->Query = $Query;
		return $this;
	}

	public function isResolved() : bool{
		return $this->url != null;
	}

	public function resolve() : string
	{
		$this->url = $this->start.$this->EndPoint.'?'.$this->Query;
		return $this->url;
	}

	
}