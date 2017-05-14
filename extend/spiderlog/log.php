<?php
namespace spiderlog;
use spider\spider;

class log{
    public $spider;
    public function __construct(spider $spider)
    {
        $this->spider = $spider;
    }

    /**
     * 启动日志
     */
    public function startLog(){
        $startTime = time() - strtotime($this->spider->startTime);
        $msg = "\r\n-------------------------------------------------\r\n";
        $msg.="启动时间:".$this->spider->startTime.",爬取网站:".$this->spider->config['webName']."\r\n";
        $msg.="抓取时长".$startTime."秒,队列长度:".$this->spider->queueListCount."\r\n";
        $msg.="\r\n-------------------------------------------------\r\n";
        $this->replace_echo($msg);
    }

    /**
     * 写入日志
     */
    public function writeLog(){

    }

    public function replace_echo($message, $force_clear_lines = NULL)
    {
        static $last_lines = 0;

        if(!is_null($force_clear_lines))
        {
            $last_lines = $force_clear_lines;
        }

        // 获取终端宽度
        $toss = $status = null;
        $term_width = exec('tput cols', $toss, $status);
        if($status || empty($term_width))
        {
            $term_width = 64; // Arbitrary fall-back term width.
        }

        $line_count = 0;
        foreach(explode("\n", $message) as $line)
        {
            $line_count += count(str_split($line, $term_width));
        }

        // Erasure MAGIC: Clear as many lines as the last output had.
        for($i = 0; $i < $last_lines; $i++)
        {
            // Return to the beginning of the line
            echo "\r";
            // Erase to the end of the line
            echo "\033[K";
            // Move cursor Up a line
            echo "\033[1A";
            // Return to the beginning of the line
            echo "\r";
            // Erase to the end of the line
            echo "\033[K";
            // Return to the beginning of the line
            echo "\r";
            // Can be consolodated into
            // echo "\r\033[K\033[1A\r\033[K\r";
        }

        $last_lines = $line_count;

        echo $message."\n";
    }

}