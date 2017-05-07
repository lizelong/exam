<?php
namespace Admin\Controller;

class TypeController extends CommonController {
    public function index(){
        $model = D('Type');

        $arr = $model->order('concat(path, id)')->getData();

        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 添加分类
     */
    public function add()
    {
    	if (IS_POST) {
    		//实例化Type表
            $model = D('Type');
            $data = $model->create();
            if ($data) {
                if ($data['pid'] !== '0') {
                    $data['path'] .= $data['pid'].',';
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
            $arr = M('Type')->where('pid=0')->select();

            $this->assign('list', $arr);
    		$this->display();
    	}
    }

    /**
     * 编辑分类
     */
    public function edit($id)
    {
        if (IS_POST) {
            //实例化class表
            $model = D('Type');
            $data = $model->create();
            if ($data) {
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
            //当前分类信息
            $info = M('Type')->find($id);
            $this->assign('info', $info);

            $this->display();
        }
    }

    /**
     * ajax删除分类
     * @param  int $id 要删掉的id
     */
    public function del($id)
    {
        if (IS_AJAX) {
            if (M('Type')->where(['pid'=>$id])->find()) {
                $this->error('请先删除子类');
            } else {
                $res = M('Type')->delete($id);
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
}