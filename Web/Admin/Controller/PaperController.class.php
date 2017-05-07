<?php
namespace Admin\Controller;

class PaperController extends CommonController {
    /**
     * 试卷列表
     */
    public function index(){
        $model = D('Paper');

        $arr = $model->getData();

        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 展示试卷详情
     * @param  int $id 试卷id
     */
    public function info($id)
    {
        $info = D('Paper')->getAll($id);
        $this->assign('info', $info);
        //从考试列表过来的，不显示重新生成按钮
        $this->assign('tests', I('tests'));
        $this->display();
    }

    /**
     * 添加试卷
     */
    public function add()
    {
    	if (IS_POST) {
            //实例化
            $model = D('Paper');
            $data = $model->create();

            if ($data) {
                //获取session中的所有题目
                $data['topics'] = $model->getTopics();
                // var_dump($data);exit;
                if ($data['topics'] == false) {
                    $this->error('还木有添加题目呢');
                    exit;
                } elseif (is_numeric($data['topics'])) {
                    $this->error('这套试卷已经存在了，ID号为: '.$data['topics']);
                    exit;
                }
                // var_dump($data);exit;
                $res = $model->add($data);
                if ($res) {
                    //添加成功清除session中的试题
                    session('topicList', null);
                    $this->success('添加成功', U('Paper/index'));
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error($model->getError());
            }
    	} else {
    		// var_dump($_SESSION);exit;
            //查询题目
            $model = D('Topic');

            //搜索条件
            $map = [];
            $type = I('type');
            if (strlen($type) > 0) {
                $map['type'] = $type;
            }
            $type_id = I('type_id');
            if (strlen($type_id) > 0) {
                $map['type_id'] = $type_id;
            }
            // var_dump($map);exit;
            $count = $model->where($map)->count();
            $page = new \Think\Page($count, 10);
            $arr = $model->where($map)->order('create_time desc')->limit($page->firstRow, $page->listRows)->getData();
            $this->assign('topicList', $arr);
            $this->assign('btn', $page->show());

            //所有分类数据
            $typeList = D('Type')->order('concat(path,id)')->getData();
            $this->assign('typeList', $typeList);

            //统计session里面题目类型
            $num = D('Paper')->countNum();
            $this->assign('nums', $num);
            // var_dump($num);exit;

    		$this->display();
    	}
    }

    /**
     * 将题目添加到试卷，试卷用session保存
     */
    public function addTopic()
    {
        // session('topicList', null);exit;
        $arr = session('topicList');
        $id = I('id');
        $type = I('type');
        if ($arr[$type][$id]) {
            unset($arr[$type][$id]);
            session('topicList', $arr);
            $data['status'] = 0;
            $data['info'] = '已取消';
        } else {
            // $arr[$type][$id] = M('Topic')->field('id,type_id,type,title,answer,option')->find($id);
            $arr[$type][$id] = $id;
            session('topicList', $arr);
            $data['status'] = 1;
            $data['info'] = '已添加';
        }

        //在Model类里面统计各种数量
        $data['nums'] = D('Paper')->countNum();
        $this->ajaxReturn($data);
    }

    /**
     * 预览试卷
     */
    public function prev()
    {
        //从Model类获取处理好的数据        
        if (I('id')) {
            $info = D('Paper')->getAll($id);
        } else {
            $info = D('Paper')->getAll();
        }

        $this->assign('info', $info);
    	$this->display();
    }

    /**
     * 预览的时候删除session中的题目
     * @param  int $id 要删除的题目id
     */
    public function delTopic($id)
    {
        if (IS_POST) {
            $arr = explode('_', $id);
            unset($_SESSION['topicList'][$arr[0]][$arr[1]]);
        }
    }

    /**
     * 清除session里面某一类试题
     */
    public function clearType()
    {
        if (IS_POST) {
            $type = I('type');
            unset($_SESSION['topicList'][$type]);
        }
    }

    /**
     * 编辑试卷
     */
    public function edit($id)
    {
        if (IS_POST) {
            //实例化
            $model = D('Paper');
            $data = $model->create();
            if ($data) {
                // var_dump($data);exit;
                $res = $model->save($data);
                if ($res) {
                    $this->success('修改成功', U('Paper/index'));
                } else {
                    $this->error('修改失败');
                }
            } else {
                $this->error($model->getError());
            }
        } else {
            //当前试卷信息
            $info = M('Paper')->find($id);
            $this->assign('info', $info);

            $this->display();
        }
    }

    /**
     * ajax删除试卷
     * @param  int $id 要删掉的id
     */
    public function del($id)
    {
        if (IS_AJAX) {
            $res = M('Paper')->delete($id);
            if ($res) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->redirect('index');
        }
    }
}