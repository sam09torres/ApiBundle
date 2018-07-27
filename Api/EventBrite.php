<?php

namespace Christiana\ApiBundle\Api;

use Christiana\ApiBundle\Api\Api;
use Service\Event\Event;
use GuzzleHttp\Client;
use Christiana\ApiBundle\Api\Url\Url;
use Christiana\ApiBundle\Api\Url\EndPoint;
use Christiana\ApiBundle\Api\UrlBag\EndPointBag;
use Christiana\ApiBundle\Api\Url\Query;
use Christiana\ApiBundle\Api\UrlBag\QueryBag;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * EventBrite
 */
final class EventBrite extends Api
{
	const BASE_API_URL = 'https://www.eventbriteapi.com/v3/';
	const ORGANIZER_ID = '12748783134';
	const TOKEN = 'YI2T6Q7HJLATCB5SWWNY';

	function __construct(CacheItemPoolInterface $Cache, EventDispatcherInterface $EventDispatcher)
	{
		parent::__construct($Cache, $EventDispatcher);
	}

	public function getName()
	{
		return self::class;
	}

	public function restoreFromCache(bool $flag)
	{
		$this->cacheEnabled = $flag;
	}

	public function getEvents($options = array(), $pagination = false, $withVenue = false, $organizerId = self::ORGANIZER_ID)
	{
		$CachedEvents = $this->Cache->getItem('evenbrite.events.index.showcase');

		if ($this->cacheEnabled && $CachedEvents->isHit()) {
		//if (!($this->cacheEnabled && $CachedEvents->isHit())) {
			$EventsList = $CachedEvents->get();
		}else{
			$Url = new Url(
				self::BASE_API_URL,
				new EndPoint(
					'organizers/%organizerId%/events',
					new EndPointBag([
						'organizerId' => $organizerId
					])
				),new Query(
					new QueryBag([
						'token' => $options['token'] ?? self::TOKEN,
						'order_by' => 'start_desc'
					])
				)
			);

			$Url->resolve();

			$EventsList = $this->request($Url);

			$CachedEvents->set($EventsList);
			$CachedEvents->expiresAt(new \DateTime('tomorrow'));
			$this->Cache->save($CachedEvents);
		}

		/*if ($withVenue) {
			foreach ($EventsList->events as $Event) {
				$this->setVenue($Event);
			}
		}*/

		$this->Cache->prune(); //Delete Expired Cache Items
		
		if ($pagination) {
			return $EventsList;
		}


		return $EventsList->events;
	}

	public function getEvent($id)
	{
		$Url = new Url(
			self::BASE_API_URL,
			new EndPoint(
				'events/%event_id%',
				new EndPointBag([
					'event_id' => $id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		);
		$Url->resolve();

		$Event = $this->request($Url);

		//With ID send a multiple request

		$this->setAll($Event);

		/*$this->setOrganizer($Event);
		$this->setVenue($Event);
		$this->setCategory($Event);

		$Event->subcategory_id == null ?: $this->setSubcategory($Event);*/

		return $Event;
	}

	public function getOrganizer($id = self::ORGANIZER_ID)
	{
		$Url = new Url(
			self::BASE_API_URL,
			new EndPoint(
				'organizers/%organizer_id%',
				new EndPointBag([
					'organizer_id' => $id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		);

		$Url->resolve();

		return $this->request($Url);
	}

	private function setAll($Event)
	{
		$urls = [];

		//ORGANIZER
		$Url = (new Url(
			self::BASE_API_URL,
			new EndPoint(
				'organizers/%organizer_id%',
				new EndPointBag([
					'organizer_id' => $Event->organizer_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		))->setReason("Requesting Organizer for Event \"".$Event->name->text."\"");
		$Url->resolve();
		$urls[] = $Url;

		//CATEGORY
		$Url = (new Url(
			self::BASE_API_URL,
			new EndPoint(
				'categories/%category_id%',
				new EndPointBag([
					'category_id' => $Event->category_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		))->setReason("Requesting Category for Event \"".$Event->name->text."\"");
		$Url->resolve();
		$urls[] = $Url;

		//SUBCATEGORY
		if($Event->subcategory_id != null){
			$urls[] = (new Url(
				self::BASE_API_URL,
				new EndPoint(
					'subcategories/%subcategory_id%',
					new EndPointBag([
						'subcategory_id' => $Event->subcategory_id
					])
				),new Query(
					new QueryBag([
						'token' => $options['token'] ?? self::TOKEN
					])
				)
			))->setReason("Requesting Subcategory for Event \"".$Event->name->text."\"");
			$Url->resolve();
			$urls[] = $Url;
		}

		//VENUE
		$Url = (new Url(
			self::BASE_API_URL,
			new EndPoint(
				'venues/%venue_id%',
				new EndPointBag([
					'venue_id' => $Event->venue_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		))->setReason("Requesting Venue for Event \"".$Event->name->text."\"");
		$Url->resolve();
		$urls[] = $Url;

		$responses = $this->multipleRequests($urls);

		if ($Event->subcategory_id != null) {
			$Event->{'organizer'} = $responses[0];
			$Event->{'category'} = $responses[1];
			$Event->{'subcategory'} = $responses[2];
			$Event->{'venue'} = $responses[3];
		} else {
			$Event->{'organizer'} = $responses[0];
			$Event->{'category'} = $responses[1];
			$Event->{'venue'} = $responses[2];
		}
		
	}

	private function setLogo($Event)
	{
		return $Event;
	}

	private function setOrganizer($Event)
	{
		$Url = new Url(
			self::BASE_API_URL,
			new EndPoint(
				'organizers/%organizer_id%',
				new EndPointBag([
					'organizer_id' => $Event->organizer_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		);

		$Url->resolve();
		$Event->{'organizer'} = $this->request($Url);
		return $Event;
	}

	private function setCategory($Event)
	{
		$Url = new Url(
			self::BASE_API_URL,
			new EndPoint(
				'categories/%category_id%',
				new EndPointBag([
					'category_id' => $Event->category_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		);

		$Url->resolve();
		$Event->{'category'} = $this->request($Url);;
		return $Event;
	}

	private function setSubcategory($Event)
	{
		$Url = new Url(
			self::BASE_API_URL,
			new EndPoint(
				'subcategories/%subcategory_id%',
				new EndPointBag([
					'subcategory_id' => $Event->subcategory_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		);

		$Url->resolve();
		$Event->{'subcategory'} = $this->request($Url);
		return $Event;
	}

	private function setVenue($Event)
	{
		$Url = new Url(
			self::BASE_API_URL,
			new EndPoint(
				'venues/%venue_id%',
				new EndPointBag([
					'venue_id' => $Event->venue_id
				])
			),new Query(
				new QueryBag([
					'token' => $options['token'] ?? self::TOKEN
				])
			)
		);

		$Url->resolve();
		$Event->{'venue'} = $this->request($Url);
		return $Event;
	}
}