<?php

/**
 * Class crmClass_controller
 *
 * @author shiy
 * @version 7.0
 */

class crmClass_controller extends adminCommon
{

    function index_action()
    {

        $categoryM = $this->MODEL('category');
        $whereData['keyid'] = '0';
        $whereData['orderby'] = array('id,asc');
        $position = $categoryM->getCrmClassList($whereData);
        $this->render_json(0, '', $position);
    }

    //  保存分类
    function saveClass_action()
    {
        if ($_POST) {

            $_POST  =   $this->post_trim($_POST);

            $addData['ctype']   =   $_POST['ctype'];
            $addData['name']    =   explode('-', $_POST['name']);
            $addData['variable']=   explode('-', $_POST['str']);
            $addData['keyid']   =   $_POST['nid'];

            $categoryM  =   $this->MODEL('category');
            $return     =   $categoryM->addCrmClass($addData);

            if ($return['msg'] == 2){

                $this->admin_json(0, 'CRM分类添加成功！');
            } elseif ($return['msg'] == 1){

                $this->render_json(1, 'CRM相同类名已存在！');
            } elseif ($return['msg'] = 3){

                $this->render_json(1, '添加失败！');
            }
        }else{
            $this->render_json(1, '参数异常，请重试！');
        }
    }

    //  更新分类
    function upClass_action()
    {
        $return = array();
        $categoryM = $this->MODEL('category');
        if ($_POST['id']) {
            $id = $_POST['id'];
            $whereOne['id'] = $id;
            $whereTwo['keyid'] = $id;
            $whereTwo['orderby'] = array('sort,asc', 'id,asc');
            $class1 = $categoryM->getCrmClass($whereOne);
            $class2 = $categoryM->getCrmClassList($whereTwo);

            $return['class1'] = $class1;
            $return['class2'] = $class2;
        }
        $position = $categoryM->getCrmClassList(array('keyid' => '0', 'orderby' => array('id,asc')));
        $return['position'] = $position;
        $this->render_json(0, '', $return);
    }

    //  删除CRM分类
    function delClass_action()
    {

        $whereData = $data = array();

        $categoryM  =   $this->MODEL('category');

        if ($_POST['delid']) {

            $whereData['id']=   $_POST['delid'];
            $data['type']   =   'one';
        }
        if ($_POST['del']) {

            $whereData['id']=   array('in', pylode(',', $_POST['del']));
            $data['type']   =   'all';
        }
        if ($whereData) {

            $return =   $categoryM->delCrmClass($whereData, $data);

            if ($return['errcode'] == '9'){

                $this->admin_json(0, $return['msg']);
            }else{

                $this->render_json(1, $return['msg']);
            }
        }else{

            $this->render_json(1, '参数异常，请重试！');
        }
    }

    //  调整分类名称/排序
    function ajaxData_action()
    {
        if ($_POST) {

            $categoryM          =   $this->MODEL('category');

            $whereData['id']    =   $_POST['id'];
            $addData['sort']    =   $_POST['sort'];
            $addData['name']    =   $_POST['name'];

            $return =   $categoryM->addCrmClass($addData, $whereData);
            $this->admin_json($return['error'], $return['msg']);
        }
    }
}