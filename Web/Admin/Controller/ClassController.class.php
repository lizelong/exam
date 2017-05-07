<?php
namespace Admin\Controller;

class ClassController extends CommonController {
    /**
     * 班级列表
     */
    public function index(){
        $cla = D('Class');

        $arr = $cla->getData();

        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 添加班级
     */
    public function add()
    {
    	if (IS_POST) {
            //实例化class表
            $cla = D('Class');
            $data = $cla->create();
            if ($data) {
                // var_dump($data);exit;

                $res = $cla->add($data);
                if ($res) {
                    //会自动判断是否是ajax请求
                    //如果是则会调用$this->ajaxReturn()
                    $this->success('添加成功', U('Class/index'));
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error($cla->getError());
            }
    	} else {
    		$this->display();
    	}
    }

    /**
     * 编辑班级
     */
    public function edit($id)
    {
        if (IS_POST) {
            //实例化class表
            $cla = D('Class');
            $data = $cla->create();
            if ($data) {
                $res = $cla->save($data);
                if ($res) {
                    //会自动判断是否是ajax请求
                    //如果是则会调用$this->ajaxReturn()
                    $this->success('修改成功', U('Class/index'));
                } else {
                    $this->error('修改失败');
                }
            } else {
                $this->error($cla->getError());
            }
        } else {
            $info = M('Class')->find($id);
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 展示班级详情
     * @param  int $id 班级id
     */
    public function info($id)
    {
        $info = M('Class')->find($id);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * ajax删除班级
     * @param  int $id 要删掉的id
     */
    public function del($id)
    {
        if (IS_AJAX) {
            //判断班级里面是否有学生
            $user = M('User')->where(['class_id'=>$id])->find();
            if ($user) {
                $this->error('该班级还有学生呢');
            } else {
                $res = M('Class')->delete($id);
                if ($res) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            }
        } else {
            $this->redirect('index');
        }
    }

    /**
     * 修改是否开放注册
     */
    public function is_reg()
    {
        if (IS_AJAX) {
            $data['id'] = I('id');
            $data['is_reg'] = (I('reg')==1)?2:1;
            $res = M('Class')->save($data);
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }        
    }
}