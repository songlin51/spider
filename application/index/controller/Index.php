<?php
namespace app\index\controller;
use spider\spider;
use think\Log;

class Index
{
    public $config = [];

    //美女图集
    public function index($workerNum = 1)
    {
            $this->config      = [
                "webSite"       =>'http://www.mmjpg.com',
                "workerNum"     =>isset($workerNum)?$workerNum:1,   //启动任务数量,需要client投递
                "memory"        =>0,                                //是否记忆上次抓取节点
                "domains"       =>[
                    "www.mmjpg.com",
                    "mmjpg.com"
                ],
                "wenName"       =>'妹子图',
                "indexUrl"      =>'http://www.mmjpg.com',
                "listUrl"       =>[
                    'www.mmjpg.com/home/\d'
                ],
                "contentUrl"=>[
                    'www.mmjpg.com/mm/\d'
                ],
                "explode"=>[
                    'type'=>'Mysql',
                    'tableName'=>'meizitu'
                ],
                "fields"=>[
                    [
                        'fieldName'=>'title',
                        'rule'=>'<h2>(.*?)</h2>'
                    ],
                    [
                        'fieldName'=>'createDate',
                        'rule'=>'<i>发表于: (.*?)</i>'
                    ],
                    [
                        'fieldName'=>'source',
                        'rule'=>'<i>来源: (.*?)</i>'
                    ],
                    [
                        'fieldName'=>'hot',
                        'rule'=>'<i>人气\((.*?)\)</i>'
                    ],
                    [
                        'fieldName'=>'img',
                        'rule'=>'<div class="content" id="content"><a.*><img src="(.*)"\salt=".*"\s\/>'
                    ]
                ]

        ];
        $spider = new spider($this->config);
        //内容页面回调
//        $spider->contentFunc = function (){
//        };
        $spider->start();

    }
    public function qiushibaike($workerNum = 1){
        $this->config      = [
            "webSite"       =>'https://www.qiushibaike.com',
            "workerNum"     =>isset($workerNum)?$workerNum:1,   //启动任务数量,需要client投递
            "memory"        =>false,                                //是否记忆上次抓取节点
            "domains"       =>[
                "www.qiushibaike.com",
                "qiushibaike.com"
            ],
            "wenName"       =>'糗事百科',
            "indexUrl"      =>'https://www.qiushibaike.com',
            "listUrl"       =>[
                'www.qiushibaike.com/8hr/page/\d'
            ],
            "contentUrl"=>[
                'www.qiushibaike.com/article/\d'
            ],
            "explode"=>[
                'type'=>'Mysql',
                'tableName'=>'糗事百科'
            ],
            "fields"=>[
                [
                    'fieldName'=>'author',
                    'rule'=>'<title>([\s\S]*)</title>',
                ],
                [
                    'fieldName'=>'content',
                    'rule'=>'<div class=\"content-text\">([\s\S]*)</div>',
                    'mode'=>'U'
                ]
            ]

        ];
        $spider = new spider($this->config);
        //内容页面回调
//        $spider->contentFunc = function ($html){
//            var_dump($html);
//        };
        $spider->fieldsFunc = function ($data){
            var_dump($data);
        };
//        $spider->thisUrlFunc = function ($url){
//            var_dump($url);
//        };
        $spider->start();
    }
}
