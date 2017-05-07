<?php
namespace Admin\Model;

use Think\Model;

class ClassModel extends Model
{
	//自动验证
	protected $_validate = [
		['name', 'require', '请填写班级名称'],
		['start_time', 'require', '请选择开班时间'],
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

	//字段映射
	protected $_map = [
		'startTime' => 'start_time',
	];

	/**
	 * 获取session中的用户id，用于自动完成
	 */
	protected function getId()
	{
		return $_SESSION['adminInfo']['id'];
	}

	/**
	 * 获取并处理班级列表数据
	 * @return array 处理好的班级列表数据
	 */
	public function getData()
	{
		$arr = $this->select();

		$sta = [
			1=>'<span class="label label-default radius">即将开班</span>', 
			'<span class="label label-success radius">正常上课</span>', 
			'<span class="label label-warning radius">已经毕业</span>'
		];

		foreach ($arr as &$v) {
			$v['status'] = $sta[ $v['status'] ];
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
		}
		return $arr;
	}
}