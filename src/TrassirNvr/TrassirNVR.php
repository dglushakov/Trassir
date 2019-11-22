<?php

namespace dglushakov\Trassir\TrassirNvr;

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
        $stream_context,
        $lastError;

    public function getIp()
    {
        return $this->ip;
    }

    public function getSid()
    {
        return $this->sid;
    }

    public function getStreamContext()
    {
        return $this->stream_context;
    }

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

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

        $sidRequestUrl = 'https://' . trim($this->ip) . ':8080/login?username=' . trim($this->userName) . '&password=' . trim($this->password);
        if (false === ($responseJson_str = @file_get_contents($sidRequestUrl, NULL, $this->stream_context))) {
            $this->lastError = "Host " . $this->ip . " is offline";
            return false;
        }
        $server_auth = json_decode($responseJson_str, true);
        if ($server_auth['success'] == 1) {
            $this->sid = $server_auth['sid'];
            $this->sidExpiresAt = new \DateTime();
            $this->sidExpiresAt->modify('+15 minutes');
        } else {
            $this->lastError = "Wrong Username or Password. Cant get sid";
        }

        $sidSdkRequestUrl = 'https://' . trim($this->ip) . ':8080/login?password=' . trim($this->passwordSDK);
        if (false === ($responseJson_str = @file_get_contents($sidSdkRequestUrl, NULL, $this->stream_context))) {
            $this->lastError = "Host " . $this->ip . " is offline";
            return false;
        }
        $server_auth = json_decode($responseJson_str, true);
        if ($server_auth['success'] == 1) {
            $this->sidSDK = $server_auth['sid'];
            $this->sidSDKExpiresAt = new \DateTime();
            $this->sidSDKExpiresAt->modify('+15 minutes');
        } else {
            $this->lastError = "Wrong Username or Password. Cant get SDK password";
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
        $request = new TrassirRequest($this, 'OBJECTS_TREE');
        return $request->execute();
    }

    public function getNvrHealth(): ?array
    {
        $request = new TrassirRequest($this, 'HEALTH');
        return $request->execute();
    }

    public function getChannels(): ?array
    {
        $request = new TrassirRequest($this, 'CHANNELS_LIST');
        $result = $request->execute();
        $channels = $result['channels'];
        return $channels;
    }

    public function getServerSettings(): ?array
    {
        $request = new TrassirRequest($this, 'SERVER_SETTINGS');
        return $request->execute();
    }



}