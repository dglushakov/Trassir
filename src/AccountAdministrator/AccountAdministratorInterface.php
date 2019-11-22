<?php


namespace dglushakov\Trassir\AccountAdministrator;


interface AccountAdministratorInterface
{
    public function getUsers();
    public function createGroup(string $groupName);
    //public function createUser();
    //public function deleteGroup(); //TODO запрет пока не пустая
    //public function deleteUser();




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