<?php

namespace Christiana\ApiBundle\Api;

use GuzzleHttp\Client;
use Christiana\ApiBundle\Api\Url\Url;
use Christiana\ApiBundle\Api\UrlBag\EndPointBag;
use Christiana\ApiBundle\Api\UrlBag\QueryBag;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\EventSubscriber\ApiEventSubscriber;
use App\Event\CallEvent;
use GuzzleHttp\RequestOptions;

/**
 * Api gestion
 */
class Api
{

	static public $timeout = 120;
	protected $cacheEnabled = true;
	protected $Cache;
	protected $EventDispatcher;
	static public $nbr = 0;
	static public $cacheBag = array();
	static public $parameters = array();

	const CACHE_IDS = [
		'evenbrite.events.index.showcase' //Index
	];

	public function __construct(CacheItemPoolInterface $Cache, EventDispatcherInterface $EventDispatcher)
	{
		$this->Cache = $Cache;
		$this->EventDispatcher = $EventDispatcher;
	}

	protected function request(Url $Url, $sslVerify = false, bool $json = true){

		$Client = new Client([
		    'base_uri' => $Url->start ?? static::BASE_API_URL,
		    'timeout' => self::$timeout,
		    'verify' => $sslVerify
		]);

		$Response = $Client->get((string) $Url);
		$Event = new CallEvent();
		$Event
			->setUrl($Url)
			->setMessage('Recherche des événements pour la page principale')
			->setResponse((string) $Response->getBody())
		;

		$this->EventDispatcher->dispatch(
			ApiEventSubscriber::REQUEST_EVENT, 
			$Event
		);

		if ($json)
			return $this->json_decode((string) $Response->getBody());
		else
			return (string) $Response->getBody();
	}

	protected function json_decode($json, bool $assoc = false)
	{
		return json_decode($json, $assoc);
	}

	protected function multipleRequests(array $urls, $sslVerify = false){
		if (count($urls) == 1) {
			return $this->request($urls[0], $sslVerify);
		}

		$Client = new Client([
		    'base_uri' => $urls[0]->start ?? static::BASE_API_URL,
		    'timeout' => self::$timeout,
		    'verify' => $sslVerify,
		    RequestOptions::SYNCHRONOUS => true
		]);

		$arr = [];

		foreach ($urls as $Url) {
			$response = $Client->get((string) $Url);
			$body = (string) $response->getBody();
			$arr[] = $this->json_decode($body);
			$this->EventDispatcher->dispatch(
				ApiEventSubscriber::REQUEST_EVENT, 
				(new CallEvent())
					->setUrl($Url)
					->setMessage($Url->getReason() ?? 'Looking for : '.(string)$Url)
					->setResponse($body)
			);
		}
		return $arr;
	}
}