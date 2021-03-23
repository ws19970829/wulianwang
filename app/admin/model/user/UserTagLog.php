<?php
/**
 * UserTagLog.php
 * desc:
 * created on  2020/9/25 12:04 AM
 * Created by caogu
 */

namespace app\admin\model\user;


use crmeb\basic\BaseModel;

class UserTagLog extends BaseModel
{

    protected $name='user_tag_log';

    /**
     * 获取用户的标签
     * @param $uid
     * @param string $get_type
     * @return array|string
     */
    public static function getTagsByUID($uid,$get_type='array'){
        $tags_list=self::where('uid','=',$uid)
            ->with('withUserTag')
            ->field('tag_id')
            ->select();
        if(!$tags_list){
            return $get_type=='array'?[]:'';
        }
        $tags_list=$tags_list->toArray();
        $tags_arr=[];
        $tags_str='';
        foreach($tags_list as $val){
            if(!isset($val['withUserTag'])){
                continue;
            }

            if($get_type=='array'){
                $tem=[
                    'tag_id'=>$val['tag_id'],
                    'tag_title'=>$val['withUserTag']['title']
                ];
                $tags_arr[]=$tem;
            }else{
                $tags_str.=$val['withUserTag']['title'].'/';
            }
        }


        $tags_str=trim($tags_str,'/');

        return $get_type=='array'?$tags_arr:$tags_str;

    }

    public function withUserTag(){
        return $this->belongsTo('UserTag','tag_id','id');
    }

    public static function getNewstTagByUID($uid){
        $info=  self::where('uid','=',$uid)
            ->order('id','desc')
            ->with('withUserTag')
            ->field('tag_id')
            ->find();
        if($info){
            $info=$info->toArray();
            return $info['withUserTag']['title'];
        }
        return '';
    }
}