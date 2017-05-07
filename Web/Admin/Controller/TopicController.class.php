<?php
namespace Admin\Controller;

class TopicController extends CommonController {
    /**
     * 题目列表
     */
    public function index(){
        $model = D('Topic');

        $arr = $model->getData();

        $this->assign('list', $arr);
        $this->display();
    }

    /**
     * 添加题目
     */
    public function add()
    {
    	if (IS_POST) {
            // var_dump($_POST);exit;
    		//实例化
            $model = D('Topic');
            $data = $model->create();
            if ($data) {
                //拼接题目和答案中的代码
                if (!empty(I('code_t'))) {
                    $data['title'] .= '<pre>'.I('code_t').'</pre>';
                }
                if (!empty(I('code_a'))) {
                    $data['answer'] .= '<pre>'.I('code_a').'</pre>';
                }
                // var_dump($data);exit;
                $res = $model->add($data);
                if ($res) {
                    $this->success('添加成功', U('Topic/index'));
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error($model->getError());
            }
    	} else {
            $arr = D('Type')->order('concat(path, id)')->getData();

            $this->assign('TypeList', $arr);
    		$this->display();
    	}
    }

    /**
     * 编辑题目
     */
    public function edit($id)
    {
        if (IS_POST) {
            //实例化
            $model = D('Topic');
            $data = $model->create();
            if ($data) {
                //拼接题目和答案中的代码
                if (!empty(I('code_t'))) {
                    $data['title'] .= '<pre>'.I('code_t').'</pre>';
                }
                if (!empty(I('code_a'))) {
                    $data['answer'] .= '<pre>'.I('code_a').'</pre>';
                }
                
                $res = $model->save($data);
                // var_dump($data, $res);exit;
                if ($res) {
                    $this->success('修改成功', U('Topic/index'));
                } else {
                    $this->error('修改失败');
                }
            } else {
                $this->error($model->getError());
            }
        } else {
            //所有分类信息
            $arr = D('Type')->order('concat(path, id)')->getData();
            $this->assign('TypeList', $arr);

            //当前题目信息
            $info = M('Topic')->find($id);
            $info['option'] = json_decode($info['option'], true);
            //处理标题中的代码
            $pos = strpos($info['title'], '<pre>');
            if ($pos !== false) {
                $info['code_t'] = substr($info['title'], $pos+5, -6);
                $info['title'] = substr($info['title'], 0, $pos);
            }
            //处理答案中的代码
            $pos = strpos($info['answer'], '<pre>');
            if ($pos !== false) {
                $info['code_a'] = substr($info['answer'], $pos+5, -6);
                $info['answer'] = substr($info['answer'], 0, $pos);
            }
            // var_dump($info);exit;
            $this->assign('info', $info);

            $this->display();
        }
    }

    /**
     * 展示题目详情
     * @param  int $id 题目id
     */
    public function info($id)
    {
        $info = M('Topic')->find($id);
        $info['option'] = json_decode($info['option'], true);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * ajax删除题目
     * @param  int $id 要删掉的id
     */
    public function del($id)
    {
        if (IS_AJAX) {
            $res = M('Topic')->delete($id);
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