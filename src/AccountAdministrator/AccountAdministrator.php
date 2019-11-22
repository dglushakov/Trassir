<?php


namespace dglushakov\Trassir\AccountAdministrator;


use dglushakov\Trassir\TrassirNvr\TrassirNvrInterface;
use dglushakov\Trassir\TrassirNvr\TrassirRequest;

class AccountAdministrator implements AccountAdministratorInterface
{
    private $trassirNvr;
    private $lastError;

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


    private function getUserDetails(string $userGuid) :?array
    {
        $userDetails['guid'] =$userGuid;

        $request = new TrassirRequest($this->trassirNvr, 'USER_OR_GROUP', $userGuid);
        $userGroupData = $request->execute();
        $userDetails ['type'] = $userGroupData['type'];

        $request = new TrassirRequest($this->trassirNvr, 'USER_NAME', $userGuid);
        $userNameData = $request->execute();
        $userDetails['name'] = $userNameData['value'];

        $request = new TrassirRequest($this->trassirNvr, 'USER_GROUP', $userGuid);
        $userGroupData = $request->execute();
        $userDetails ['parentGroupGuid'] = $userGroupData['value'];
        return $userDetails;
    }

    public function getUsers()
    {
        $users = [];
        $request = new TrassirRequest($this->trassirNvr, 'USERS');
        $res = $request->execute();

        foreach ($res['subdirs'] as $userGuid) {
            $userData  = $this->getUserDetails($userGuid);
            $users[] = $userData;
        }

        return $users;
    }

    public function createGroup(string $groupName) { //TODO проверять нет ли уже такой группы

        if(!$this->isGroupExists($groupName)) {
            $request = new TrassirRequest($this->trassirNvr, 'CREATE_GROUP_PREPARE', $groupName);
            var_dump($request->execute());

            $request = new TrassirRequest($this->trassirNvr, 'CREATE_GROUP_EXECUTE');
            var_dump($request->execute());
        } else {
            $this->lastError = "Group {$groupName} already exists";
            return false;
        }

        return true;
    }

    private function isGroupExists($groupName){
        $users  = $this->getUsers();
        foreach ($users as $user) {
            if ($user['type'] == 'Group' && $user['name'] == $groupName) {
                return true;
            }
        }
        return false;
    }

}