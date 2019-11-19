<?php


namespace dglushakov\Trassir;

Interface TrassirNvrInterface {

    //public function login(): ?string;
    public function getIp();
    public function getSid();
    public function getObjectsTree(): ?array;
    public function getNvrHealth(): ?array;
    public function getChannels(): ?array;

}
