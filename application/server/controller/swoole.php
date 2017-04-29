<?php
/**
 * swoole 服务端
 */
namespace app\server\controller;
use think\Config;
use think\Log;
class swoole{

    public $serv;
    public $conf;
    public function __construct()
    {
        $this->conf = Config::get('swoole_set');
        $this->check_run();
        $this->check_params();
    }

    public function index(){
        $this->serv = new \swoole_server("127.0.0.1", 9501);
        $this->serv->set($this->conf);
        $this->serv->on("Start",array($this,'on_start'));           //swoole启动主进程主线程回调
        $this->serv->on("Shutdown",array($this,'on_shutdown'));     //服务关闭回调
        $this->serv->on("Connect",array($this,'on_connect'));       //新连接进入回调
        $this->serv->on("Receive",array($this,'on_receive'));       //接收数据回调
        $this->serv->on("Close",array($this,'on_close'));           //客户端关闭回调
        $this->serv->on("Task",array($this,'on_task'));             //task进程回调
        $this->serv->on("Finish",array($this,'on_finish'));         //进程投递的任务在task_worker中完成时回调 exit("服务已经在运行!");
        $this->serv->start();
    }

    public function on_start($serv){
        file_put_contents($this->conf['master_pid'],$serv->master_pid);
        file_put_contents($this->conf['manager_pid'],$serv->manager_pid);
        Log::write("Swoole服务启动成功!");

    }

    public function on_shutdown(){

    }

    public function on_connect(){

    }

    public function on_receive(){

    }

    public function on_close(){

    }

    public function on_task(){

    }

    public function on_finish(){

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
        $master_pid = file_get_contents($this->conf['master_pid']);
        switch($params){
            case "reload":
                exec("kill -USR1 $master_pid");
                Log::write("Swoole Reload 完成!");
                exit;
                break;
            case "shutdown":
                exec("kill -15 $master_pid");
                Log::write("Swoole Shutdown 完成!");
                exit;
                break;
        }
    }

}