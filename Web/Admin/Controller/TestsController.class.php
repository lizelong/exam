<?php
namespace Admin\Controller;

class TestsController extends CommonController {
    public function index(){
        $model = D('Tests');
        //搜索条件
        if (I('class_id')) $map['class_id'] = I('class_id');
        if (I('des')) $map['des'] = ['like', '%'.I('des').'%'];
        //分页
        $page = $this->page($model->where($map)->count());

        $arr = $model->where($map)->limit($page['limit'])->getData();
        
        $this->assign('list', $arr);
        $this->assign('btn', $page['show']);//分页按钮

        //ajax分页
        if (IS_AJAX) {
            $this->display('ajaxTests');exit;
        }

        //查出所有班级，用于搜索
        $classList = M('class')->order('name desc')->select();
        $this->assign('classList', $classList);

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
                $data['start_by'] = $_SESSION['adminInfo']['id'];
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
            $p = M('Paper');
            $info = $p->find($id);
            $info['topics'] = json_decode($info['topics'], true);

            //过滤被删掉的题目ID
            $model = M('Topic');
            foreach ($info['topics'] as &$v) {
                $v = $model->where(['id'=>['in', join($v, ',')]])->getField('id', true);
            }
            $p->save([
                    'topics'=>json_encode($info['topics']),
                    'id'=>$id
                    ]);

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

    /**
     * 修改考试时间
     */
    public function editTime()
    {
        if (IS_POST) {
            if (M('Tests')->save($_POST)) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->assign('tests_id', I('tests_id'));
            $this->assign('time', I('time'));
            $this->display();
        }
    }

    /**
     * 更新当前次考试的试卷，并重新审核选择题和判断题
     * @param  int $id 要更新的考试ID
     */
    public function recheck($id)
    {
        $data['info'] = D('Paper')->getAll($_POST['paper_id'], false);
        $data['id'] = $id;
        $t = M('Tests');
        $res = $t->save($data);

        //确实更新了试卷，才开始重新审核
        if ($res) {
            $tests = $t->find($id);
            //还原试卷信息
            $topics = json_decode($tests['info'], true);

            //查出当前次考试的所有详情
            $d = M('Detail');
            $arr = $d->where(['tests_id'=>$id])->select();

            foreach ($arr as $val) {
                $answer = json_decode($val['answer'], true);
                //判断选择题
                $x_num = 0;
                foreach ($topics[1] as $v) {
                    $name = 'a_'.$v['id'];
                    if (isset($answer[$name]) && strtolower($v['answer']) == strtolower($answer[$name])) {
                        $x_num++;
                    }
                }

                //判断判断题
                $p_num = 0;
                foreach ($topics[2] as $v) {
                    $name = 'a_'.$v['id'];
                    if (isset($answer[$name]) && strtolower($v['answer']) == strtolower($answer[$name])) {
                        $p_num++;
                    }
                }

                //判断是计数还是计分
                if ($tests['type'] == 1) {
                    //计数
                    $score_auto = $x_num + $p_num;
                } else {
                    //格式化考试的计分规则
                    $rule = json_decode($tests['rule'], true);
                    $score_auto = $x_num * $rule[1][1];//选择题
                    $score_auto += $p_num * $rule[2][1];//判断题
                }
                $d->save(['score_auto'=>$score_auto, 'id'=>$val['id']]);
            }
            
            $this->success('更新成功~');
        } else {
            $this->error('更新失败，没有任何变化~');
        }
    }
}