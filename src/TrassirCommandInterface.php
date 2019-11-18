<?php


namespace dglushakov\Trassir;

Interface TrassirCommandInterface {

    public function login(): ?string;
    public function getObjectsTree(): ?array;

}
