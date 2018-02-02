<?php
namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller {
    public function _initialize(){
        //判断是否登录
        if (!session('?adminInfo')) {
        	$this->redirect('Login/index');
        }
    }

    /**
     * 空操作直接跳转到退出页面
     */
    public function _empty(){
        $this->redirect('Login/logout');
    }

    /**
     * 数据分页
     * @param  integer $total 总条数
     * @param  integer $num   每页显示条数
     * @return array         包括分页条件和分页按钮
     */
    public function page($total = 0, $num = 8)
    {
        if (I('displayNum')) $num = I('displayNum');
        $page = new \Think\Page($total, $num);
        $page->setConfig('first', '首页');
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 共 %TOTAL_ROW% 条数据，第 %NOW_PAGE%/%TOTAL_PAGE% 页');

        //处理分页按钮：在后台处理，方便ajax请求
        $btn = preg_replace('/class="(.*?)"/', 'class="\\1 btn btn-success" style="margin-left:5px"', $page->show());
        $btn = preg_replace('/class="current (.*?)"/', 'class="success btn btn-default"', $btn);

        return [
            'limit'=>$page->firstRow.', '.$page->listRows,
            'show'=>$btn
        ];
    }

    public function ceshi()
    {
        $arr = M('User')->select();

        $local_user = M('User2', 'exam_', 'mysqli://root:123@localhost:3306/exam#utf8');
        // $arr = $local_user->select();
        $str = 'insert into exam_user2 values';
        $res = 0;
        foreach ($arr as $v) {
            $str .= "('{$v['id']}','{$v['class_id']}','{$v['username']}','{$v['pwd']}','{$v['role']}','{$v['status']}','{$v['sex']}','{$v['phone']}','{$v['pic']}','{$v['qq']}','{$v['age']}','{$v['email']}','{$v['has_check']}','{$v['create_time']}','{$v['create_by']}','{$v['edit_time']}','{$v['edit_by']}'),";
            // if ($local_user->add($v)) {
            //     $res++;
            // }
        }
        $str = rtrim($str, ',');
        // $res = $local_user->execute($str);
        // echo $local_user->_sql();
        $res = $local_user->execute("truncate table exam_user2");
        var_dump($res);
        var_dump($arr);
        exit;
    }
}