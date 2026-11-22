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
class crmphoneauth_model extends model
{

    /**
     * @param array $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getList($whereData = array(), $data = array())
    {

        $select =   isset($data['field']) ? $data['field'] : '*';

        if (!empty($data['sql'])) {

            $List   =   $this->DB_query_all($data['sql'], 'all');
        } else {

            $List   =   $this->select_all('crm_phoneauth', $whereData, $select);
        }

        if (!empty($List)) {

            $ruid = $cuid = $ltuid = $pxuid = $phone_arr = array();

            foreach ($List as $ke => $val) {

                $phone_arr[] = $val['phone'];
            }

            $member =   $this->select_all('member', array('moblie' => array('in', pylode(',', array_unique($phone_arr)))), '`uid`,`usertype`,`moblie`');
            foreach ($member as $mk => $mv) {
                foreach ($List as $listk => $listv) {
                    if ($mv['moblie'] == $listv['phone']) {

                        $List[$listk]['uid']        =   $mv['uid'];
                        $List[$listk]['usertype']   =   $mv['usertype'];
                        $this->update_once('crm_phoneauth', array('uid' => $mv['uid'], 'usertype' => $mv['usertype']), array('phone' => $mv['moblie']));
                    }
                }
            }

            foreach ($List as $key => $value) {
                if ($value['usertype'] == 1 && !in_array($value['uid'], $ruid)) {

                    $ruid[] = $value['uid'];
                } else if ($value['usertype'] == 2 && !in_array($value['uid'], $cuid)) {

                    $cuid[] = $value['uid'];
                } else if ($value['usertype'] == 3 && !in_array($value['uid'], $ltuid)) {

                    $ltuid[] = $value['uid'];
                } else if ($value['usertype'] == 4 && !in_array($value['uid'], $pxuid)) {

                    $pxuid[] = $value['uid'];
                }
            }

            $expect =   $this->select_all('resume_expect', array('uid' => array('in', pylode(',', array_unique($ruid))), 'groupby' => 'uid'), 'uid,count(*) as num');
            $resume =   $this->select_all('resume', array('uid' => array('in', pylode(',', array_unique($ruid)))), '`uid`,`name`');
            $company=   $this->select_all('company', array('uid' => array('in', pylode(',', array_unique($cuid)))), '`uid`,`name`');
            $itlist =   $this->select_all('lt_info', array('uid' => array('in', pylode(',', array_unique($ltuid)))), '`uid`,`realname`');
            $pxlist =   $this->select_all('px_train', array('uid' => array('in', pylode(',', array_unique($pxuid)))), '`uid`,`name`');

            include(CONFIG_PATH.'db.data.php');

            foreach ($List as $k => $v) {
                if ($v['source']) {

                    $List[$k]['source_n']   =   $arr_data['source'][$v['source']];
                }

                if ($v['atime']) {

                    $List[$k]['atime_n']    =   date('Y-m-d H:i', $v['atime']);
                }

                $List[$k]['name_n']     =   '';
                $List[$k]['usertype_n'] =   '未注册';

                if ($v['usertype'] == 1) {

                    $List[$k]['resume']     =   0;
                    foreach ($resume as $rk => $rv) {
                        if ($v['uid'] == $rv['uid']) {

                            $List[$k]['name_n']     =   $rv['name'];
                            $List[$k]['usertype_n'] =   '个人';
                        }
                    }
                    foreach ($expect as $ek => $ev) {
                        if ($v['uid'] == $ev['uid']) {

                            $List[$k]['resume']     =   $ev['num'];
                        }
                    }
                } else if ($v['usertype'] == 2) {
                    foreach ($company as $ck => $cv) {
                        if ($v['uid'] == $cv['uid']) {

                            $List[$k]['name_n']     =   $cv['name'];
                            $List[$k]['usertype_n'] =   '企业';
                        }
                    }
                } else if ($v['usertype'] == 3) {
                    foreach ($itlist as $lk => $lv) {
                        if ($v['uid'] == $lv['uid']) {

                            $List[$k]['name_n']     =   $lv['realname'];
                            $List[$k]['usertype_n'] =   '猎头';
                        }
                    }
                } else if ($v['usertype'] == 4) {
                    foreach ($pxlist as $pk => $pv) {
                        if ($v['uid'] == $pv['uid']) {

                            $List[$k]['name_n']     =   $pv['name'];
                            $List[$k]['usertype_n'] =   '培训';
                        }
                    }
                } else if ($v['uid']) {

                    $List[$k]['usertype_n']         =   '未选择身份';
                }
            }
        }
        return $List;
    }

    function getNum($whereData)
    {

        return $this->select_num('crm_phoneauth', $whereData);
    }

    function addPhoneAuth($data)
    {
        if (!empty($data)) {

            return $this->insert_into('crm_phoneauth', $data);
        }
    }

    function getInfo($whereData = array(), $data = array())
    {

        return $this->select_once('crm_phoneauth', $whereData);
    }

    function upPhoneAuth($whereData = array(), $data = array())
    {
        if (!empty($whereData)) {

            $nid = $this->update_once('crm_phoneauth', $data, $whereData);
        }

        $return['errcode']  =   $nid ? 9 : 8;
        $return['msg']      =   $nid ? '备注成功！' : '备注失败！';
        return $return;
    }

    /**
     * @param $id
     * @param string[] $data
     * @return array
     */
    public function delPhoneAuth($id, $data = array('utype' => ''))
    {
        if (!empty($id)) {

            $return = array();

            if (is_array($id)) {

                $ids = $id;
                $return['layertype'] = 1;
            } else {

                $ids = @explode(',', $id);
            }

            $id         =   pylode(',', $ids);
            $delWhere   =   array('id' => array('in', $id));

            $return['id']       =   $this->delete_all('crm_phoneauth', $delWhere, '');

            $return['msg']      =   '记录(ID:' . $id . ')';
            $return['errcode']  =   $return['id'] ? 9 : 8;
            $return['msg']      =   $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
        } else {

            $return['msg']      =   '请选择您要删除的记录！';
            $return['errcode']  =   8;
        }

        return $return;
    }

    function authNum()
    {

        $arr = array('authAllNum'=>0,'authNum1'=>0,'authNum2'=>0,'authNum3'=>0);

        //总数
        $authAllNum = $this->select_num('crm_phoneauth');
        if ($authAllNum > 0) {
            $arr['authAllNum'] = $authAllNum;
        }

        //已注册
        $authNum1 = $this->select_num('crm_phoneauth', array('uid' => array('>', 0)));
        if ($authNum1 > 0) {
            $arr['authNum1'] = $authNum1;
        }

        //未注册
        $authNum2 = $this->select_num('crm_phoneauth', array('uid' => 0));
        if ($authNum2 > 0) {
            $arr['authNum2'] = $authNum2;
        }

        //已发布简历
        $sql = "select count(*) as num from `" . $this->def . "crm_phoneauth` a where (EXISTS (SELECT `id`,`uid` FROM `" . $this->def . "resume_expect` b WHERE b.uid=a.uid)) AND a.`uid`>0 AND a.`usertype`=1";
        $authNum3 = $this->DB_query_all($sql, 'all');
        if (intval($authNum3[0]['num']) > 0) {
            $arr['authNum3'] = intval($authNum3[0]['num']);
        }
        return $arr;
    }
}
?>