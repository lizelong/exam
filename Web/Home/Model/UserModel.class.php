<?php
namespace Home\Model;

use Think\Model;

class UserModel extends Model
{
	//自动验证
	protected $_validate = [
		['class_id', 'require', '请选择班级', 1, 'regex', 1],
		['username', 'require', '请填写用户名称', 1],
		['pwd', 'require', '请填写登录密码', 2],
		['pwd2', 'pwd','两次密码不一致',0,'confirm'],
	];

	//自动完成
	protected $_auto = [
		['pwd', 'md5', 3, 'function'],
		//创建数据时自动完成
		['create_time', 'time', 1, 'function'],
		['create_by', 0],

		//更新数据时自动完成
		['edit_time', 'time', 3, 'function'],
		['edit_by', "getId", 3, 'callback'],
	];

	/**
	 * 获取session中的用户id，用于自动完成
	 */
	protected function getId()
	{
		return $_SESSION['homeInfo']['id']?$_SESSION['homeInfo']['id']:0;
	}
}