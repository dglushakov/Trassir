<?php


namespace dglushakov\Trassir\AccountAdministrator;


use dglushakov\Trassir\TrassirNvr\TrassirNvrInterface;
use dglushakov\Trassir\TrassirNvr\TrassirRequest;

class AccountAdministrator implements AccountAdministratorInterface
{
    private $trassirNvr;
    private $lastError;
    private $users = [];

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    public function __construct(TrassirNvrInterface $trassirNvr)
    {
        $this->trassirNvr = $trassirNvr;
    }


    public function getUsers()
    {
        if (!empty($this->users)) {
            return $this->users;
        }
        $users = [];
        $request = new TrassirRequest($this->trassirNvr, 'USERS');
        $res = $request->execute();

        foreach ($res['subdirs'] as $userGuid) {
            $userData = $this->getUserDetails($userGuid);
            $users[] = $userData;
        }

        $this->users = $users;
        return $this->users;
    }

    private function getUserDetails(string $userGuid): ?array
    {
        $userDetails = [];
        $userDetails['guid'] = $userGuid;

        $request = new TrassirRequest($this->trassirNvr, 'USER_OR_GROUP', ['userGuid' => $userGuid]);
        $userGroupData = $request->execute();
        $userDetails ['type'] = $userGroupData['type'];

        $request = new TrassirRequest($this->trassirNvr, 'USER_NAME', ['userGuid' => $userGuid]);
        $userNameData = $request->execute();
        $userDetails['name'] = $userNameData['value'];

        if ($userDetails ['type'] == 'User') {
            $request = new TrassirRequest($this->trassirNvr, 'USER_GROUP', ['userGuid' => $userGuid]);
            $userGroupData = $request->execute();
            $userDetails ['parentGroupGuid'] = $userGroupData['value'];
        }

        return $userDetails;
    }

    public function createUser(string $userName)
    {

        if ($this->isUserExists($userName)) {
            $this->lastError = "User {$userName} already not exist";
            return false;
        }


        return true;
//        https://[адрес_сервера]:[порт]/settings/users/user_add/new_user_name=[имя_пользователя]?sid=[id_сессии]
//        https://[адрес_сервера]:[порт]/settings/users/user_add/new_user_password=[пароль_пользователя]?sid=[id_сессии]
//        https://[адрес_сервера]:[порт]/settings/users/user_add/create_now=1?sid=[id_сессии]
        return false;
    }

    public function deleteUser(string $userName)
    {
        if (!$this->isUserExists($userName)) {
            return false;
        }

        $userGuid = $this->getUserGuidByName($userName);
        return $this->deleteUserOrGroup($userGuid);
    }

    private function isUserExists(string $userName)
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['type'] == 'User' && $user['name'] == $userName) {
                return true;
            }
        }
        $this->lastError = "User {$userName} does not exist";
        return false;
    }

    private function getUserGuidByName(string $userName): ?string
    {
        $numberOfUsersWithSameName = 0;
        $userGuid = false;
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['type'] == 'User' && $user['name'] == $userName) {
                $userGuid = $user['guid'];
                $numberOfUsersWithSameName++;
            }
        }

        if (!$numberOfUsersWithSameName === 1) {
            $userGuid = false;
            $this->lastError = "Nvr have {$numberOfUsersWithSameName} groups with name {$userName}";
        }

        return $userGuid;
    }

    public function createGroup(string $groupName)
    {

        if (!$this->isGroupExists($groupName)) {
            $request = new TrassirRequest($this->trassirNvr, 'CREATE_GROUP_PREPARE', ['groupName' => $groupName]);
            $request->execute();

            $request = new TrassirRequest($this->trassirNvr, 'CREATE_GROUP_EXECUTE');
            $request->execute();
        } else {
            $this->lastError = "Group {$groupName} already exists";
            return false;
        }

        return true;
    }

    public function deleteGroup(string $groupName)
    {
        if (!$this->isGroupExists($groupName)) {
            return false;
        }

        if (!$this->isGroupEmpty($groupName)) {
            return false;
        }

        $groupGuid = $this->getGroupGuidByName($groupName);
        return $this->deleteUserOrGroup($groupGuid);
    }

    private function deleteUserOrGroup(string $guid)
    {
        $request = new TrassirRequest($this->trassirNvr, 'DELETE_USER', ['userGuid' => $guid]);
        $result = $request->execute();

        $response = $result['success'];
        return $result;
    }

    private function isGroupExists($groupName)
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['type'] == 'Group' && $user['name'] == $groupName) {
                return true;
            }
        }
        $this->lastError = "Group {$groupName} does not exist";
        return false;
    }

    private function isGroupEmpty($groupName)
    {
        if (!$this->isGroupExists($groupName)) {
            return false;
        }
        $groupGuid = $this->getGroupGuidByName($groupName);
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['type'] == 'User' && $user['parentGroupGuid'] == $groupGuid) {
                $this->lastError = "Group {$groupName} is not empty";
                return false;
            }
        }
        return true;
    }

    private function getGroupGuidByName(string $groupName): ?string
    {
        $numberOfGroupsWithSameName = 0;
        $groupGuid = false;
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['type'] == 'Group' && $user['name'] == $groupName) {
                $groupGuid = $user['guid'];
                $numberOfGroupsWithSameName++;
            }

        }

        if (!$numberOfGroupsWithSameName === 1) {
            $groupGuid = false;
            $this->lastError = "Nvr have {$numberOfGroupsWithSameName} groups with name {$groupName}";
        }

        return $groupGuid;
    }


}