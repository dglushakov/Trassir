<?php


namespace dglushakov\Trassir\TrassirNvr;

Interface TrassirNvrInterface {

    public function login(): ?string;
    public function getIp();
    public function getUserName();
    public function getPassword();
    public function getPasswordSDK();
    public function getSid();
    public function getSidSDK();
    public function getObjectsTree(): ?array;
    public function getNvrHealth(): ?array;
    public function getChannels(): ?array;
    public function getUsers(): ?array;
    public function createGroup(string $groupName);
    public function deleteGroup(string $groupName);
    public function createUser(string $userName, string $userPassword);
    public function deleteUser(string $userName);
    public function getScreenshot(string $channelGuid, \DateTime $timestamp);

    //getVideuUrl(container? )

}
