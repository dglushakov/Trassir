<?php


namespace dglushakov\Trassir\TrassirNvr;

Interface TrassirNvrInterface {

    //public function login(): ?string;
    public function getIp();
    public function getSid();
    public function getSidSDK();
    public function getObjectsTree(): ?array;
    public function getNvrHealth(): ?array;
    public function getChannels(): ?array;

}
