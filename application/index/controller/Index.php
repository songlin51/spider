<?php
namespace app\index\controller;
use spider\spider;

class Index
{
    public $config = [];
    public function index()
    {
            $this->config      = [
            "webSite"       =>'http://www.mmjpg.com',
            "wenName"       =>'妹子图',
            "indexUrl"      =>'http://www.mmjpg.com',
            "listUrl"       =>[
                'www.mmjpg.com/home/\d'
            ],
            "contentUrl"=>[
                'www.mmjpg.com/mm/\d'
            ],
        ];
        $spider = new spider($this->config);
        $spider->run();

    }
}
