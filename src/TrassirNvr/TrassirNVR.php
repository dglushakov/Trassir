<?php

namespace dglushakov\Trassir\TrassirNvr;

use dglushakov\Trassir\NvrRequest\NvrRequest;

class TrassirNVR implements TrassirNvrInterface

{
    private
        $ip,
        $userName,
        $password,
        $passwordSDK,
        $sid,
        $sidSDK,
        $sidExpiresAt,
        $sidSDKExpiresAt,
        $stream_context;

    private $nvrRequest;
    private $users = [];


    /**
     * TrassirNVR constructor.
     * @param $ip
     * @param $userName
     * @param $password
     * @param $passwordSDK
     * @throws \Exception
     */
    public function __construct(
        string $ip = "127.0.0.1",
        string $userName = "Admin",
        string $password = "12345",
        string $passwordSDK = "")
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->ip = $ip;
        } else {
            throw new \Exception("Not valid IP");
        }

        $this->userName = $userName;
        $this->password = $password;
        $this->passwordSDK = $passwordSDK;
        $this->stream_context = stream_context_create(['ssl' => [  //разрешаем принимать самоподписанные сертификаты от NVR
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
            'verify_depth' => 0]]);

        $this->nvrRequest = new NvrRequest($this);
        $this->login();
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getUserName()
    {
        return $this->userName;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPasswordSDK()
    {
        return $this->passwordSDK;
    }

    public function getSid()
    {
        return $this->sid;
    }

    public function getStreamContext()
    {
        return $this->stream_context;
    }

    private function sidIsValid()
    {
        $result = false;
        if (($this->sid !== false) && $this->sidExpiresAt > new \DateTime()) {
            $result = true;
        }
        return $result;
    }

    private function sidSdkIsValid()
    {
        $result = false;
        if (($this->sidSDK !== false) && $this->sidSDKExpiresAt > new \DateTime()) {
            $result = true;
        }
        return $result;
    }


    public function login(): ?string
    {
        $result = false;
        if ($this->sidIsValid() && $this->sidSdkIsValid()) {
            return true;
        }

        $this->sid = false;
        $this->sidSDK = false;
        $this->sid = $this->nvrRequest->getSid();
        if ($this->sid) {
            $this->sidExpiresAt = new \DateTime();
        }
        $this->sidSDK = $this->nvrRequest->getSidSDK();
        if ($this->sidSDK) {
            $this->sidSDKExpiresAt = new \DateTime();
        }

        if ($this->sid != false && $this->sidSDK != false) {
            $result = true;
        }

        return $result;
    }


    /**
     * @return mixed
     */
    public function getSidSDK()
    {
        return $this->sidSDK;
    }

    public function getObjectsTree(): ?array
    {
        return $this->nvrRequest->getObjectsTree();
    }

    public function getNvrHealth(): ?array
    {
        return $this->nvrRequest->getHealth();
    }

    public function getChannels(): ?array
    {
        return $this->nvrRequest->getChannels();
    }

    public function getUsers(): ?array
    {
        if (empty($this->users)) {
            $this->users = $this->nvrRequest->getUsers();
        }
        return $this->users;
    }

    public function createUser(string $username, string $userPassword)
    {
        try {
            return $this->nvrRequest->createUser($username, $userPassword);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }
    }

    public function createGroup(string $groupName)
    {
        try {
            return $this->nvrRequest->createGroup($groupName);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }

    }

    public function deleteGroup(string $groupName)
    {
        try {
            return $this->nvrRequest->deleteUser($groupName);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }
    }

    public function deleteUser(string $userName)
    {
        try {
            return $this->nvrRequest->deleteUser($userName);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }
    }

    public function getScreenshot(string $channelGuid, \DateTime $timestamp)
    {
        return $this->nvrRequest->getScreenshot($channelGuid, $timestamp);
    }

    public function getNetworkInterfaces(){
        $networkInterfaceSettings=[];
        $interfaces = $this->nvrRequest->getNetworkInterfaces();

        foreach ($interfaces as $interface) {
            $networkInterfaceSettings[$interface] =  $this->nvrRequest->getNetworkInterfaceSettings($interface);
        }


        return $networkInterfaceSettings;
    }

    public function getHddInfo(){
        $hddInfo =[];
        $hddList = $this->nvrRequest->getHddList();
        $hddList = $hddList['subdirs'];

        foreach ($hddList as $hddName) {
            $hddInfo[$hddName] = $this->nvrRequest->getHddInfo($hddName);
        }
        return $hddInfo;
    }

    public function getCamerasInfo(){
        $camerasInfo =[];
        $camerasList = $this->nvrRequest->getCamerasList();
        $camerasList = $camerasList['subdirs'];

        foreach ($camerasList as $guid) {
            $camerasInfo[$guid] = $this->nvrRequest->getCameraInfo($guid);
        }
        return $camerasInfo;
    }

}