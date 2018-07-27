<?php

namespace Christiana\ApiBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\EventSubscriber\ApiEventSubscriber;
use Christiana\ApiBundle\Api\Api;

class ApiCollector extends DataCollector
{

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'requests' => ApiEventSubscriber::$calls,
        );
    }

    public function reset()
    {
        $this->data = array();
    }

    public function getName()
    {
        return 'custom.api.collector';
    }

    public function getRequests()
    {
        return $this->data['requests'];
    }
}