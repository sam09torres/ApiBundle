<?php

namespace Christiana\ApiBundle\Api\Url;

use Christiana\ApiBundle\Api\UrlBag\EndPointBag;

/**
 * 
 */
final class EndPoint
{
	private $rawEndPoint;
	private $endPoint; // (string)
	private $EndPointBag;
	public function __construct($rawEndPoint, $EndPointBag = null)
	{
		$this->rawEndPoint = $rawEndPoint;
		$this->EndPointBag = $EndPointBag;
	}

	public function __toString()
	{
		return $this->isResolved() ? $this->endPoint : $this->resolve();
	}

	public function getEndPointBag() : EndPointBag{
		return $this->EndPointBag;
	}

	public function setEndPointBag($EndPointParameters = array()){
		if (($EndPointParameters instanceof EndPointBag)) {
			$this->EndPointBag = $EndPointParameters;
		}else if(is_array($EndPointParameters)){
			$this->EndPointBag = new QueryBag($EndPointParameters);
		}else{
			$this->EndPointBag = new QueryBag();
		}

		$this->endPoint = null;

		return $this;
	}

	public function getRawEndPoint()
	{
		return $rawEndPoint;
	}

	public function setRawEndPoint($rawEndPoint)
	{
		return $this->rawEndPoint = $rawEndPoint;
	}

	public function getEndPoint()
	{
		return $endPoint;
	}

	public function setEndPoint($endPoint)
	{
		return $this->endPoint = $endPoint;
	}

	public function isResolved()
	{
		return ($this->endPoint != null);
	}

	public function resolve(){

		if (count($this->EndPointBag) == 0) {
			$this->endPoint = $this->rawEndPoint;
		}else{

			$pattern = '/%([\w]+)%/';

			preg_match_all($pattern, $this->rawEndPoint, $out);
			//$out[0] : Trouvailles / $out[1] : recherches
			$raw = $this->rawEndPoint;

			array_map(function($unresolvedParam) use(&$raw) {
				$key = str_replace('%', '', $unresolvedParam); //Resolved Param for Key
				$raw = str_replace($unresolvedParam, $this->getParameter($key), $raw);
			}, $out[0]);

			$this->endPoint = $raw;
		}

		
		return $this->endPoint;
	}

	public function getParameter($parameter)
	{
		return $this->EndPointBag->get($parameter);
	}

	public function setParameter($parameter , $value)
	{
		$this->EndPointBag->set($parameter, $value);
	}
}