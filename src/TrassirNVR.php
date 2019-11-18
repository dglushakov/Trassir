<?php

namespace dglushakov\Trassir;

class TrassirNVR
{
    private $ip, $userName, $password, $passwordSDK;

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
    }


}