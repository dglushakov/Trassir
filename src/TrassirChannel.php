<?php


namespace dglushakov\Trassir;


class TrassirChannel implements TrassirChannelInterface
{
    private $guid, $name, $rights, $codec;

    public function __construct($guid, $name, $rights, $codec)
    {
        $this->guid = $guid;
        $this->name = $name;
        $this->rights = $rights;
        $this->codec = $codec;
    }

    public function getGuid()
    {
        return $this->guid;
    }
}