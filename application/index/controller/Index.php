<?php
namespace app\index\controller;
use spider\spider;
use think\Log;

class Index
{
    public $config = [];
    public function index($workerNum = 1)
    {
            $this->config      = [
                "webSite"       =>'http://www.mmjpg.com',
                "workerNum"     =>isset($workerNum)?$workerNum:1,
                "memory"        =>1,
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
}
