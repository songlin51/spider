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
        $this->serv = new \swoole_server(Config::get("swoole.host"),Config::get("swoole.port"));
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

    /**
     * 服务启动
     */
    public function on_start($serv){
        file_put_contents($this->conf['master_pid'],$serv->master_pid);
        file_put_contents($this->conf['manager_pid'],$serv->manager_pid);
        Log::write("Swoole服务启动成功!");

    }

    /**
     * 服务关闭
     */
    public function on_shutdown(){
        Log::write("Swoole关闭成功!");
    }

    /**
     * 客户端连接
     */
    public function on_connect($server, $fd, $from_id){
        Log::write("Swoole客户端连接成功fd:$fd,from_id:$from_id");
    }

    /**
     * 接收数据
     */
    public function on_receive($serv, $fd, $from_id, $data){
        Log::write("接收客户端fd:".$fd."from_id".$from_id);
        $params = json_decode($data,true);
        if($params['workerNum']){
            for($i=0;$i<$params['workerNum'];$i++){
                $serv->task($data);
            }
        }
    }

    /**
     * 客户端关闭
     */
    public function on_close($serv, $fd, $reactorId){
        Log::write("客户端 $fd 关闭成功");
    }

    public function on_task($serv, $task_id, $src_worker_id, $data){
        Log::write("task任务接收_task_id:".$task_id."_data:".$data);
        $params     = json_decode($data,true);
        $group      = $params['group'];
        $controller = $params['controller'];
        $action     = $params['action'];
        $workerNum  = $params['workerNum'];
        $event      = controller("$group/$controller");
        $event->$action($workerNum);

    }

    public function on_finish($serv, $task_id, $data){

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
        if($params){
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

}