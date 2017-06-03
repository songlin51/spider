<?php
namespace app\client\controller;
use think\Config;
use think\Log;

/**
 * swoole客户端调用
 */
class client{
    public $client;
    public function __construct()
    {
        if(empty($this->client)){
            $this->client = new \swoole_client(SWOOLE_SOCK_TCP);
            $this->client->connect(Config::get("swoole.host"),Config::get("swoole.port"));
        }
    }

    public function index(){
        $params = array(
            'group'=>input('param.group','index'),
            'controller'=>input('param.controller','index'),
            'action'=>input('param.action','index'),
            'workerNum'=>input('param.workernum',1)

        );
        $params = json_encode($params);
        Log::write("Swoole Client send $params");
        $this->client->send($params);
    }

}