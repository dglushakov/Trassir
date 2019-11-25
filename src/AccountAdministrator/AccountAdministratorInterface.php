<?php


namespace dglushakov\Trassir\AccountAdministrator;


interface AccountAdministratorInterface
{
    public function getUsers();

    public function createUser(string $userName);

    public function deleteUser(string $userName);
    //public function renameuser(string $userName);
    //public function changePassword(string $userName);

    public function createGroup(string $groupName);

    public function deleteGroup(string $groupName);
    //public function renameGroup(string $groupName);


    //last_login_time

    //changepassword
    /*
    https://10.17.26.33:8080/settings/users/IPPfYVFW/password?sid=Bh9SpFWI


    {
    "directory" : "users/IPPfYVFW/",
    "name" : "password"
    }



    To modify this value use:

    https://localhost:8080/settings/users/IPPfYVFW/password=new_value

    Or use POST request:

    POST https://localhost:8080/settings/users/IPPfYVFW/
    post-data: password=new_value

    */

}