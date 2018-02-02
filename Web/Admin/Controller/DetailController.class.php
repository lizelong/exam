<?php
namespace Admin\Controller;

class DetailController extends CommonController {
    public function index(){
        $model = D('Detail');
        $arr = $model->getData();

        //判断非法访问
        if ($arr === false) $this->redirect('Login/logout');
        
        //获取未开始考试的童鞋
        $not = array_pop($arr);
        // var_dump($arr, $_GET);exit;
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
    	$info = M('Detail')->find($id);
        $tests = M('Tests')->find($info['tests_id']);
        if (D('Detail')->submit($info, $tests)) {
            $this->success('已强制提交');
        } else {
            $this->error('强制提交失败');
        }
    }

    /**
     * 显示审核页面
     */
    public function check($id)
    {
        $tid = I('tests_id');
        $tests = M('Tests')->find($tid);
        $tests['rule'] = json_decode($tests['rule'], true);
        //判断是否需要手动审核
        if (empty($tests['rule'][3]) && empty($tests['rule'][4]) && empty($tests['rule'][5])) {
            $this->error('由系统自动审核');
        }
        $tests['topics'] = json_decode($tests['info'], true);

        //随机查出一个人进行审核
        $map['status'] = 2;
        $res = M('Detail')->where($map)->find($id);
        if ($res) {
            //格式化答案
            $tests['answer'] = json_decode($res['answer'], true);
            $tests['detail_id'] = $id;
            //删除选择题和判断题
            unset($tests['topics'][1]);
            unset($tests['topics'][2]);

            $this->assign('tests', $tests);
            $this->display();
        } else {
            $this->error('该童鞋已经审核过了！');
        }
    }

    /**
     * 查看考试详情
     * @param  int $id 要查看的考试详情id
     */
    public function info($id)
    {
        $arr = D('Detail')->getInfo($id);
        if ($arr) {
            $this->assign('tests', $arr);
            $this->display();
        } else {
            $this->error('抱歉，没有该考试');
        }
    }

    /**
     * 处理审核
     * @param  int $tests_id 当前次考试的id号
     */
    public function docheck($tests_id)
    {
        // var_dump($_POST);exit;
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
                            \Think\Log::write('老师《'.$_SESSION['adminInfo']['username'].'》审核异常');
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
            // echo $num;exit;

            //准备修改数据
            $data['id'] = I('detail_id');
            $data['status'] = 3;
            $data['score_check'] = $num;
            $data['check_id'] = $_SESSION['adminInfo']['id'];
            unset($_POST['tests_id']);
            unset($_POST['detail_id']);
            $data['check_info'] = json_encode($_POST);
            $data['check_time'] = time();
            $res = M('Detail')->save($data);
            // var_dump($_POST);
            // var_dump($data, $res);exit;
            if ($res) {
                $this->success('审阅成功');
            } else {
                $this->error('审阅失败');
            }
        }
    }

    /**
     * 导出姓名和百分制的成绩
     * @param  int $tests_id 考试的ID
     */
    public function export($tests_id)
    {
        //查询考试类型
        $tests = M('Tests')->field('rule, type, des')->find($tests_id);
        $rule = json_decode($tests['rule'], true);
        $total = 0; //总数
        if ($tests['type'] == 2) {
            //计分
            foreach ($rule as $val) {
                $total += $val[0] * $val[1];
            }
        } else {
            $this->error('sorry,无法导出非计分考试');
        }

        //查询要导出的数据
        $arr = M('Detail')->alias('d')
            ->field('u.username,d.score_auto,d.score_check')
            ->join('left join __USER__ u on d.user_id=u.id')
            ->where(['tests_id'=>$tests_id])
            ->select();
        foreach ($arr as &$v) {
            $v['total_h'] = round(100/$total*($v['score_auto'] + $v['score_check']));

            unset($v['score_auto']);
            unset($v['score_check']);
        }
        //调用自定义的函数进行降序排列
        $arr = multisort($arr, 'total_h', SORT_DESC);

        //调用自定义函数导出Excel文件
        exportExcel($arr, ['姓名', '分数'], $tests['des']);
    }
}