<?php

/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class skill_class_controller extends adminCommon
{
    // 分类列表
    function index_action()
    {
        $categoryM = $this->MODEL('skillcategory');
        $whereData['keyid'] = '0';
        $position = $categoryM->getSkillClassList(array('keyid' => '0'));
        $this->render_json(0, 'ok', $position);
    }

    //添加
    function save_action()
    {
        $_POST = $this->post_trim($_POST);
        $addData['ctype'] = $_POST['ctype'];
        $addData['name'] = explode('-', $_POST['name']);
        $addData['variable'] = explode('-', $_POST['str']);
        $addData['keyid'] = $_POST['nid'];

        $categoryM = $this->MODEL('skillcategory');
        $return = $categoryM->addSkillClass($addData);

        if ($return['error'] === 0) {
            $this->admin_json($return['error'], $return['msg']);
        } else {
            $this->render_json($return['error'], $return['msg']);
        }
    }

    //分类管理
    function up_action()
    {
        $categoryM = $this->MODEL('skillcategory');
        //查询子类别
        if ($_POST['id']) {
            $id = $_POST['id'];
            $whereOne['id'] = $id;
            $whereTwo['keyid'] = $id;
            $whereTwo['orderby'] = 'sort,asc';
            $class1 = $categoryM->getSkillClass($whereOne);
            $class2 = $categoryM->getSkillClassList($whereTwo);
            foreach ($class2 as &$value){
                $value['sort'] = !$value['sort']?0:$value['sort'];
            }
        }
        $position = $categoryM->getSkillClassList(array('keyid' => '0'));
        $this->render_json(0, 'ok', compact('position', 'class1', 'class2'));
    }

    //删除
    function del_action()
    {
        $whereData = array();
        $data = array();
        $categoryM = $this->MODEL('skillcategory');
        if ($_POST['delid']) {//单个删除
            $whereData['id'] = $_POST['delid'];
            $data['type'] = 'one';
        }
        if ($_POST['del']) {//批量删除
            $whereData['id'] = array('in', pylode(',', $_POST['del']));
            $data['type'] = 'all';
        }
        $return = $categoryM->delSkillClass($whereData, $data);

        if ($return['error'] === 0) {
            $this->admin_json($return['error'], $return['msg']);
        } else {
            $this->render_json($return['error'], $return['msg']);
        }
    }

    function ajax_action()
    {
        if (!empty($_POST)) {
            $categoryM = $this->MODEL('skillcategory');
            $whereData['id'] = $_POST['id'];
            $addData['sort'] = $_POST['sort'];
            $addData['name'] = $_POST['name'];

            $return = $categoryM->addSkillClass($addData, $whereData);

            if ($return['error'] === 0) {
                $this->admin_json($return['error'], $return['msg']);
            } else {
                $this->render_json($return['error'], $return['msg']);
            }
        }
    }

}

?>
