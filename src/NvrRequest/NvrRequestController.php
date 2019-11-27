<?php


namespace dglushakov\Trassir\NvrRequest;


use dglushakov\Trassir\TrassirNvr\TrassirNvrInterface;

class NvrRequestController
{
    private $trassirNvr;
    private $urlPrefix;

    public function __construct(TrassirNvrInterface $trassirNvr)
    {
        $this->trassirNvr = $trassirNvr;
        $this->urlPrefix = "https://{$this->trassirNvr->getIp()}:8080";
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

    private function generateSidUrl()
    {
        return $this->urlPrefix . "/login?username={$this->trassirNvr->getUserName()}&password={$this->trassirNvr->getPassword()}";
    }

    private function generateSidSDKUrl()
    {
        return $this->urlPrefix . "/login?password={$this->trassirNvr->getPasswordSDK()}";
    }

    private function generateHealthUrl()
    {
        return $this->urlPrefix . "/health?sid={$this->trassirNvr->getSid()}";
    }

    private function generateObjectsUrl()
    {
        return $this->urlPrefix . "/objects/?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateChannelsUrl()
    {
        return $this->urlPrefix . "/channels?sid={$this->trassirNvr->getSid()}";
    }

    private function generateUserGuidesUrl()
    {
        return $this->urlPrefix . "/settings/users/?sid={$this->trassirNvr->getSid()}";
    }

    private function generateUserDetailsUrl(string $userGuid)
    {
        return $this->urlPrefix . "/settings/users/{$userGuid}/?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateUserNameUrl(string $userGuid)
    {
        return $this->urlPrefix . "/settings/users/{$userGuid}/name?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateUseGroupUrl(string $userGuid)
    {
        return $this->urlPrefix . "/settings/users/{$userGuid}/group?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateCreateGroupPrepareUrl(string $groupName)
    {
        return $this->urlPrefix . "/settings/users/user_add/new_group_name={$groupName}?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateCreateGroupExecuteUrl()
    {
        return $this->urlPrefix . "/settings/users/user_add/create_group_now=1?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateCreateUserPrepareNameUrl(string $userName)
    {
        return $this->urlPrefix . "/settings/users/user_add/new_user_name={$userName}?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateCreateUserPreparePasswordUrl(string $userPassword)
    {
        return $this->urlPrefix . "/settings/users/user_add/new_user_password={$userPassword}?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateCreateUserExecuteUrl()
    {
        return $this->urlPrefix . "/settings/users/user_add/create_now=1?sid={$this->trassirNvr->getSidSDK()}";
    }

    private function generateDeleteUserUrl(string $guid)
    {
        return $this->urlPrefix . "/settings/users/user_add/delete_user_id={$guid}?sid={$this->trassirNvr->getSidSDK()}";
    }


    public function getSid()
    {
        $sid = false;
        $requestUrl = $this->generateSidUrl();
        $result = $this->executeRequest($requestUrl);
        if ($result['success'] == 1) {
            $sid = $result['sid'];
        }
        return $sid;
    }

    public function getSidSDK()
    {
        $sidSDK = false;
        $requestUrl = $this->generateSidSDKUrl();
        $result = $this->executeRequest($requestUrl);
        if ($result['success'] == 1) {
            $sidSDK = $result['sid'];
        }
        return $sidSDK;
    }

    public function getObjectsTree(): array
    {
        $requestUrl = $this->generateObjectsUrl();
        return $this->executeRequest($requestUrl);
    }

    public function getHealth(): array
    {
        $requestUrl = $this->generateHealthUrl();
        return $this->executeRequest($requestUrl);
    }

    public function getChannels(): ?array
    {
        $requestUrl = $this->generateChannelsUrl();
        return $this->executeRequest($requestUrl);
    }


    public function getUsers()
    {

        $users = [];
        $guidesRequestUrl = $this->generateUserGuidesUrl();
        $userGuides = $this->executeRequest($guidesRequestUrl);

        foreach ($userGuides['subdirs'] as $guid) { //TODO если гуидов нет ошибка
            $detailsRequestUrl = $this->generateUserDetailsUrl($guid);
            $namesRequestUrl = $this->generateUserNameUrl($guid);
            $groupRequestUrl = $this->generateUseGroupUrl($guid);
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
        $requestPrepareUrl = $this->generateCreateGroupPrepareUrl($groupName);
        $requestExecuteUrl = $this->generateCreateGroupExecuteUrl();
        if ($this->isUserAlreadyExists($groupName)) {
            throw new \Exception("Group {$groupName} already exists");
        }
        $this->executeRequest($requestPrepareUrl);
        return $this->executeRequest($requestExecuteUrl);
    }

    public function createUser(string $userName, string $userPassword)
    {

        $requestPrepareNameUrl = $this->generateCreateUserPrepareNameUrl($userName);
        $requestExecutePasswordUrl = $this->generateCreateUserPreparePasswordUrl($userPassword);
        $requestExecuteUrl = $this->generateCreateUserExecuteUrl();

        if ($this->isUserAlreadyExists($userName)) {
            throw new \Exception("User {$userName} already exists");
        }
        $this->executeRequest($requestPrepareNameUrl);
        $this->executeRequest($requestExecutePasswordUrl);
        return $this->executeRequest($requestExecuteUrl);
    }

    public function deleteUser(string $userName) //TODO if its a group and not empty throw exception
    {
        $existingUsers = $this->trassirNvr->getUsers();
        foreach ($existingUsers as $user) {
            if ($user['parentGroupGuid'] == $this->getUserGuidByName($userName)) {
                throw new \Exception("Group {$userName} is not empty.");
            }
        }
        $userGuid=$this->getUserGuidByName($userName);
        $requestUrl = $this->generateDeleteUserUrl($userGuid);
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
}