<?php
namespace Common\Model;

use Think\Model;

class ErrorModel extends Model
{
	/**
     * 统计错误的题目信息
     * @param  int $tests_id 考试ID
     * @param  int $topic_id 题目ID
     * @param  int $user_id  用户ID
     */
    public function countError($tests_id, $topic_id, $user_id = 0)
    {
        $map['tests_id'] = $tests_id;
        $map['topic_id'] = $topic_id;

        //获取错误人姓名
        if ($user_id > 0) {
            //查询用户姓名
            $name = M('User')->where('id='.$user_id)->getField('username');
        } else {
            //前台用户
            $name = $_SESSION['homeInfo']['username'];
        }
        if ($info = $this->where($map)->find()) {
            //已经存在，将数量加1并拼接错误人姓名
            $data['num']    = $info['num']+1;
            $data['users']  = $info['users'].$name.',';
            $data['id']     = $info['id'];
            $this->save($data);
        } else {
            //不存在
            $map['num']     = 1;
            //两边加,为了用like统计每个用户错了哪些题：
            //where users like %,用户名,%
            $map['users']   = ','.$name.',';
            $this->add($map);
        }
    }
}