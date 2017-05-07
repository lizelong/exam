<?php
namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller {
    public function index(){
        if (IS_POST) {
        	$map['username'] = I('username');
        	$map['pwd'] = md5(I('pwd'));
        	$info = M('User')->where($map)->find();
        	if ($info) {
        		if ($info['role'] != 2) {
        			//记录日志：Runtime\Logs\Admin
        			\Think\Log::write('学员《'.$info['username'].'》异常登录');
        			$this->error('你没有权限，我们已记录你的IP和设备号，请不要多次尝试', '', 5);
        			exit;
        		}
        		if ($info['status'] != 1) {
        			$this->error('你已被禁用');
        			exit;
        		}

        		//登录成功
        		$_SESSION['adminInfo'] = $info;
        		if (isset($_POST['online'])) {
        			//记住状态1天
        			setCookie(session_name(), session_id(), time()+3600*24, '/');
        		}
        		$this->success('登录成功', U('Index/index'));
        	} else {
        		$this->error('用户名或密码错误');
        	}
        } else {
            session('adminInfo', null);
            setCookie(session_name(), session_id(), -1, '/');
        	$this->display();
        }
    }

    /**
     * 退出登录，记得删除cookie
     */
    public function logout()
    {
        session('adminInfo', null);
        setCookie(session_name(), session_id(), -1, '/');
        $this->redirect('index');
    }

    public function _empty(){
        $this->redirect('Login/logout');
    }
}