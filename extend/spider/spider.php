<?php
namespace spider;
use Curl\Curl;
use think\Log;

class spider{
    public $config      = [];
    public $curl;
    public $queueList   = [];       //队列数组,swoole看情况需要一个文件队列或者Redis
    public $loadImagesFunc;         //图片回调方法
    public $contentFunc;            //内容回调
    public $titleFunc;              //标题回调

    public function __construct($config)
    {
        $this->config['webSite']        = !empty($config['webSite'])?$config['webSite']:'';
        $this->config['webName']        = !empty($config['webName'])?$config['webName']:'看妹子';
        $this->config['indexUrl']       = !empty($config['indexUrl'])?$config['indexUrl']:'';
        $this->config['listUrl']        = !empty($config['listUrl'])?$config['listUrl']:'';
        $this->config['contentUrl']     = !empty($config['contentUrl'])?$config['contentUrl']:'';

        if($this->config['indexUrl'])
            $this->queueLeftPush($this->config['indexUrl']);

        $this->curl = new Curl();
    }

    /**
     * 任务执行
     */
    public function run(){
        do{
            $this->getHttp();
        }while($this->queueCount() > 0);
    }


    /**
     * 获取url信息
     */
    private function getHttp(){
        $url = $this->queueLeftPop();
        if($url){
            $result = $this->curl->get($url);
            if(!$this->curl->error){
                $this->analysisContent($result);
            }else{
                Log::write("抓取失败");
            }
        }
    }


    /**
     * 解析内容加入队列
     */
    private function analysisContent($text = ''){

    }

    /**
     * 链接头部加入队列
     */
    private function queueLeftPush($arr = ''){
        return array_unshift($this->queueList,$arr);
    }

    /**
     * 链接尾部加入队列
     */
    private function queueRightPush($arr = ''){
        return array_push($this->queueList,$arr);
    }

    /**
     * 链接头部弹出队列
     */
    private function queueLeftPop(){
        return array_shift($this->queueList);
    }

    /**
     * 链接尾部弹出队列
     */
    private function queueRightPop(){
        return array_pop($this->queueList);
    }

    private function queueCount(){
        return count($this->queueList);
    }


}