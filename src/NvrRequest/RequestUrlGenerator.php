<?php


namespace dglushakov\Trassir\NvrRequest;


use dglushakov\Trassir\TrassirNvr\TrassirNvrInterface;

class RequestUrlGenerator
{
    protected $trassirNvr;
    protected $url;

    public function __construct(TrassirNvrInterface $trassirNvr)
    {
        $this->trassirNvr = $trassirNvr;
        $this->url= "https://{$this->trassirNvr->getIp()}:8080";
    }

    public function getSidUrl()
    {
        return $this->url . "/login?username={$this->trassirNvr->getUserName()}&password={$this->trassirNvr->getPassword()}";
    }

    public function getSDKSidUrl()
    {
        return $this->url . "/login?password={$this->trassirNvr->getPasswordSDK()}";
    }

    public function getHealthUrl()
    {
        return $this->url . "/health?sid={$this->trassirNvr->getSid()}";
    }

    public function getObjectsTreeUrl()
    {
        return $this->url . "/objects/?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getChannelsUrl()
    {
        return $this->url . "/channels?sid={$this->trassirNvr->getSid()}";
    }
    public function getUserGuidesUrl()
    {
        return $this->url . "/settings/users/?sid={$this->trassirNvr->getSid()}";
    }

    public function getUserDetailsUrl(string $userGuid)
    {
        return $this->url . "/settings/users/{$userGuid}/?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getUserNameUrl(string $userGuid)
    {
        return $this->url . "/settings/users/{$userGuid}/name?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getUserGroupUrl(string $userGuid)
    {
        return $this->url . "/settings/users/{$userGuid}/group?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCreateGroupPrepareUrl(string $groupName)
    {
        return $this->url . "/settings/users/user_add/new_group_name={$groupName}?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCreateGroupExecuteUrl()
    {
        return $this->url . "/settings/users/user_add/create_group_now=1?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCreateUserPrepareNameUrl(string $userName)
    {
        return $this->url . "/settings/users/user_add/new_user_name={$userName}?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCreateUserPreparePasswordUrl(string $userPassword)
    {
        return $this->url . "/settings/users/user_add/new_user_password={$userPassword}?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCreateUserExecuteUrl()
    {
        return $this->url . "/settings/users/user_add/create_now=1?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getDeleteUserUrl(string $userGuid)
    {
        return $this->url . "/settings/users/user_add/delete_user_id={$userGuid}?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getScreenshotUrl(string $channelGuid,\DateTime $timestamp)
    {
        $timestamp = $timestamp->getTimestamp();
        return $this->url . "/screenshot/{$channelGuid}?timestamp={$timestamp}&sid={$this->trassirNvr->getSid()}";
    }

    public function getVideoTokenUrl(string $channelGuid)
    {
        return $this->url . "/get_video?channel={$channelGuid}&container=hls&stream=sub&sid={$this->trassirNvr->getSid()}";
    }

    public function getNetworkInterfacesUrl()
    {
        return $this->url . "/settings/network_interfaces/?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getNetworkInterfaceSettings(string $interfaceName, string $settingName)
    {
        return $this->url . "/settings/network_interfaces/{$interfaceName}/{$settingName}?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getArchiveSettingsUrl()
    {
        return $this->url . "/settings/archive/?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getHddModelUrl($hddName)
    {
        return $this->url . "/settings/archive/$hddName/model?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getHddSerialUrl($hddName)
    {
        return $this->url . "/settings/archive/$hddName/serial?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getHddCapacityGb($hddName)
    {
        return $this->url . "/settings/archive/$hddName/capacity_gb?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCamerasSettingsUrl()
    {
        return $this->url . "/settings/ip_cameras/?sid={$this->trassirNvr->getSidSDK()}";
    }

    public function getCameraModelUrl(string $guid)
    {
        return $this->url . "/settings/ip_cameras/{$guid}/model?sid={$this->trassirNvr->getSidSDK()}";
    }
    public function getCameraNameUrl(string $guid)
    {
        return $this->url . "/settings/ip_cameras/{$guid}/name?sid={$this->trassirNvr->getSidSDK()}";
    }
    public function getCameraIplUrl(string $guid)
    {
        return $this->url . "/settings/ip_cameras/{$guid}/connection_ip?sid={$this->trassirNvr->getSidSDK()}";
    }
}