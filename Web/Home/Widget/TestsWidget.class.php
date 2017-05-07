<?php
namespace Home\Widget;

class TestsWidget extends \Think\Controller {
	/**
	 * 根据传的数组参数，展示试卷的扩展
	 * @param  arr $arr 包含试卷详情的数组
	 */
	public function _tests($arr)
	{
		$this->assign('tests', $arr);
		$this->display('Detail/_tests');
	}
}