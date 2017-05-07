<?php
namespace Admin\Controller;

class ErrorController extends CommonController {
    /**
     * 错题统计
     */
    public function index(){
        //查询出错的最多的前50条
        // $arr = M()->query('select id,topic_id,sum(num) nums,users from __ERROR__ group by topic_id order by nums desc limit 50;');

        if (I('tests_id')) $map['tests_id'] = I('tests_id');
        $arr = M('Error')
                ->field('id,topic_id,sum(num) nums,users')
                ->where($map)
                ->group('topic_id')
                ->order('nums desc')
                ->limit(50)
                ->select();

        $this->assign('list', $arr);
        $this->assign('tests_id', I('tests_id'));
        $this->display();
    }

    /**
     * 统计错误人姓名和错误次数
     * @param  题目ID $topic_id 要统计的题目ID
     */
    public function users($topic_id)
    {
        $map['topic_id'] = $topic_id;
        if (I('tests_id')) $map['tests_id'] = I('tests_id');
        $arr = M('Error')->where($map)->getField('users', true);

        $str = '';
        foreach ($arr as $v) {
            $str .= ltrim($v, ',');
        }
        $users = explode(',', rtrim($str, ','));
        $res = array_count_values($users);
        arsort($res);
        // var_dump($res);exit;
        $this->assign('list', $res);
        $this->display();
    }
}