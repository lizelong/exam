<?php
namespace Home\Controller;

/**
 * 空控制器
 */
class EmptyController extends CommonController {
	/**
	 * 空方法直接跳到退出页面
	 */
    public function _empty(){
        $this->redirect('Login/logout');
    }
}