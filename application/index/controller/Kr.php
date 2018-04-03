<?php
namespace app\index\controller;
use spider\spider;
use think\File;

class Kr
{
    public $config = [];
    public static $data = [];
    public $this_key;       //当前key
    public static $key =0;

    //36氪抓取新闻
    public function index($workerNum = 1)
    {


        $this->config      = [
            "webSite"       =>'http://36kr.com',
            "workerNum"     =>isset($workerNum)?$workerNum:1,   //启动任务数量,需要client投递
            "memory"        =>false,                            //是否开启记忆功能(多进程需要开启)
            "domains"       =>[
                "36kr.com",
            ],
            "webName"       =>'36氪',
            "indexUrl"      =>'',
            "contentUrl"=>[
                '36kr.com/api/search/newsflashes/(.*)\?q=(.*)&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/阿里\?q=阿里&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/百度\?q=百度&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/网易\?q=网易&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/今日头条\?q=今日头条&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/美团\?q=美团&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/滴滴\?q=滴滴&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/新浪\?q=新浪&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/爱奇艺\?q=爱奇艺&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/亚马逊\?q=亚马逊&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/谷歌\?q=谷歌&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/苹果\?q=苹果&ts=\d+&page=\d+&per_page=\d+&_=\d+',
//                '36kr.com/api/search/newsflashes/微软\?q=微软&ts=\d+&page=\d+&per_page=\d+&_=\d+',
            ],
            "explode"=>[
                'type'=>'Mysql',
                'tableName'=>'36kr'
            ],
        ];
        $spider = new spider($this->config);


        $arr_url = [
            "%E8%85%BE%E8%AE%AF",                       //腾讯
            "%E9%98%BF%E9%87%8C",                       //阿里
//            "%E7%99%BE%E5%BA%A6",                       //百度
//            "%E7%BD%91%E6%98%93",                       //网易
//            "%E4%BB%8A%E6%97%A5%E5%A4%B4%E6%9D%A1",     //今日头条
//            "%E7%BE%8E%E5%9B%A2",                       //美团
//            "%E6%BB%B4%E6%BB%B4",                       //滴滴
//            "%E6%96%B0%E6%B5%AA",                       //新浪
//            "%E7%88%B1%E5%A5%87%E8%89%BA",              //爱奇艺
//            "%E4%BA%9A%E9%A9%AC%E9%80%8A",              //亚马逊
//            "%E8%B0%B7%E6%AD%8C",                       //谷歌
//            "%E8%8B%B9%E6%9E%9C",                       //苹果
//            "%E5%BE%AE%E8%BD%AF"                        //微软
        ];


        //内容页面回调
        $spider->contentFunc = function ($json){
            if($json->code === 0 && !empty($json->data->data)){
                self::$data[self::$key]['index'] = $this->this_key;
                foreach($json->data->data as $info){
                    self::$data[self::$key]['data'][] = [
                        'id'                =>$info->id,
                        'created_at'        =>$info->created_at,
                        'hash_title'        =>$info->hash_title,
                        'description_text'  =>$info->description_text,
                        'news_url'          =>$info->news_url,
                    ];
                }
                self::$key++;
            }
        };

        foreach($arr_url as $value){
            $this->this_key = $value;
            for($i=1;$i<=2;$i++){
                $url = "36kr.com/api/search/newsflashes/{$value}?q={$value}&ts=".(time()-50)."&page={$i}&per_page=20&_=".(time()-10);
                $spider->addScanUrl($url);
            }
            $spider->start();
        }

        $next_week = date("Y-m-d",strtotime("-15 day"));

        if(!empty(self::$data)){
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->addFontStyle('Link', array('color' => '0000FF', 'underline' => 'single','size'=>8));
            $section = $phpWord->addSection();
            foreach(self::$data as $key=>$value){
                foreach($value as $kk=>$vv){
                    if($kk == 'index')$section->addText(urldecode($vv)."<w:br />");
                    if($kk == 'data'){
                        foreach($vv  as $info){
                            if(strtotime($next_week) >= strtotime($info['created_at']))continue;
                            $info['news_url']           = !empty($info['news_url'])?$info['news_url']:'javascript:;';
                            $info['hash_title']         = !empty($info['hash_title'])?$info['hash_title']:'---------';
                            $info['description_text']   = !empty($info['description_text'])?$info['description_text']:'---------';
                            $info['created_at']         = date("Y-m-d H:i:s",strtotime($info['created_at']));

                            $section->addLink($info['news_url'],$info['hash_title'],"Link");
                            $section->addText($info['description_text'].$info['created_at'], ['bold' => true,'size'=>8]);
                            $section->addText("<w:br />");
                        }
                    }
                }
            }

            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('helloWorld.docx');
        }

        die;


        file_put_contents(RUNTIME_PATH."file/{$date}_news.json",json_encode(self::$data));

    }
}
