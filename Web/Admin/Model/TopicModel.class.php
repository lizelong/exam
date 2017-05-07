<?php
namespace Admin\Model;

use Think\Model;

class TopicModel extends Model
{
	//自动验证
	protected $_validate = [
		['type_id', 'require', '请选择分类'],
		['type', 'require', '请选择题目类型'],
		['title', 'require', '请输入题目标题'],
		['answer', 'require', '请输入答案'],
	];

	//自动完成
	protected $_auto = [
		['option', 'json_encode', 3, 'function'],
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
		$arr = $this
				->alias('top')
				->field('top.*,t.name type_id')
				->join('left join __TYPE__ t on top.type_id=t.id')
				->select();

		$types = [1=>'选择题', '判断题', '简答题', '编程题', '填空题'];
		$typeTable = M('Type');
		foreach ($arr as &$v) {
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
			//type类型
			$v['type_lx'] = $types[ $v['type'] ];

			//处理标题
			if (strpos($v['title'], '<pre>') !== false) {
				$v['title'] = substr($v['title'], 0, strpos($v['title'], '<pre>'));
			}
			//处理答案
			if (strpos($v['answer'], '<pre>') !== false) {
				$v['answer'] = substr($v['answer'], 0, strpos($v['answer'], '<pre>'));
			}

			//查多少条数据就会循环查多少次type表，后期可以尝试着优化一下！
			// $v['type_id'] = $typeTable->where(['id'=>$v['type_id']])->getField('name');
		}
		return $arr;
	}
}