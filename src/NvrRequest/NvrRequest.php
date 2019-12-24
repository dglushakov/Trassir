<?php


namespace dglushakov\Trassir\NvrRequest;


use dglushakov\Trassir\TrassirNvr\TrassirNvrInterface;

class NvrRequest
{
    protected $trassirNvr;
    protected $requestUrlGenerator;

    public function __construct(TrassirNvrInterface $trassirNvr)
    {
        $this->trassirNvr = $trassirNvr;
        $this->requestUrlGenerator = new RequestUrlGenerator($this->trassirNvr);
    }

    public function setNvr(TrassirNvrInterface $trassirNvr)
    {
        $this->trassirNvr = $trassirNvr;
    }

    private function executeRequest(string $requestUrl)
    {
        $responseJson_str = @file_get_contents($requestUrl, null, $this->trassirNvr->getStreamContext());//TODO переделать nvr->login и перенести исключения туда if (!$responseJson_str)

        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        if (!$comment_position) {
            $comment_position = strlen($responseJson_str);
        }
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $responseJson_str = json_decode($responseJson_str, true);

        return $responseJson_str;
    }

    public function getSid()
    {
        $sid = false;
        $result = $this->executeRequest($this->requestUrlGenerator->getSidUrl());
        if ($result['success'] == 1) {
            $sid = $result['sid'];
        }
        return $sid;
    }

    public function getSidSDK()
    {
        $sidSDK = false;
        $result = $this->executeRequest($this->requestUrlGenerator->getSDKSidUrl());
        if ($result['success'] == 1) {
            $sidSDK = $result['sid'];
        }
        return $sidSDK;
    }

    public function getHealth(): array
    {
        return $this->executeRequest($this->requestUrlGenerator->getHealthUrl());
    }

    public function getObjectsTree(): array
    {
        return $this->executeRequest($this->requestUrlGenerator->getObjectsTreeUrl());
    }

    public function getChannels(): ?array
    {
        return $this->executeRequest($this->requestUrlGenerator->getChannelsUrl());
    }


    public function getUsers()
    {
        $users = [];
        $guidesRequestUrl = $this->requestUrlGenerator->getUserGuidesUrl();
        $userGuides = $this->executeRequest($guidesRequestUrl);

        foreach ($userGuides['subdirs'] as $guid) {
            $detailsRequestUrl = $this->requestUrlGenerator->getUserDetailsUrl($guid);
            $namesRequestUrl = $this->requestUrlGenerator->getUserNameUrl($guid);
            $groupRequestUrl = $this->requestUrlGenerator->getUserGroupUrl($guid);
            $details = $this->executeRequest($detailsRequestUrl);
            $name = $this->executeRequest($namesRequestUrl);
            $group = $this->executeRequest($groupRequestUrl);
            $user = [
                'guid' => $guid,
                'name' => $name['value'],
                'type' => $details['type'],
                'parentGroupGuid' => $group['value'],
            ];
            $users[] = $user;
        }
        return $users;
    }


    public function createGroup(string $groupName)
    {
        $requestPrepareUrl = $this->requestUrlGenerator->getCreateGroupPrepareUrl($groupName);
        $requestExecuteUrl = $this->requestUrlGenerator->getCreateGroupExecuteUrl();
        if ($this->isUserAlreadyExists($groupName)) {
            throw new \Exception("Group {$groupName} already exists");
        }
        $this->executeRequest($requestPrepareUrl);
        return $this->executeRequest($requestExecuteUrl);
    }

    public function createUser(string $userName, string $userPassword)
    {

        $requestPrepareNameUrl = $this->requestUrlGenerator->getCreateUserPrepareNameUrl($userName);
        $requestExecutePasswordUrl = $this->requestUrlGenerator->getCreateUserPreparePasswordUrl($userPassword);
        $requestExecuteUrl = $this->requestUrlGenerator->getCreateUserExecuteUrl();

        if ($this->isUserAlreadyExists($userName)) {
            throw new \Exception("User {$userName} already exists");
        }
        $this->executeRequest($requestPrepareNameUrl);
        $this->executeRequest($requestExecutePasswordUrl);
        return $this->executeRequest($requestExecuteUrl);
    }

