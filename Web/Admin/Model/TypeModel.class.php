<?php
namespace Admin\Model;

use Think\Model;

class TypeModel extends Model
{
	//自动验证
	protected $_validate = [
		['name', 'require', '请输入分类名']
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
	 * 获取并处理分类树数据
	 * @return array 处理好的分类树数据
	 */
	public function getData()
	{
		$arr = $this->field(true)->select();

		foreach ($arr as &$v) {
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);

			//处理分类树
			$num = substr_count($v['path'], ',');
			$v['name'] = '┖--'.str_repeat('--', ($num-1)*2).' '.$v['name'];
		}
		return $arr;
	}
}