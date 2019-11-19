<?php


namespace dglushakov\Trassir\MediaGrabber;

use dglushakov\Trassir\TrassirNvr\TrassirNvrInterface;

class TrassirMediaGrabber implements MediaGrabberInterface
{
    public static $lastError;

    public static function getScreenShotUrl(TrassirNvrInterface $trassirNvr, $channelNumber=0, \DateTime $timestamp = null) :?string
    {
        if(!$timestamp){
            $timestamp = new \DateTime();
        }
        $timestamp = $timestamp->getTimestamp();

        $channels = $trassirNvr->getChannels();
        if ($channelNumber >= count($channels)) {
            self::$lastError = "Server have only ". count($channels)." channels. Channel ".$channelNumber." is out of range";
            return false;
        }

        $screenShotUrl = 'https://' . trim($trassirNvr->getIp()) . ':8080/screenshot/'
            .$channels[$channelNumber]->getGuid()
            .'?timestamp='.$timestamp
            .'&sid=' . trim($trassirNvr->getSid());

        return  $screenShotUrl;
    }

}