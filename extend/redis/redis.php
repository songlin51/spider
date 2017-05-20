<?php
namespace redis;
use think\Config;
use think\Exception;
use spiderlog;

class redis{
    public static $instance;
    public static $redis;
    public static $config;
    private function __construct()
    {
        if(empty(self::$config)){
            self::$config = Config::get('redis');
        }
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
        try{
            self::$redis = new \Redis();
            self::$redis->connect(self::$config['host'],self::$config['port']);
            return self::$redis;
        }catch (\Exception $e){
            exit($e->getMessage());
        }
    }

    /**
     * 数据添加链表左侧
     */
    public function lPush($key,$str){
        try{
            $str = json_encode($str);
            self::$redis->lPush($key,$str);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 数据添加链表右侧
     */
    public function rPush($key,$str){
        try{
            $str = json_encode($str);
            self::$redis->rPush($key,$str);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 左侧出列
     */
    public function lPop($key){
        try{
            $str = self::$redis->lPop($key);
            $str = json_decode($str,true);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return $str;
    }

    /**
     * 右侧出列
     */
    public function rPop($key){
        try{
            $str = self::$redis->rPop($key);
            $str = json_decode($str,true);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return $str;
    }

    /**
     * 加入集合
     */
    public function sadd($key,$str){
        try{
            self::$redis->sAdd($key,$str);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 判断元素是否是集合成员
     */
    public function sisMember($key,$str){
        return self::$redis->SISMEMBER($key,$str);
    }

    /**
     * 队列长度
     */
    public function listLen($key){
        return self::$redis->LLEN($key);
    }
}