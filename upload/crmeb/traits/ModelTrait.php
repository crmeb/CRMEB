<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace crmeb\traits;

use think\db\Query;
use think\Model;

trait ModelTrait
{
    public static function get($where)
    {
        if(!is_array($where)){
            return self::find($where);
        }else{
            return self::where($where)->find();
        }
    }

    public static function all($function){
        $query = self::newQuery();
        $function($query);
        return $query->select();
    }

    /**
     * 添加多条数据
     * @param $group
     * @param bool $replace
     * @return mixed
     */
    public static function setAll($group, $replace = false)
    {
        return self::insertAll($group,$replace);
    }

    /**
     * 修改一条数据
     * @param $data
     * @param $id
     * @param $field
     * @return bool $type 返回成功失败
     */
    public static function edit($data,$id,$field = null)
    {
        $model = new self;
        if(!$field) $field = $model->getPk();
//        return false !== $model->update($data,[$field=>$id]);
//        return 0 < $model->update($data,[$field=>$id])->result;
        $res=$model->update($data,[$field=>$id]);
        if(isset($res->result))
            return 0 < $res->result;
        else if(isset($res['data']['result']))
            return  0 < $res['data']['result'];
        else
            return false !== $res;
    }


    /**
     * 查询一条数据是否存在
     * @param $map
     * @param string $field
     * @return bool 是否存在
     */
    public static function be($map, $field = '')
    {
        $model = (new self);
        if(!is_array($map) && empty($field)) $field = $model->getPk();
        $map = !is_array($map) ? [$field=>$map] : $map;
        return 0 < $model->where($map)->count();
    }

    /**
     * 删除一条数据
     * @param $id
     * @return bool $type 返回成功失败
     */
    public static function del($id)
    {
        return false !== self::destroy($id);
    }


