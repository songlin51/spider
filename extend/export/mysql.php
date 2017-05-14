<?php
namespace export;
use think\Db;

class mysql{
    public $tableName;
    public function __construct($tableName)
    {
        if(empty($tableName))exit("tableName is empty!");
        $this->tableName = $tableName;
    }

    public function addRow($data = []){
        $result = Db::table($this->tableName)->insert($data);
        return $result;
    }

    public function addAll($data = []){

    }

    public function delete($where = []){

    }
}