<?php
/**
 * swoole服务端配置文件
 */
return array(
    'swoole_set'=> array(
        'worker_num'            => 4,       //设置启动的worker进程数
        'daemonize'             =>0,        //是否守护进程运行
        'max_request'           => 0,       //设置worker进程的最大任务数，默认为0
        'max_conn'              => 100,     //服务器程序，最大允许的连接数
        'task_worker_num'       =>30,       //配置task进程的数量
        'task_ipc_mode'         =>'1',      //设置task进程与worker进程之间通信的方式1unix socket、2消息队列、3消息队列通信，并设置为争抢模式
        'task_max_request'      =>30,      //task任务最大进程数
        'task_tmpdir'           =>'/tmp/task_data', //task数据临时存放目录
        'log_file'              =>'/tmp/swoole/swoole.log',   //swoole错误日志存放路径
        'master_pid'            =>'/tmp/swoole/master_pid.log',      //master进程号
        'manager_pid'           =>'/tmp/swoole/manager_pid.log'         //管理进程号
    )
);