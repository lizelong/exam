<?php
namespace Home\Model;

use Think\Model;

class TestsModel extends Model
{
	/**
	 * 获取并处理当前用户的考试列表数据
	 * @return array 处理好的考试列表数据
	 */
	public function getData()
	{
		$map['class_id'] = $_SESSION['homeInfo']['class_id'];
		$arr = $this
			->alias('t')
			->field('t.*, c.name class_name')
			->where($map)
			->join('left join __CLASS__ c on t.class_id=c.id')
			->select();

		$type = [1=>'计数', '计分'];
		foreach ($arr as &$v) {
			//添加时间
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);

			//用于统计当前考试分数
			$d = M('Detail')->where(['tests_id'=>$v['id'], 'user_id'=>$_SESSION['homeInfo']['id']])->find();
			// var_dump($d);exit;
			
			//总分或者总个数
			$rule = json_decode($v['rule'], true);
			$total = 0;
			if ($v['type'] == 1) {
				//计数
				foreach ($rule as $val) {
					$total += $val;
				}
				$v['total_h'] = $d['score_auto'] + $d['score_check'];
				$total = '总个数：'.$total;
			} else {
				//计分
				foreach ($rule as $val) {
					$total += $val[0] * $val[1];
				}
				$v['total_h'] = round(100/$total*($d['score_auto'] + $d['score_check']), 1);
				$total = '总分数：'.$total;
			}
			$v['total'] = $total;

			//处理考试类型
			$v['type'] = $type[$v['type']];
		}

		return $arr;
	}
}