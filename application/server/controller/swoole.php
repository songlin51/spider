<?php
/**
 * swoole 服务端
 */
namespace app\server\controller;
use think\Config;
class swoole{

    public $serv;
    public $conf;
    public function __construct()
    {
        //$this->check_run();
        //$this->check_params();
    }

    public function index(){
        $this->serv = new \swoole_server("127.0.0.1", 9501, SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $this->conf = Config::get('swoole_set');
        $this->serv->set($this->conf);
        $this->serv->on("OnStart",'on_start');           //swoole启动主进程主线程回调
        $this->serv->on("OnShutdown",'on_shutdown');     //服务关闭回调
        $this->serv->on("OnConnect",'on_connect');       //新连接进入回调
        $this->serv->on("OnReceive",'on_receive');       //接收数据回调
        $this->serv->on("OnClose",'on_close');           //客户端关闭回调
        $this->serv->on("OnTask",'on_task');             //task进程回调
        $this->serv->on("OnFinish",'on_finish');         //进程投递的任务在task_worker中完成时回调 exit("服务已经在运行!");
    }

    private function on_start(){
        
    }

    private function on_shutdown(){

    }

    private function on_connect(){

    }

    private function on_receive(){

    }

    private function on_close(){

    }

    private function on_task(){

    }

    private function on_finish(){

    }

    /**
     * 运行检测
     */
    private function check_run(){
        if(!IS_CLI){
            exit("请在client模式下运行!");
        }
    }

    /**
     * 参数解析
     */
    private function check_params(){
        $params = input('s');
        switch($params){
            case "reload":
                exec("kill -USR1 ");
                exit;
                break;
            case "shutdown":
                exit;
                break;
        }
    }

}