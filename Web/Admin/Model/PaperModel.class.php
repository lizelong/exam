<?php
namespace Admin\Model;

use Think\Model;

class PaperModel extends Model
{
	//自动验证
	protected $_validate = [
		['des', 'require', '请输入试卷描述', 1]
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
	 * 获取并处理试卷列表数据
	 * @return array 处理好的试卷列表数据
	 */
	public function getData()
	{
		$arr = $this->alias('p')
					->field('p.*,u.username')
					->join('left join __USER__ u on p.create_by=u.id')
					->order('create_time desc')
					->select();

		foreach ($arr as &$v) {
			$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);

			$topics = json_decode($v['topics'], true);
			$str = '';
			if (count($topics[1]) > 0) $str .= '选择题：'.count($topics[1]).'道<br>';
			if (count($topics[2]) > 0) $str .= '判断题：'.count($topics[2]).'道<br>';
			if (count($topics[3]) > 0) $str .= '简答题：'.count($topics[3]).'道<br>';
			if (count($topics[4]) > 0) $str .= '编程题：'.count($topics[4]).'道<br>';
			if (count($topics[5]) > 0) $str .= '填空题：'.count($topics[5]).'道<br>';
			$v['topicNum'] = $str;
		}
		return $arr;
	}

	/**
	 * 统计session中各种类型题目的数量
	 * @return array 统计好数量的数组
	 */
	public function countNum()
	{
		$arr = $_SESSION['topicList'];
		$total = 0;
		foreach ($arr as $v) {
			$total += count($v);
		}
		$data['total'] = $total;
		$data['num1'] = count($arr[1]);
		$data['num2'] = count($arr[2]);
		$data['num3'] = count($arr[3]);
		$data['num4'] = count($arr[4]);
		$data['num5'] = count($arr[5]);
		return $data;
	}

	/**
	 * 获取session中的所有题目并json_encode
	 */
	public function getTopics()
	{
		$arr = $_SESSION['topicList'];
		foreach ($arr as $k=>&$v) {
			if (empty($v)) {
				unset($arr[$k]);
				continue;
			}
			ksort($v);
		}

		if (empty($arr)) {
			return false;
		} else {
			ksort($arr);
			$str = json_encode($arr);
			$info = $this->where(["topics"=>$str])->find();
			if ($info) {
				return $info['id'];
			} else {
				return $str;
			}
		}
	}

	/**
	 * 根据session中保存的题目id的格式，还原为所有题目内容
	 * @return array  查询并处理好的数组
	 */
	public function getAll($id = false, $flag = true)
	{
		if ($id) {
            $info = $this->find($id);
            $arr = json_decode($info['topics'], true);

            if ($flag) $_SESSION['topicList'] = $arr;
		} else {
			$arr = $_SESSION['topicList'];
		}
        foreach ($arr as $k=>$v) {
            if (!empty($v)) {
                $arr[$k] = M('Topic')->field('id,type_id,type,title,answer,option')->where(['id'=>['in', join(',', $v)]])->select();
            }
        }
        //用于插入tests表做数据冗余
        if (!$flag) return json_encode($arr);
        
        $info['topics'] = $arr;
        return $info;
	}
}