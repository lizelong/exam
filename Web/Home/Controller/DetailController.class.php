<?php
namespace Home\Controller;

class DetailController extends \Think\Controller {
    public function _empty()
    {
        $this->redirect('Index/index');
    }
    /**
     * 展示试卷考试
     */
    public function doing()
    {
        //判断是否登录（不能继承Common）
        if (!session('?homeInfo')) $this->redirect('Login/index');

        //判断是否正在考试
        $map['class_id'] = $_SESSION['homeInfo']['class_id'];
        $map['status'] = 1;
        $tests = M('Tests')->where($map)->find();
    	if (!$tests) $this->redirect('Index/index');

        //格式化考试的计分规则
        $tests['rule'] = json_decode($tests['rule'], true);
        $tests['topics'] = json_decode($tests['info'], true);
        $tests['tests_id'] = $tests['id'];
        //判断是否是中途退出后再进来
        $detail = M('Detail')->where(['tests_id'=>$tests['id'], 'user_id'=>$_SESSION['homeInfo']['id']])->find();
        if ($detail) {
            $tests['id'] = $detail['id'];
            $tests['answer'] = json_decode($detail['answer'], true);
        } else {
            //第一次进来，插入考试详情表
            $data['tests_id'] = $tests['id'];
            $data['user_id'] = $_SESSION['homeInfo']['id'];
            $data['check_id'] = 0;
            $data['start_time'] = $data['end_time'] = time();
            $res = M('Detail')->add($data);
            if (!$res) $this->error('参加考试失败', U('Index/index'));
            $tests['id'] = $res;
        }
        // var_dump($tests);
        if ($detail && $detail['status'] != 1) {
            //控制是否显示自己写的答案
            unset($tests['answer']);
            $this->assign('tests', $tests);

            $this->display('ok');
        } else {
            $this->assign('tests', $tests);
            $this->display();
        }
    }

    /**
     * 考试过程中ajax修改学生所做答案(后期可尝试改为memcache)
     */
    public function modifyAnswer($id)
    {
        if (IS_AJAX && IS_POST) {
            $model = M('Detail');
            $info = $model->field('status')->find($id);
            if ($info['status'] != 1) $this->error('你已被强制交卷，请返回首页或等待考试结束');

            $data['id'] = $id;
            unset($_POST['id']);
            //答案
            $data['answer'] = json_encode(I('post.'));
            $res = $model->save($data);
            // var_dump($res);
        }
    }

    /**
     * 提交处理：自动判断选择题和判断题并记录分数或者正确个数
     * @param  int $id 要处理的detail表的id
     */
    public function submit($id)
    {
        $info = M('Detail')->find($id);
        if ($info['status'] != 1) $this->ajaxReturn(['status'=>2]);

        $tests = M('Tests')->find($info['tests_id']);
        $topics = json_decode($tests['info'], true);

        //判断选择题
        $x_num = 0;
        foreach ($topics[1] as $v) {
            $name = 'a_'.$v['id'];
            if (isset($_POST[$name]) && strtolower($v['answer']) == strtolower($_POST[$name])) {
                $x_num++;
            } else {
                //错了
                D('Error')->countError($tests['id'], $v['id']);
            }
        }

        //判断判断题
        $p_num = 0;
        foreach ($topics[2] as $v) {
            $name = 'a_'.$v['id'];
            if (isset($_POST[$name]) && strtolower($v['answer']) == strtolower($_POST[$name])) {
                $p_num++;
            } else {
                //错了
                D('Error')->countError($tests['id'], $v['id']);
            }
        }

        // var_dump($x_num, $p_num);
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

        //判断是否只有选择题和判断题，如果是，则状态为已完成
        if (empty($topics[3])&&empty($topics[4])&&empty($topics[5])) {
            $data['status'] = 3;
        } else {
            $data['status'] = 2;
        }
        $data['score_auto'] = $score_auto;
        $data['end_time'] = time();
        $data['id'] = $id;
        unset($_POST['id']);
        //答案
        $data['answer'] = json_encode(I('post.'));
        
        // var_dump($data);exit;
        $res = M('Detail')->save($data);
        if ($res) {
            $this->success('恭喜你，已经完成考试，请耐心等候考试结束。', U('Detail/doing'));
        } else {
            $this->error('sorry，提交失败。');
        }
    }

    /**
     * 随机抽取一位进行审核
     */
    public function check()
    {
        $tid = I('tests_id');
        $tests = M('Tests')->find($tid);
        $tests['rule'] = json_decode($tests['rule'], true);
        //判断是否需要手动审核
        if (empty($tests['rule'][3]) && empty($tests['rule'][4]) && empty($tests['rule'][5])) {
            $this->error('由系统自动审核');
        }
        //判断是否开启了审核权限
        if ($tests['is_check'] !=2) {
            $this->error('老师没有开启审核权限');
        }
        $tests['topics'] = json_decode($tests['info'], true);

        //随机查出一个人进行审核
        $map['tests_id'] = $tid;
        $map['user_id'] = ['neq', $_SESSION['homeInfo']['id']];
        $map['status'] = 2;
        $res = M('Detail')->where($map)->select();
        if ($res) {
            shuffle($res);
            //格式化答案
            $tests['answer'] = json_decode($res[0]['answer'], true);
            $tests['detail_id'] = $res[0]['id'];
            //删除选择题和判断题
            unset($tests['topics'][1]);
            unset($tests['topics'][2]);

            $this->assign('tests', $tests);
            $this->display();
        } else {
            $this->error('没有可以审核的童鞋！');
        }
    }

    /**
     * 处理学员审核
     * @param  int $tests_id 当前次考试的id号
     */
    public function docheck($tests_id)
    {
        if (IS_POST && IS_AJAX) {
            $info = M('Tests')->field('rule, info, type')->find($tests_id);
            $rule = json_decode($info['rule'], true);
            $topics = json_decode($info['info'], true);

            //删除选择题和判断题
            unset($topics[1]);
            unset($topics[2]);

            $num = 0;
            if ($info['type'] == 2) {
                //计分题
                foreach ($topics as $k=>$v) {
                    //遍历填空题
                    foreach ($v as $val) {
                        if (I('fen_'.$val['id']) > $rule[$k][1]) {
                            //记录日志：Runtime\Logs\Admin
                            \Think\Log::write('学员《'.$_SESSION['homeInfo']['username'].'》审核异常');
                            $this->error('同学，别乱来。');
                        } else {
                            $num += I('fen_'.$val['id']);
                        }
                    }
                }
            } else {
                //计数题
                foreach ($_POST as $v) {
                    if ($v === 'yes') {
                        $num++;
                    }
                }
            }

            //准备修改数据
            $data['id'] = I('detail_id');
            $data['status'] = 3;
            $data['score_check'] = $num;
            $data['check_id'] = $_SESSION['homeInfo']['id'];
            unset($_POST['tests_id']);
            unset($_POST['detail_id']);
            $data['check_info'] = json_encode(I('post.'));
            $data['check_time'] = time();
            $res = M('Detail')->save($data);
            if ($res) {
                $this->success('审阅成功', U('Detail/doing'));
            } else {
                $this->error('审阅失败');
            }
        }
    }

    /**
     * 获取当前时间戳
     */
    public function getTime()
    {
        echo time();
        exit;
    }
}