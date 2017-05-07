<?php
namespace Admin\Model;

use Think\Model;

class DetailModel extends Model
{
	/**
	 * 获取考试详情信息的列表
	 * @return array 处理好的考试详情
	 */
	public function getData()
	{
		$arr = M('Detail')->alias('d')
			->field('d.*,u.username')
			->join('left join __USER__ u on d.user_id=u.id')
			->where(['tests_id'=>I('id')])
			->select();

		$tests = M('Tests')->field('rule, type')->find(I('id'));

		//判断非法访问
		if (empty($arr) && empty($tests)) return false;
		$rule = json_decode($tests['rule'], true);
		$total = 0;	//总数
		$total_auto = 0;	//自动
		$total_check = 0;	//手动
		if ($tests['type'] == 1) {
			//计数
			foreach ($rule as $k=>$val) {
				if ($k < 3) {
					$total_auto += $val;
				} else {
					$total_check += $val;
				}
				$total += $val;
			}
		} else {
			//计分
			foreach ($rule as $k=>$val) {
				if ($k < 3) {
					$total_auto += $val[0] * $val[1];
				} else {
					$total_check += $val[0] * $val[1];
				}
				$total += $val[0] * $val[1];
			}
		}

		foreach ($arr as $k=>&$v) {
			//处理考试时间
			if ($v['end_time'] == -1) {
				$v['tests_time'] = '<span class="c-red">强制提交</span>';
			} elseif ($v['end_time'] == $v['start_time']) {
				$v['tests_time'] = f_time(time()-$v['start_time']);
			} else {
				$v['tests_time'] = f_time($v['end_time']-$v['start_time']);
			}

			//处理百分制
			if ($tests['type'] == 2) {
				$v['total_h'] = round(100/$total*($v['score_auto'] + $v['score_check']), 1);
			} else {
				$v['total_h'] = '计数';
			}
			$v['total'] = $total;
			$v['total_auto'] = $total_auto;
			$v['total_check'] = $total_check;

			$id_ok[] = $v['user_id'];
		}

		//查出班级里面所有的学生，用于判断哪些学生还没考试
		$map['class_id'] = I('class_id');
		if (!empty($id_ok)) {
			$map['id'] = ['not in', $id_ok];
		}
		$names = M('User')->where($map)->getField('id,username', true);
		$arr[] = $names;
		return $arr;
	}

	/**
	 * 获取单个考试的考试详情
	 */
	public function getInfo($id)
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
        $map['id'] = $id;
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

	/**
	 * 强制提交并自动评分的方法
	 * @param  int $id 要强制的详情ID
	 * @return boolean 提交成功返回true，失败返回false
	 */
	public function submit($info, $tests)
	{
        $topics = json_decode($tests['info'], true);

        $answer = json_decode($info['answer'], true);
        //判断选择题
        $x_num = 0;
        foreach ($topics[1] as $v) {
            $name = 'a_'.$v['id'];
            if (isset($answer[$name]) && strtolower($v['answer']) == strtolower($answer[$name])) {
                $x_num++;
            } else {
            	//错了，记录错误信息
            	D('Error')->countError($tests['id'], $v['id'], $info['user_id']);
            }
        }

        //判断判断题
        $p_num = 0;
        foreach ($topics[2] as $v) {
            $name = 'a_'.$v['id'];
            if (isset($answer[$name]) && strtolower($v['answer']) == strtolower($answer[$name])) {
                $p_num++;
            } else {
            	//错了，记录错误信息
            	D('Error')->countError($tests['id'], $v['id'], $info['user_id']);
            }
        }

        // var_dump($x_num, $p_num);
        //判断是计数还是计分
        if ($tests['type'] == 1) {
            //计数
            $score_auto = $x_num + $p_num;
        } else {
            //格式化考试的计分规则
            $rule = json_decode($tests['rule'], true);
            $score_auto = $x_num * $rule[1][1];//选择题
            $score_auto += $p_num * $rule[2][1];//判断题
        }

        //判断是否只有选择题和判断题，如果是，则状态为已完成
        if (empty($topics[3])&&empty($topics[4])&&empty($topics[5])) {
            $data['status'] = 3;
        } else {
            $data['status'] = 2;
        }
        $data['score_auto'] = $score_auto;
        $data['end_time'] = -1;//强制提交
        $data['id'] = $info['id'];
        
        // var_dump($data);exit;
        $res = M('Detail')->save($data);
        // echo M('Detail');
        if ($res) {
            return true;
        } else {
            return false;
        }
	}
}