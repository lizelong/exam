<?php
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller {
    public function _initialize(){
        //判断是否登录
        if (!session('?homeInfo') && CONTROLLER_NAME.'/'.ACTION_NAME !== 'User/add') {
        	$this->redirect('Login/index');
        }

        //判断如果有考试在进行，就直接进入考试
        $map['class_id'] = $_SESSION['homeInfo']['class_id'];
        $map['status'] = 1;
        $tests = M('Tests')->where($map)->find();
        // echo M('Tests')->_sql();
        // var_dump($tests);exit;
        if ($tests) {
            $this->success('班级正在考试中，请稍等...', U('Detail/doing'));
            exit;
        }
    }

    /**
     * 空操作直接跳转到退出页面
     */
    public function _empty(){
        $this->redirect('Login/logout');
    }
}