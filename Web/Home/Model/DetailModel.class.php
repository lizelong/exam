<?php
namespace Home\Model;

use Think\Model;

class DetailModel extends Model
{
	/**
	 * 获取单个考试的考试详情
	 */
	public function getInfo()
	{
		$tid = I('tests_id');
        $tests = M('Tests')->find($tid);
        //判断是否需要手动审核
        if (empty($tests)) {
            return false;
        }
        $tests['rule'] = json_decode($tests['rule'], true);
        $tests['topics'] = json_decode($tests['info'], true);

        //查出当前考试详情
        $map['tests_id'] = $tid;
        $map['user_id'] = $_SESSION['homeInfo']['id'];
        $res = M('Detail')->where($map)->find();
        if ($res) {
            //格式化答案
            $tests['answer'] = json_decode($res['answer'], true);
            $tests['check_info'] = json_decode($res['check_info'], true);
            $tests['detail_id'] = $res['id'];

            // var_dump($tests);exit;
            return $tests;
        } else {
            return false;
        }
	}
}