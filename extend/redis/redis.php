<?php
namespace redis;
class redis{
    public static $instance;
    public static $redis;
    private function __construct()
    {

    }

    /**
     * 获取实例
     */
    public static function getInstance(){
        if(empty(self::$instance)){
            self::$instance = new self();

        }
        return self::$instance;
    }

    public function conn(){
        self::$redis = new \Redis();
        self::$redis->connect('127.0.01',6379);
        return self::$redis;
    }

}