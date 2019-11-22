<?php


namespace dglushakov\Trassir\TrassirNvr;


class TrassirRequest
{
    private $trassirNvr;
    private $requestType;
    private  $inputData;

    public function __construct(TrassirNvrInterface $trassirNvr, string $requestType, $inputData = null)
    {
        $this->trassirNvr = $trassirNvr;
        $this->requestType = $requestType;
        $this->inputData = $inputData;
    }
    public static function getRequestTypes(){
        $types=[
            'HEALTH',
            'OBJECTS_TREE',
            'CHANNELS_LIST',
            'SERVER_SETTINGS',
            'USERS',
            'USER_OR_GROUP',
            'USER_NAME',
            'CREATE_GROUP_PREPARE',
            'CREATE_GROUP_EXECUTE',


        ];

        return $types;
    }

    public function execute(){ //TODO проверка онлайна сервера
        if(!$this->trassirNvr->login()){
            return null;
        }

        $requstUrl ="https://{$this->trassirNvr->getIp()}:8080";
        switch ($this->requestType) {
            case "HEALTH":
                $requstUrl .="/health?sid={$this->trassirNvr->getSid()}";
                break;
            case "OBJECTS_TREE":
                $requstUrl .='/objects/?sid=' . trim($this->trassirNvr->getSidSDK());
                break;
            case "CHANNELS_LIST":
                $requstUrl .='/channels?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "SERVER_SETTINGS":
                $requstUrl .='/settings/?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USERS":
                $requstUrl .='/settings/users/?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USER_OR_GROUP":
                $requstUrl .='/settings/users/'.$this->inputData.'/?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USER_NAME":
                $requstUrl .='/settings/users/'.$this->inputData.'/name?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USER_GROUP":
                $requstUrl .='/settings/users/'.$this->inputData.'/group?sid=' . trim($this->trassirNvr->getSid());
                break;

            case "CREATE_GROUP_PREPARE":
                $requstUrl .='/settings/users/user_add/new_group_name='.$this->inputData.'?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "CREATE_GROUP_EXECUTE":
                $requstUrl .='/settings/users/user_add/create_group_now=1?sid=' . trim($this->trassirNvr->getSid());
                break;
        }

        $responseJson_str = file_get_contents($requstUrl, null, $this->trassirNvr->getStreamContext());
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $response = json_decode($responseJson_str, true);

        return $response;


    }


}