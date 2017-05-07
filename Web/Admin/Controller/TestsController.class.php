<?php
namespace Admin\Controller;

class TestsController extends CommonController {
    public function index(){
        $model = D('Tests');

        $arr = $model->getData();

        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 添加考试
     */
    public function add()
    {
    	if (IS_POST) {
    		//实例化Tests表
            $model = D('Tests');
            $map['status'] = 1;
            $map['class_id'] = I('class_id');
            $res = $model->where($map)->find();
            if ($res) {
                $this->error('该班级已经在考试中，请先结束考试');
                exit;
            }
            $data = $model->create();
            if ($data) {
                //获取规则
                $data['rule'] = $model->getRule($data);
                //将试卷题目写入考试表做冗余
                $data['info'] = D('Paper')->getAll($data['paper_id'], false);
                // var_dump($data);exit;
                $res = $model->add($data);
                if ($res) {
                    $this->success('开始考试', U('index'));
                } else {
                    $this->error('开始失败');
                }
            } else {
                $this->error($model->getError());
            }
    	} else {
            //查出当前要考的试卷信息
            $id = I('paper_id');
            if (!$id) $this->redirect('index');
            $info = M('Paper')->find($id);
            $info['topics'] = json_decode($info['topics'], true);

            //查出所有的班级信息
            $classList = M('Class')->order('name desc')->select();

            $this->assign('classList', $classList);
            $this->assign('info', $info);
    		$this->display();
    	}
    }

    /**
     * ajax删除考试
     * @param  int $id 要删掉的id
     */
    public function del($id)
    {
        if (IS_AJAX) {
            if (I('del') === 'ok') {//强制删除
                M('Detail')->startTrans();
                M('Tests')->startTrans();
                $res = M('Detail')->where(['tests_id'=>$id])->delete();
                $res2 = M('Tests')->delete($id);

                if ($res && $res2) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } elseif (M('Detail')->where(['tests_id'=>$id])->find()) {//有详情是否确认删除
                $this->ajaxReturn(['info'=>'该测试下有学生考试详情，确认删除？！', 'status'=>5]);
            } else {//直接删除
                $res = M('Tests')->delete($id);
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
     * 结束考试
     * @param  int $id 要结束的考试id
     */
    public function stop($id)
    {
        $res = M('Tests')->where(['id'=>$id])->save(['status'=>2]);
        if ($res) {
            //查询当前考试信息
            $tests = M('Tests')->find($id);
            $d = D('Detail');
            //开启事务
            $d->startTrans();
            $arr = $d->where('status=1')->select();
            foreach ($arr as $v) {
                if ($d->submit($v, $tests) === false) {
                    $d->rollback();//回滚事务
                    $this->error('考试结束失败');
                }
            }
            //提交事务
            $d->commit();
            $this->success('考试已结束');
        } else {
            $this->error('考试结束失败');
        }
    }

    /**
     * 查看考试详情
     * @param  int $id 要查看的考试ID
     */
    public function detail($id)
    {
        $model = D('Tests');
        $arr = $model->getDetail();

        //获取未开始考试的童鞋
        $not = array_pop($arr);
        // var_dump($not);exit;
        $this->assign('not', $not);
        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 强制提交
     * @param  int $id 要强制结束的详情id
     */
    public function submitOk($id)
    {
        $res = M('Detail')->where(['id'=>$id])->save(['status'=>2]);
        if ($res) {
            $this->success('已强制提交');
        } else {
            $this->error('强制提交失败');
        }
    }

    /**
     * 修改是否开放给学员审阅
     */
    public function is_check()
    {
        if (IS_AJAX) {
            $data['id'] = I('id');
            $data['is_check'] = (I('flag')==1)?2:1;
            $res = M('Tests')->save($data);
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }        
    }

    /**
     * 修改是否给学员查看分数
     */
    public function is_score()
    {
        if (IS_AJAX) {
            $data['id'] = I('id');
            $data['is_score'] = (I('flag')==1)?2:1;
            $res = M('Tests')->save($data);
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }        
    }
}