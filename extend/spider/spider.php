<?php
namespace spider;
use Curl\Curl;
use think\Log;

class spider{
    public $config          = [];
    public $curl;
    public $queueList       = [];       //队列数组,swoole看情况需要一个文件队列或者Redis
    public $queueListKey    = [];
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
        $this->config['domains']        = !empty($config['domains'])?$config['domains']:[];

        if($this->config['indexUrl'])
            $this->addScanUrl($this->config['indexUrl']);

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
        $collect_url = $this->queueLeftPop();
        if($collect_url){
            $html = $this->curl->get($collect_url);
            if(!$this->curl->error){
                $this->analysisContent($html,$collect_url);
            }else{
                Log::write("抓取失败");
            }
        }
    }


    /**
     * 解析内容加入队列
     */
    private function analysisContent($html = '',$collect_url){
        if($this->config['listUrl']){
            preg_match_all("/<a.*href=[\"']{0,1}(.*)[\"']{0,1}[> \r\n\t]{1,}/isU", $html, $urls);
            if($urls[1]){
                foreach ($urls[1] as $key=>$url)
                {
                    $urls[$key] = str_replace(array("\"", "'",'&amp;'), array("",'','&'), $url);
                }
                $urls = array_unique($urls);
                foreach($urls as $key=>$v){
                    $val = $this->fillUrl($v,$collect_url);
                    if($val){
                        $urls[$key] = $val;
                    }else{
                        unset($urls[$key]);
                    }
                }
                var_dump($urls);die;
            }
        }
    }

    /**
     * 投递url
     */
    private function addScanUrl($url){
        $link['url'] = $url;
        if($this->isListPage($url)){
            $link['url_type'] = 'list_url';
        }elseif($this->isContentPage($url)){
            $link['url_type'] = 'content_url';
        }else{
            $link['url_type'] = 'index_url';
        }
        $this->queueLeftPush($link);
    }

    /**
     * 链接头部加入队列
     */
    private function queueLeftPush($arr = []){
        $result = false;
        $key = md5($arr['url']);
        if(!array_key_exists($key,$this->queueListKey)){
            $this->queueListKey[$key] = time();
            $result = array_unshift($this->queueList,$arr['url']);
        }
        return $result;
    }

    /**
     * 链接尾部加入队列
     */
    private function queueRightPush($arr = ''){
        $result = false;
        $key = md5($arr['url']);
        if(!array_key_exists($key,$this->queueListKey)){
            $this->queueListKey[$key] = time();
            $result = array_push($this->queueList,$arr);
        }
        return $result;
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

    /**
     * 是否是列表页
     */
    private function isListPage($url){
        $result = false;
        foreach($this->config['listUrl'] as $role){
            if(preg_match("#$role#i",$url)){
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * 是否是内容页
     */
    private function isContentPage($url){
        $result = false;
        foreach($this->config['contentUrl'] as $role){
            if(preg_match("#$role#i",$url)){
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * 获得完整的连接地址
     */
    public function fillUrl($url, $collect_url)
    {
        $url = trim($url);
        $collect_url = trim($collect_url);

        // 排除JavaScript的连接
        //if (strpos($url, "javascript:") !== false)
        if( preg_match("@^(javascript:|#|'|\")@i", $url) || $url == '')
        {
            return false;
        }
        // 排除没有被解析成功的语言标签
        if(substr($url, 0, 3) == '<%=')
        {
            return false;
        }
        $parse_url = @parse_url($collect_url);
        if (empty($parse_url['scheme']) || empty($parse_url['host']))
        {
            return false;
        }
        // 过滤mailto、tel、sms、wechat、sinaweibo、weixin等协议
        if (!in_array($parse_url['scheme'], array("http", "https")))
        {
            return false;
        }
        $scheme = $parse_url['scheme'];
        $domain = $parse_url['host'];
        $path = empty($parse_url['path']) ? '' : $parse_url['path'];
        $base_url_path = $domain.$path;
        $base_url_path = preg_replace("/\/([^\/]*)\.(.*)$/","/",$base_url_path);
        $base_url_path = preg_replace("/\/$/",'',$base_url_path);

        $i = $path_step = 0;
        $dstr = $pstr = '';
        $pos = strpos($url,'#');
        if($pos > 0)
        {
            // 去掉#和后面的字符串
            $url = substr($url, 0, $pos);
        }
        // 京东变态的都是 //www.jd.com/111.html
        if(substr($url, 0, 2) == '//')
        {
            $url = str_replace("//", "", $url);
        }
        // /1234.html
        elseif($url[0] == '/')
        {
            $url = $domain.$url;
        }
        // ./1234.html、../1234.html 这种类型的
        elseif($url[0] == '.')
        {
            if(!isset($url[2]))
            {
                return false;
            }
            else
            {
                $urls = explode('/',$url);
                foreach($urls as $u)
                {
                    if( $u == '..' )
                    {
                        $path_step++;
                    }
                    // 遇到 ., 不知道为什么不直接写$u == '.', 貌似一样的
                    else if( $i < count($urls)-1 )
                    {
                        $dstr .= $urls[$i].'/';
                    }
                    else
                    {
                        $dstr .= $urls[$i];
                    }
                    $i++;
                }
                $urls = explode('/',$base_url_path);
                if(count($urls) <= $path_step)
                {
                    return false;
                }
                else
                {
                    $pstr = '';
                    for($i=0;$i<count($urls)-$path_step;$i++){ $pstr .= $urls[$i].'/'; }
                    $url = $pstr.$dstr;
                }
            }
        }
        else
        {
            if( strtolower(substr($url, 0, 7))=='http://' )
            {
                $url = preg_replace('#^http://#i','',$url);
                $scheme = "http";
            }
            else if( strtolower(substr($url, 0, 8))=='https://' )
            {
                $url = preg_replace('#^https://#i','',$url);
                $scheme = "https";
            }
            else
            {
                $url = $base_url_path.'/'.$url;
            }
        }
        // 两个 / 或以上的替换成一个 /
        $url = preg_replace('@/{1,}@i', '/', $url);
        $url = $scheme.'://'.$url;
        //echo $url;exit("\n");

        $parse_url = @parse_url($url);
        $domain = empty($parse_url['host']) ? $domain : $parse_url['host'];
        // 如果host不为空, 判断是不是要爬取的域名
        if (!empty($parse_url['host']))
        {
            //排除非域名下的url以提高爬取速度
            if (!in_array($parse_url['host'], $this->config['domains']))
            {
                return false;
            }
        }

        return $url;
    }
}