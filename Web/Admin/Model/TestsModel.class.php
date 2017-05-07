<?php
namespace Admin\Model;

use Think\Model;

class TestsModel extends Model
{
	//自动验证
	protected $_validate = [
		['class_id', 'require', '请选择考试'],
		['paper_id', 'require', '木有正确的试卷'],
		['des', 'require', '请输入考试描述'],
		['type', 'require', '请选择考试类型']
	];

	//自动完成
	protected $_auto = [
		//创建数据时自动完成
		['create_time', 'time', 1, 'function'],
		['create_by', "getId", 1, 'callback'],

		//更新数据时自动完成
		['edit_time', 'time', 3, 'function'],
		['edit_by', "getId", 3, 'callback'],
	];

	/**
	 * 获取session中的用户id，用于自动完成
	 */
	protected function getId()
	{
		return $_SESSION['adminInfo']['id'];
	}

	/**
	 * 根据考试类型获取计分或计数规则（后期或许可以优化）
	 */
	public function getRule($data)
	{
		$str = M('Paper')->where(['id'=>$data['paper_id']])->getField('topics');
		$arr = json_decode($str, true);
		if ($data['type'] == 1) {
			foreach ($arr as $k=>$v) {
				$rule[$k] = count($v);
			}
		} else {
			foreach ($arr as $k=>$v) {
				$rule[$k][] = count($v);
				$rule[$k][] = $_POST["fen{$k}"];
			}
		}
		// var_dump($rule);exit;
		return json_encode($rule);
	}

	/**
	 * 获取并处理考试列表数据
	 * @return array 处理好的考试列表数据
	 */
	public function getData()
	{
		$arr = $this
				->alias('t')
				->field('t.*,c.name class_name')
				->join('left join __CLASS__ c on t.class_id=c.id')
				->select();

		$type = [1=>'计数', '计分'];
		foreach ($arr as &$v) {
			//添加时间
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);

			//总分或者总个数
			$rule = json_decode($v['rule'], true);
			$total = 0;
			if ($v['type'] == 1) {
				//计数
				foreach ($rule as $val) {
					$total += $val;
				}
				$total = '总个数：'.$total;
			} else {
				//计分
				foreach ($rule as $val) {
					$total += $val[0] * $val[1];
				}
				$total = '总分数：'.$total;
			}
			$v['total'] = $total;

			//处理考试类型
			$v['type'] = $type[$v['type']];
		}

		return $arr;
	}
}