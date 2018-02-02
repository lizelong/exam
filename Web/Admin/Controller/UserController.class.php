<?php
namespace Admin\Controller;

class UserController extends CommonController {
    /**
     * 用户列表
     */
    public function index(){
        $map['is_del'] = 1;//未删除的
        //搜索条件
        if (I('id')) $map['u.id'] = I('id');
        if (I('class_id')) $map['class_id'] = I('class_id');
        if (I('username')) $map['username'] = ['like', '%'.I('username').'%'];

        //是否显示其他老师的信息
        if ($_SESSION['adminInfo']['id'] != 1) {
            $map['u.role|u.id'] = [['neq', 2], $_SESSION['adminInfo']['id'], '_multi'=>true];
        }

        $model = D('User')->alias('u');
        //分页
        $page = $this->page($model->where($map)->count());

        //查用户数据
        $arr = $model->where($map)->limit($page['limit'])->getData();
        $this->assign('list', $arr);
        $this->assign('btn', $page['show']);//分页按钮

        //ajax分页
        if (IS_AJAX) {
            $this->display('ajaxUser');exit;
        }

        //查出所有班级，用于搜索
        $classList = M('class')->order('name desc')->select();
        $this->assign('classList', $classList);

        $this->display();
    }

    /**
     * 添加用户
     */
    public function add()
    {
    	if (IS_POST) {
    		//实例化
            $model = D('User');
            $data = $model->create();
            if ($data) {
                //判断是否是教师的角色
                if ($data['class_id'] === '0') {
                    $data['role'] = 2;
                }
                // var_dump($data);exit;
                $res = $model->add($data);
                if ($res) {
                    $this->success('添加成功', U('index'));
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error($model->getError());
            }
    	} else {
            $arr = M('Class')->order('create_time desc')->select();

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
            //所有班级信息
            $arr = M('Class')->order('create_time desc')->select();
            $this->assign('classList', $arr);

            //当前用户信息
            $info = M('User')->find($id);
            $this->assign('info', $info);

            $this->display();
        }
    }

    /**
     * 展示用户详情
     * @param  int $id 班级id
     */
    public function info($id)
    {
        $info = M('User')->find($id);
        if (empty($info)) {
            echo '<h3>sorry，该用户可能已被删除~</h3>';
            exit;
        }
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * ajax删除用户
     * @param  int $id 要删掉的id
     */
    public function del($id)
    {
        if (IS_AJAX) {
            // $res = M('User')->delete($id);
            $res = M('User')->save(['id'=>$id, 'is_del'=>2, 'status'=>2]);
            if ($res) {
                //顺带删除考试详情
                M('Detail')->where(['user_id'=>$id])->delete();
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->redirect('index');
        }
    }
}