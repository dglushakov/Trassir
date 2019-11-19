<?php

namespace dglushakov\Trassir\MediaGrabber;

use dglushakov\Trassir\TrassirNvrInterface;

interface MediaGrabberInterface {
    public static function getScreenShotUrl(TrassirNvrInterface $trassirNvr, $channel, \DateTime $timestamp): ?string;
    //public function getVideoStream();
//    public function getAudioStream();  ?
    //public function getVideoArchive();
}