    /**
     * 分页
     * @param null $model 模型
     * @param null $eachFn 处理结果函数
     * @param array $params 分页参数
     * @param int $limit 分页数
     * @return array
     */
    public static function page($model = null, $eachFn = null, $params = [], $limit = 20)
    {
        if(is_numeric($eachFn) && is_numeric($model)){
            return parent::page($model,$eachFn);
        }
        
        if(is_numeric($eachFn)){
            $limit = $eachFn;
            $eachFn = null;
        }else if(is_array($eachFn)){
            $params = $eachFn;
            $eachFn = null;
        }

        if(is_callable($model)){
            $eachFn = $model;
            $model = null;
        }elseif(is_numeric($model)){
            $limit = $model;
            $model = null;
        }elseif(is_array($model)){
            $params = $model;
            $model = null;
        }

        if(is_numeric($params)){
            $limit = $params;
            $params = [];
        }

        $paginate = $model === null ? self::paginate($limit,false,['query'=>$params]) : $model->paginate($limit,false,['query'=>$params]);
        $list = is_callable($eachFn) ? $paginate->each($eachFn) : $paginate;
        $page = $list->render();
        $total = $list->total();
        return compact('list','page','total');
    }
    /**
     * 获取分页 生成where 条件和 whereOr 支持多表查询生成条件
     * @param object $model 模型对象
     * @param array $where 需要检索的数组
     * @param array $field where字段名
     * @param array $fieldOr whereOr字段名
     * @param array $fun 闭包函数
     * @param string $like 模糊查找 关键字
     * @return array
     */
    public static function setWherePage($model=null,$where=[],$field=[],$fieldOr=[],$fun=null,$like='LIKE'){
        if(!is_array($where) || !is_array($field)) return false;
        if($model===null) $model=new self();
        //处理等于行查询
        foreach ($field as $key=>$item){
            if(($count=strpos($item,'.'))===false){
                if(isset($where[$item]) && $where[$item]!='') {
                    $model=$model->where($item,$where[$item]);
                }
            }else{
                $item_l=substr($item,$count+1);
                if(isset($where[$item_l]) && $where[$item_l]!=''){
                    $model=$model->where($item,$where[$item_l]);
                }
            }
        }
        //回收变量
        unset($count,$key,$item,$item_l);
        //处理模糊查询
        if(!empty($fieldOr) && is_array($fieldOr) && isset($fieldOr[0])){
            if(($count=strpos($fieldOr[0],'.'))===false){
                if(isset($where[$fieldOr[0]]) && $where[$fieldOr[0]]!='') {
                    $model=$model->where(self::get_field($fieldOr),$like,"%".$where[$fieldOr[0]]."%");
                }
            }else{
                $item_l = substr($fieldOr[0],$count+1);
                if(isset($where[$item_l]) && $where[$item_l]!='') {
                    $model=$model->where(self::get_field($fieldOr),$like,"%".$where[$item_l]."%");
                }
            }
        }
        unset($count,$key,$item,$item_l);
        return $model;
    }
    /**
     * 字符串拼接
     * @param int|array $id
     * @param string $str
     * @return string
     */
    private static function get_field($id,$str='|'){
        if(is_array($id)){
            $sql="";
            $i=0;
            foreach($id as $val){
                $i++;
                if($i<count($id)){
                    $sql.=$val.$str;
                }else{
                    $sql.=$val;
                }
            }
            return  $sql;
        }else{
            return $id;
        }
    }
    /**
     * 条件切割
     * @param string $order
     * @param string $file
     * @return string
     */
    public static function setOrder($order,$file='-'){
        if(empty($order)) return '';
        return str_replace($file,' ',$order);
    }
    /**
     * 获取时间段之间的model
     * @param int|string $time
     * @param string $ceil
     * @return array
     */
    public static function getModelTime($where,$model=null,$prefix='add_time',$data='data',$field=' - '){
        if ($model == null) $model = new self;
        if(!isset($where[$data])) return $model;
        switch ($where[$data]){
            case 'today':case 'week':case 'month':case 'year':case 'yesterday':
            $model=$model->whereTime($prefix,$where[$data]);
            break;
            case 'quarter':
                list($startTime,$endTime)=self::getMonth();
                $model = $model->where($prefix, '>', strtotime($startTime));
                $model = $model->where($prefix, '<', strtotime($endTime));
                break;
            case 'lately7':
                $model = $model->where($prefix,'between',[strtotime("-7 day"),time()]);
                break;
            case 'lately30':
                $model = $model->where($prefix,'between',[strtotime("-30 day"),time()]);
                break;
            default:
                if(strstr($where[$data],$field)!==false){
                    list($startTime, $endTime) = explode($field, $where[$data]);
                    $model = $model->where($prefix, '>', strtotime($startTime));
                    $model = $model->where($prefix, '<', bcadd(strtotime($endTime),86400,0));
                }
                break;
        }
        return $model;
    }
    /**
     * 获取去除html去除空格去除软回车,软换行,转换过后的字符串
     * @param string $str
     * @return string
     */
    public static function HtmlToMbStr($str){
        return trim(strip_tags(str_replace(["\n","\t","\r"," ","&nbsp;"],'',htmlspecialchars_decode($str))));
    }
    /**
     * 截取中文指定字节
     * @param string $str
     * @param int $utf8len
     * @param string $chaet
     * @param string $file
     * @return string
     */
    public static function getSubstrUTf8($str,$utf8len=100,$chaet='UTF-8',$file='....'){
        if(mb_strlen($str,$chaet)>$utf8len){
            $str=mb_substr($str,0,$utf8len,$chaet).$file;
        }
        return $str;
    }
    /**
     * 获取本季度 time
     * @param int|string $time
     * @param string $ceil
     * @return array
     */
    public static function getMonth($time='',$ceil=0){
        if($ceil!=0)
            $season = ceil(date('n') /3)-$ceil;
        else
            $season = ceil(date('n') /3);
        $firstday=date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));
        $lastday=date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));
        return array($firstday,$lastday);
    }
    /**
     * 高精度 加法
     * @param int|string $uid id
     * @param string $decField 相加的字段
     * @param float|int $dec 加的值
     * @param string $keyField id的字段
     * @param int $acc 精度
     * @return bool
     */
    public static function bcInc($key, $incField, $inc, $keyField = null, $acc=2)
    {
        if(!is_numeric($inc)) return false;
        $model = new self();
        if($keyField === null) $keyField = $model->getPk();
        $result = self::where($keyField,$key)->find();
        if(!$result) return false;
        $new = bcadd($result[$incField],$inc,$acc);
        return false !== $model->where($keyField,$key)->update([$incField=>$new]);
    }


    /**
     * 高精度 减法
     * @param int|string $uid id
     * @param string $decField 相减的字段
     * @param float|int $dec 减的值
     * @param string $keyField id的字段
     * @param bool $minus 是否可以为负数
     * @param int $acc 精度
     * @return bool
     */
    public static function bcDec($key, $decField, $dec, $keyField = null, $minus = false, $acc=2)
    {
        if(!is_numeric($dec)) return false;
        $model = new self();
        if($keyField === null) $keyField = $model->getPk();
        $result = self::where($keyField,$key)->find();
        if(!$result) return false;
        if(!$minus && $result[$decField] < $dec) return false;
        $new = bcsub($result[$decField],$dec,$acc);
        return false !== $model->where($keyField,$key)->update([$decField=>$new]);
    }

    /**
     * @param null $model
     * @return Model
     */
    protected static function getSelfModel($model = null)
    {
        return $model == null ? (new self()) : $model;
    }

}