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

    public static function getVideoStreamUrl(TrassirNvrInterface $trassirNvr, $channelNumber=0, \DateTime $timestamp = null){
        var_dump($timestamp);
        $channels = $trassirNvr->getChannels();

        //https://192.18.1.20:8080/get_video?channel=CKq5LLiO&container=mjpeg&quality=80&stream=main&framerate=1000&sid=WP6IRcrQ
        //https://10.17.26.33:8080/get_video?channel=XlA6pfs6&container=mjpeg&quality=80&stream=mainframerate=1000&sid=jLsivwZ1

        $videoToken = self::getVideoToken($trassirNvr, $channelNumber, $timestamp);
        var_dump($videoToken);


        $videoStreamUrl = 'http://' . trim($trassirNvr->getIp()) . ':555/'.$videoToken;

       // var_dump($videoStreamUrl);
        return  $videoStreamUrl;

    }

    private static function getVideoToken(TrassirNvrInterface $trassirNvr, $channelNumber=0, \DateTime $timestamp = null) {
        $token = false;

        $channels = $trassirNvr->getChannels();
        $tokenUrl = 'https://' . trim($trassirNvr->getIp()) . ':8080/get_video?channel='
            .$channels[$channelNumber]->getGuid()
            .'&container=jpeg'
            .'&quality=80'
            .'&stream=main'
            .'&framerate=1000'
            .'&sid=' . trim($trassirNvr->getSid());

        $responseJson_str = file_get_contents($tokenUrl, null, $trassirNvr->getStreamContext());
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        if ($comment_position) {
            $responseJson_str = substr($responseJson_str, 0, $comment_position);
        }
        $token = json_decode($responseJson_str, true);
        return $token['token'];
    }

}