<?php


namespace dglushakov\Trassir\TrassirNvr;


class TrassirRequest
{
    private $trassirNvr;
    private $requestType;
    private  $params;

    public function __construct(TrassirNvrInterface $trassirNvr, string $requestType, $params = [])
    {
        $this->trassirNvr = $trassirNvr;
        $this->requestType = $requestType;
        $this->params = $params;
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
            'CREATE_USER_PREPARE_USERNAME',
            'CREATE_USER_PREPARE_PASSWORD',
            'CREATE_USER_EXECUTE',
            'CREATE_GROUP_PREPARE',
            'CREATE_GROUP_EXECUTE',


        ];

        return $types;
    }

    public function execute(){
        if(!$this->trassirNvr->login()){
            return null;
        }

        $requestUrl ="https://{$this->trassirNvr->getIp()}:8080";
        switch ($this->requestType) {
            case "HEALTH":
                $requestUrl .="/health?sid={$this->trassirNvr->getSid()}";
                break;
            case "OBJECTS_TREE":
                $requestUrl .='/objects/?sid=' . trim($this->trassirNvr->getSidSDK());
                break;
            case "CHANNELS_LIST":
                $requestUrl .='/channels?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "SERVER_SETTINGS":
                $requestUrl .='/settings/?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USERS":
                $requestUrl .='/settings/users/?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USER_OR_GROUP":
                $requestUrl .='/settings/users/'.$this->params['userGuid'].'/?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USER_NAME":
                $requestUrl .='/settings/users/'.$this->params['userGuid'].'/name?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "USER_GROUP":
                $requestUrl .='/settings/users/'.$this->params['userGuid'].'/group?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "DELETE_USER":
                $requestUrl .='/settings/users/user_add/delete_user_id='.$this->params['userGuid'].'?sid=' . trim($this->trassirNvr->getSid());
                break;

            case "CREATE_GROUP_PREPARE":
                $requestUrl .='/settings/users/user_add/new_group_name='.$this->params['groupName'].'?sid=' . trim($this->trassirNvr->getSid());
                break;
            case "CREATE_GROUP_EXECUTE":
                $requestUrl .='/settings/users/user_add/create_group_now=1?sid=' . trim($this->trassirNvr->getSid());
                break;

        }

        $responseJson_str = file_get_contents($requestUrl, null, $this->trassirNvr->getStreamContext());
        $comment_position = strripos($responseJson_str, '/*');    //отрезаем комментарий в конце ответа сервера  //TODO если комента нет, то $comment_position=false и response = пустая строка
        if (!$comment_position) {
            $comment_position=strlen($responseJson_str);
        }
        $responseJson_str = substr($responseJson_str, 0, $comment_position);
        $response = json_decode($responseJson_str, true);

        return $response;
    }


}