<?php
namespace Home\Controller;

class TestsController extends CommonController {
    /**
     * 显示当前用户所有考试列表
     */
    public function index()
    {
        $model = D('Tests');

        $arr = $model->getData();

        // var_dump($arr);exit;
        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 查看考试详情
     * @param  int $id 要查看的考试id
     */
    public function info()
    {
        $arr = D('Detail')->getInfo();
        // var_dump($arr);exit;
        if ($arr) {
            $this->assign('tests', $arr);
            $this->display();
        } else {
            $this->error('抱歉，没有该考试');
        }
    }
}