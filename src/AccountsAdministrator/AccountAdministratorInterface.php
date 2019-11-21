<?php


namespace dglushakov\Trassir\AccountAdministrator;


interface AccountAdministrator
{
    public function getUsers(); //TODO с группами? в виде дерева?


    //public function createGroup();
    //public function createUser();
    //public function deleteGroup(); //TODO запрет пока не пустая
    //public function deleteUser();

}