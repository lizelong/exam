<?php
namespace Admin\Controller;

class IndexController extends CommonController {
    public function index(){
        //获取用户、题库等统计信息
        $data = $this->getData();

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 获取统计数据
     * @return array 处理好的数据数组
     */
    public function getData()
    {
        $arr = [];
        $user = M('User');
        $topic = M('topic');
        $paper = M('paper');
        $detail = M('detail');
        //日
        $arr['用户'][] = $user->where('create_time>'.strtotime(date('Y-m-d')))->count();
        $arr['题库'][] = $topic->where('create_time>'.strtotime(date('Y-m-d')))->count();
        $arr['试卷数'][] = $paper->where('create_time>'.strtotime(date('Y-m-d')))->count();
        $arr['测试人次'][] = $detail->where('start_time>'.strtotime(date('Y-m-d')))->count();

        //周
        $arr['用户'][] = $user->where('create_time>'.strtotime('-1week'))->count();
        $arr['题库'][] = $topic->where('create_time>'.strtotime('-1week'))->count();
        $arr['试卷数'][] = $paper->where('create_time>'.strtotime('-1week'))->count();
        $arr['测试人次'][] = $detail->where('start_time>'.strtotime('-1week'))->count();

        //月
        $arr['用户'][] = $user->where('create_time>'.strtotime('-1month'))->count();
        $arr['题库'][] = $topic->where('create_time>'.strtotime('-1month'))->count();
        $arr['试卷数'][] = $paper->where('create_time>'.strtotime('-1month'))->count();
        $arr['测试人次'][] = $detail->where('start_time>'.strtotime('-1month'))->count();

        //获取总数
        $arr['用户'][] = $user->count();
        $arr['题库'][] = $topic->count();
        $arr['试卷数'][] = $paper->count();
        $arr['测试人次'][] = $detail->count();
        return $arr;
    }

    /**
     * 修改bug，将未转义的答案用htmlspecialchars转义一下
     */
    public function bug()
    {
    	$model = M('Detail');
    	$arr = $model->field('id,answer')->select();
    	// var_dump($arr);
    	foreach ($arr as $v) {
    		$answer = json_decode($v['answer'], true);
    		foreach ($answer as &$value) {
    			$value = htmlspecialchars($value);
    			// var_dump($value);
    		}
    		$data['answer'] = json_encode($answer);
    		$data['id'] = $v['id'];
    		$res = $model->save($data);
    		var_dump($res);
    	}
    }
}