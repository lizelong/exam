<?php
namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller {
    public function _initialize(){
        //判断是否登录
        if (!session('?adminInfo')) {
        	$this->redirect('Login/index');
        }
    }

    /**
     * 空操作直接跳转到退出页面
     */
    public function _empty(){
        $this->redirect('Login/logout');
    }
}