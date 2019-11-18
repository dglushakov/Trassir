<?php

namespace dglushakov\Trassir;

class TrassirNVR implements TrassirCommandInterface
{
    private $ip, $userName, $password, $passwordSDK, $sid, $sidExpiresAt, $stream_context, $lastError;

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

    public function login(): ?string
    {
        if(isset($this->sid) && ($this->sid!==false) && isset($this->sidExpiresAt) && ($this->sidExpiresAt> new \DateTime()))
        {
            return $this->sid;
        }

        $this->sid = false;
        $url = 'https://' . trim($this->ip) . ':8080/login?username=' . trim($this->userName) . '&password=' . trim($this->password);
        if(false ===($responseJson_str = @file_get_contents($url, NULL, $this->stream_context))){
            $this->lastError = $this->sid= "Host " . $this->ip . " is offline";
            return $this->sid;
        }
        $server_auth = json_decode($responseJson_str, true); //переводим JSON в массив
        if ($server_auth['success'] == 1) {
            $this->sid = $server_auth['sid'];
            $this->sidExpiresAt = new \DateTime();
            $this->sidExpiresAt->modify('+15 minutes');
        } else {
             $this->lastError= "Wrong Username or Password";
        }

        return $this->sid;
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
        $objects=[];
        $url = 'https://' . trim($this->ip) . ':8080/objects/?password=' . trim($this->passwordSDK);;
        $responseJson_str = file_get_contents($url, NULL, $this->stream_context);
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $objects = json_decode($responseJson_str, true);

        if ($objects['success'] == 0 && $objects['error_code']== "no session") {
            $this->lastError = "Wrong SDK password";
            $objects = null;
        }

        return $objects;
    }

}