    public function deleteUser(string $userName)
    {
        $userGuid=$this->getUserGuidByName($userName);
        $requestUrl = $this->requestUrlGenerator->getDeleteUserUrl($userGuid);
        return $this->executeRequest($requestUrl);
    }

    public function deleteGroup(string $groupName)
    {
        $existingUsers = $this->trassirNvr->getUsers();
        foreach ($existingUsers as $user) {
            if ($user['parentGroupGuid'] == $this->getUserGuidByName($groupName)) {
                throw new \Exception("Group {$groupName} is not empty.");
            }
        }
        $userGuid=$this->getUserGuidByName($groupName);
        $requestUrl = $this->requestUrlGenerator->getDeleteUserUrl($userGuid);
        return $this->executeRequest($requestUrl);
    }

    private function isUserAlreadyExists(string $userName)
    {
        $existingUsers = $this->trassirNvr->getUsers();
        foreach ($existingUsers as $user) {
            if ($user['name'] == $userName) {
                return true;
            }
        }
        return false;
    }

    private function getUserGuidByName(string $userName)
    {
        $guid = false;
        $existingUsers = $this->trassirNvr->getUsers();

        foreach ($existingUsers as $user) {
            if ($user['name'] == $userName) {
                $guid = $user['guid'];
            }
        }
        return $guid;
    }

    public function getScreenshot(string $channelGuid, \DateTime $timestamp) {

        return $this->requestUrlGenerator->getScreenshotUrl($channelGuid, $timestamp);
    }

    public function getVideoToken($channelGuid){
        $tokenUrl = $this->requestUrlGenerator->getVideoTokenUrl($channelGuid);
        $token =$this->executeRequest($tokenUrl);
        return $token['token'];
    }

    public function getNetworkInterfaces(): ?array
    {
        $interfaces = $this->executeRequest($this->requestUrlGenerator->getNetworkInterfacesUrl());
        return $interfaces['subdirs'];
    }
    public function getNetworkInterfaceSettings($interfaceName): ?array
    {
       $settings =[];
       $settingNames = [
           "dns2",
           "dns1",
           "routes",
           "gateway",
           "netmask",
           "ip",
           "dhcp",
           "enabled"
       ];
       foreach ($settingNames as $settingName) {
           $requestUrl = $this->requestUrlGenerator->getNetworkInterfaceSettings($interfaceName, $settingName);
           $setting = $this->executeRequest($requestUrl);
           $settings[$settingName] = $setting['value'];
       }
        return $settings;
    }

    public function getHddList(){
        $hddUrl = $this->requestUrlGenerator->getArchiveSettingsUrl();
        return  $this->executeRequest($hddUrl);
    }

    public function getHddInfo(string $hddName){
        $hddInfo =[];
        $hddInfo['name'] = $hddName;

        $hddModelUrl = $this->requestUrlGenerator->getHddModelUrl($hddName);
        $hddInfo['model'] = $this->executeRequest($hddModelUrl)['value'];

        $hddSerialUrl = $this->requestUrlGenerator->getHddSerialUrl($hddName);
        $hddInfo['serial'] = $this->executeRequest($hddSerialUrl)['value'];

        $hddCapacity = $this->requestUrlGenerator->getHddCapacityGb($hddName);
        $hddInfo['capacity'] = $this->executeRequest($hddCapacity)['value'];

        return  $hddInfo;
    }

    public function getCamerasList(){
        $camerasUrl = $this->requestUrlGenerator->getCamerasSettingsUrl();
        return  $this->executeRequest($camerasUrl);
    }

    public function getCameraInfo(string $guid){
        $cameraInfo =[];
        $cameraInfo['guid'] = $guid;

        $modelUrl = $this->requestUrlGenerator->getCameraModelUrl($guid);
        $cameraInfo['model'] = $this->executeRequest($modelUrl)['value'];

        $nameUrl = $this->requestUrlGenerator->getCameraNameUrl($guid);
        $cameraInfo['name'] = $this->executeRequest($nameUrl)['value'];

        $ipAddressUrl = $this->requestUrlGenerator->getCameraIplUrl($guid);
        $cameraInfo['ip'] = $this->executeRequest($ipAddressUrl)['value'];

        return  $cameraInfo;
    }

}