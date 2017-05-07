<?php
namespace Admin\Controller;

class IndexController extends CommonController {
    public function index(){
        $this->display();
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