<?php
namespace Admin\Model;

use Think\Model;

class UserModel extends Model
{
	//自动验证
	protected $_validate = [
		['class_id', 'require', '请选择班级', 1],
		['username', 'require', '请填写用户名称', 1],
		['pwd', 'require', '请填写登录密码', 2],
		['pwd2', 'pwd','两次密码不一致',0,'confirm'],
	];

	//自动完成
	protected $_auto = [
		['pwd', 'md5', 3, 'function'],
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
	 * 获取并处理班级列表数据
	 * @return array 处理好的班级列表数据
	 */
	public function getData()
	{
		//联查
		$arr = $this
				->alias('u')
				->field('u.id,u.username,u.role,u.sex,u.create_time,c.name class_id')
				->join('left join __CLASS__ c on u.class_id=c.id')
				->order('u.role desc, u.create_time desc')
				->select();

		$sex = ['女', '男'];

		foreach ($arr as &$v) {
			$v['sex'] = $sex[ $v['sex'] ];
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
			if (empty($v['class_id'])) {
				$v['class_id'] = '教师';
			}
		}
		return $arr;
	}
}