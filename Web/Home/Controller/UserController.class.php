<?php
namespace Home\Controller;

class UserController extends CommonController {
    /**
     * 添加用户
     */
    public function add()
    {
    	if (IS_POST) {
    		//实例化
            $model = D('User');
            //判断用户名是否存在
            if (M('User')->where(['username'=>I('username')])->find()) {
                $this->error('用户名已存在');
            }
            $data = $model->create();
            if ($data) {
                // var_dump($data);exit;
                $res = $model->add($data);
                if ($res) {
                    $this->success('添加成功', U('Login/login'));
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error($model->getError());
            }
    	} else {
            //判断是否登录
            if (session('?homeInfo')) {
                $this->redirece('index');
            }
            //查出可以开放注册的班级列表
            $arr = M('Class')->where(['is_reg'=>2])->order('create_time desc')->select();

            $this->assign('classList', $arr);
    		$this->display();
    	}
    }

    /**
     * 编辑用户
     */
    public function edit($id)
    {
        if (IS_POST) {
            //实例化
            $model = D('User');
            $data = $model->create();
            if ($data) {
                //判断没修改密码
                if (strlen($_POST['pwd']) <= 0) unset($data['pwd']);
                // var_dump($data);exit;
                $res = $model->save($data);
                if ($res) {
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                }
            } else {
                $this->error($model->getError());
            }
        } else {
            //当前用户信息
            $info = M('User')->find($id);
            $this->assign('info', $info);

            $this->display();
        }
    }
}