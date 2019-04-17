<?php

namespace App\Controller;

use App\Entity\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Stopwatch\Stopwatch;

class RequestController extends AbstractController
{
    public static function startStopWatch($eventName): Stopwatch
    {
        $stopWatch = new Stopwatch();
        // start event
        $stopWatch->start($eventName);
        return $stopWatch;
    }

    /**
     * @param $url
     * @param $httpCode
     * @param $executedIn
     */
    public static function logRequest($url, $httpCode, Stopwatch $stopWatch, $eventName, ObjectManager $em)
    {
        // last attribute date is generated here
        $request = new Request();
        $request->setHttpCode($httpCode);
        // compute execution time
        $event = $stopWatch->stop($eventName);
        $request->setExecutedIn($event->getDuration());
        $request->setUrl($url);

        $dt = new \DateTime();
        $request->setDate($dt->format('Y-m-d H:i:s O'));

        $em->persist($request);
        $em->flush();
    }
}
