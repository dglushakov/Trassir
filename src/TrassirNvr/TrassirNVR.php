<?php

namespace dglushakov\Trassir\TrassirNvr;

use dglushakov\Trassir\NvrRequest\NvrRequestController;

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

    private $requestController;
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

        $this->requestController = new NvrRequestController($this);
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
        $this->sid = $this->requestController->getSid();
        if ($this->sid) {
            $this->sidExpiresAt = new \DateTime();
        }
        $this->sidSDK = $this->requestController->getSidSDK();
        if ($this->sidSDK) {
            $this->sidSDKExpiresAt = new \DateTime();
        }

        if ($this->sid != false && $this->sidSDK != false) {
            $result = true;
        }

        return $result;
    }

    private function isHostOnline(): bool
    {
        $online = false;
        $url = 'http://' . trim($this->ip) . ':80';
        $data = @file_get_contents($url, NULL, $this->stream_context);
        if (!$data) {
            $this->lastError = "Host " . $this->ip . " is offline";
        } else {
            $online = true;
        }

        return $online;
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
        return $this->requestController->getObjectsTree();
    }

    public function getNvrHealth(): ?array
    {
        return $this->requestController->getHealth();
    }

    public function getChannels(): ?array
    {
        return $this->requestController->getChannels();
    }

    public function getUsers(): ?array
    {
        if (empty($this->users)) {
            $this->users = $this->requestController->getUsers();
        }
        return $this->users;
    }

    public function createUser(string $username, string $userPassword)
    {
        try {
            return $this->requestController->createUser($username, $userPassword);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }
    }

    public function createGroup(string $groupName)
    {
        try {
            return $this->requestController->createGroup($groupName);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }

    }

    public function deleteUser(string $userName)
    {
        try {
            return $this->requestController->deleteUser($userName);
        } catch (\Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }
    }

    public function getScreenshot(string $channelGuid, \DateTime $timestamp)
    {
        return $this->requestController->getScreenshot($channelGuid, $timestamp);
    }
}