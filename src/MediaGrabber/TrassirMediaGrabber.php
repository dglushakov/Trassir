<?php


namespace dglushakov\Trassir\MediaGrabber;

use dglushakov\Trassir\TrassirNvrInterface;

class TrassirMediaGrabber implements MediaGrabberInterface
{
    public static $lastError;

    public static function getScreenShotUrl(TrassirNvrInterface $trassirNvr, $channelNumber=0, $timestamp=0) :?string
    {
        $channels = $trassirNvr->getChannels();
        if ($channelNumber >= count($channels)) {
            self::$lastError = "Server have only ". count($channels)." channels. Channel ".$channelNumber." is out of range";
            return false;
        }

        $screenShotUrl = 'https://' . trim($trassirNvr->getIp()) . ':8080/screenshot/'
            .$channels[$channelNumber]->getGuid()
            .'?timestamp='.$timestamp->getTimestamp()
            .'&sid=' . trim($trassirNvr->getSid());

        return  $screenShotUrl;
    }

}