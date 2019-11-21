<?php

namespace dglushakov\Trassir\TrassirNvr;


use dglushakov\Trassir\TrassirChannel\TrassirChannel;

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
        $lastError,
        $channels;

    public function getIp(){
        return $this->ip;
    }
    public function getSid(){
        return $this->sid;
    }

    public function getStreamContext(){
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


    private function sidIsValid(){
        $result = false;
        if(($this->sid!==false) && $this->sidExpiresAt> new \DateTime()){
            $result = true;
        }
        return $result;
    }

    private function sidSdkIsValid(){
        $result = false;
        if(($this->sidSDK!==false) && $this->sidSDKExpiresAt> new \DateTime()){
            $result = true;
        }
        return $result;
    }


    private function login(): ?string
    {
        $result = false;
        if($this->sidIsValid() && $this->sidSdkIsValid())
        {
            return true;
        }

        $this->sid = false;
        $this->sidSDK = false;

        $sidRequestUrl = 'https://' . trim($this->ip) . ':8080/login?username=' . trim($this->userName) . '&password=' . trim($this->password);
        if(false ===($responseJson_str = @file_get_contents($sidRequestUrl, NULL, $this->stream_context))){
            $this->lastError = "Host " . $this->ip . " is offline";
            return false;
        }
        $server_auth = json_decode($responseJson_str, true);
        if ($server_auth['success'] == 1) {
            $this->sid = $server_auth['sid'];
            $this->sidExpiresAt = new \DateTime();
            $this->sidExpiresAt->modify('+15 minutes');
        } else {
             $this->lastError= "Wrong Username or Password. Cant get sid";
        }

        $sidSdkRequestUrl = 'https://' . trim($this->ip) . ':8080/login?password=' . trim($this->passwordSDK);
        if(false ===($responseJson_str = @file_get_contents($sidSdkRequestUrl, NULL, $this->stream_context))){
            $this->lastError = "Host " . $this->ip . " is offline";
            return false;
        }
        $server_auth = json_decode($responseJson_str, true);
        if ($server_auth['success'] == 1) {
            $this->sidSDK = $server_auth['sid'];
            $this->sidSDKExpiresAt = new \DateTime();
            $this->sidSDKExpiresAt->modify('+15 minutes');
        } else {
            $this->lastError= "Wrong Username or Password. Cant get SDK password";
        }

        if($this->sid!=false && $this->sidSDK!=false) {
            $result = true;
        }

        return $result;
    }

    private function isHostOnline(): bool
    {
        $online = false;
        $url = 'http://' . trim($this->ip) . ':80';
        $data = @file_get_contents($url, NULL, $this->stream_context );
        if (!$data) {
            $this->lastError = "Host ".$this->ip." is offline";
        } else {
            $online = true;
        }

        return $online;
    }

    public function getObjectsTree(): ?array
    {
        if($this->isHostOnline() && $this->login()){
        $url = 'https://' . trim($this->ip) . ':8080/objects/?sid=' . trim($this->sidSDK);;
        $responseJson_str = file_get_contents($url, NULL, $this->stream_context);
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $objects = json_decode($responseJson_str, true);

        if ($objects['success'] == 0 && $objects['error_code']== "no session") {
            $this->lastError = "Wrong SDK password";
            $objects = null;
        }

        return $objects;
        } else {
            return null;
        }
    }

    public function getNvrHealth(): ?array
    {
        if(!$this->login()){
            return null;
        }
        $url = 'https://' . trim($this->ip) . ':8080/health?sid=' . trim($this->sid);
        $responseJson_str = file_get_contents($url, null, $this->stream_context);
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $server_health = json_decode($responseJson_str, true);

        return $server_health;
    }

    public function getChannels(): ?array
    {
        if(!$this->login()){
            return null;
        }
        $url = 'https://' . trim($this->ip) . ':8080/channels?sid=' . trim($this->sid);
        $responseJson_str = file_get_contents($url, null, $this->stream_context);
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $channels = json_decode($responseJson_str, true);

        foreach ($channels['channels'] as $channel) {
            $NvrChannel = new TrassirChannel($channel['guid'], $channel['name'], $channel['rights'], $channel['codec']);
            $this->channels[] = $NvrChannel;
        }

        return $this->channels;
    }

    public function getServerSettings(): ?array {
        if(!$this->login()){
            return null;
        }
        $serverSettings=[];
        $url = 'https://' . trim($this->ip) . ':8080/settings/?sid=' . trim($this->sid);
        $responseJson_str = file_get_contents($url, null, $this->stream_context);
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $serverSettings = json_decode($responseJson_str, true);
        return $serverSettings;
    }

    public function getUsers() {
        if(!$this->login()){
            return null;
        }
        $Users=[];
        $url = 'https://' . trim($this->ip) . ':8080/settings/users/?sid=' . trim($this->sid);
        $responseJson_str = file_get_contents($url, null, $this->stream_context);
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $Users = json_decode($responseJson_str, true);
        $this->trassirUsers = $Users['subdirs'];
        return $this->trassirUsers;
    }

}