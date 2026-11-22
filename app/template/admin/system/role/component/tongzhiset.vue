<template>
    <div class="moduleElHight">
        <div class="tableDome_tip" style="display: flex;">
            <el-alert title="微信模板通知和企业微信通知两种方式可供选择,根据时间运营，配置不同的消息通知指定人员接受查看。" type="success" :closable="false"></el-alert>
            <div style="margin-left: 10px;">
                <el-button type="primary" size="small" icon="el-icon-setting" @click="noticeWay = true">通知方式</el-button>
            </div>
        </div>
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="180">名称</th>
                    <th>接收通知的管理员</th>
                    <th width="100">操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item,index) in list" :key="index">
                    <td>
                        <div class="TableTite">{{ item.name }}</div>
                    </td>
                    <td>
                        <el-tag v-for="(uname,idx) in item.uname_arr" :key="idx" size="medium">{{ uname }}</el-tag>
                    </td>
                    <td>
                        <el-button size="mini" @click="showSet(item)">设置</el-button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- 设置弹窗 -->
        <div class="tck_setbox">
            <el-dialog title="通知管理员" :visible.sync="set" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="500px">
                <div class="tck_setname">
                    <el-checkbox-group v-model="checked_uid">
                        <el-checkbox v-for="admin in admins" :label="admin.uid" :key="admin.uid">{{ admin.username }}</el-checkbox>
                    </el-checkbox-group>
                </div>
                <el-divider content-position="left">操作提示</el-divider>
                <div class="wxsettip">接收微信模板通知需要后台账号先
                    <el-link icon="el-icon-edit" @click="getcode">绑定微信</el-link>
                </div>
                <div class="wxsettip">接收企业微信通知需要后台账号先
                    <el-link icon="el-icon-edit" href="./myaccount.html">绑定企业微信</el-link>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="set = false">取 消</el-button>
                    <el-button type="primary" :loading="save_load" @click="submitMb">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="tck_setbox">
            <el-dialog title="扫描二维码" :visible.sync="code" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="300px">
                <div class="code_img">
                    <img :src="code_img" width="200" height="200">
                </div>
                <div class="code_p">请使用微信扫描二维码绑定</div>
            </el-dialog>
        </div>
        <div class="tck_setbox">
            <el-dialog title="通知方式" :visible.sync="noticeWay" :with-header="true" :modal-append-to-body="false" :show-close="true" width="500px">
                <el-divider content-position="left">请选择通知管理员方式</el-divider>
                <div style="margin-top: 25px;">
                    <el-radio v-model="wmType" label="1">微信通知</el-radio>
                    <el-radio v-model="wmType" label="2">企微通知</el-radio>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="noticeWay = false">取 消</el-button>
                    <el-button type="primary" :loading="save_load" @click="setNoticeWay">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    module.exports = {
        data: function () {
            return {
                set: false,
                list: [],
                admins: [],
                wmType: 1,
                curr_type: '',
                checked_uid: [],
                code: false,
                qycode: false,
                code_img: null,
                codesetval: null,
                save_load:false,
                noticeWay: false
            }
        },
        methods: {
            async getList() {
                let that = this;
                httpPost('m=system&c=role_notice&a=wxMsgSet', {}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.list = res.data.msg_type;
                        that.admins = res.data.admins;
                        that.wmType = res.data.wm_type;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            setNoticeWay() {
                let that = this;
                let params = {
                    wmType: that.wmType
                }
                that.save_load = true;
                httpPost('m=system&c=role_notice&a=wxMsgSave', params).then(function (res) {
                    that.save_load = false;
                    if (res.data.error == 0) {
                        that.$message.success({
                            message: res.data.msg,
                            onClose: function () {
                                that.getList()
                                that.noticeWay = false
                            }
                        });
                    } else {
                        that.$message.error(res.data.msg);
                    }
                });
            },
            showSet(data) {
                this.set = true
                this.curr_type = data.type
                this.checked_uid = data.uid_arr
            },
            submitMb() {
                let that = this;
                that.checked_uid
                let params = {
                    wmid: that.curr_type,
                    checkeduids: that.checked_uid
                }
                that.save_load = true;
                httpPost('m=system&c=role_notice&a=wxMsgSave', params).then(function (res) {
                    that.save_load = false;
                    if (res.data.error == 0) {
                        that.$message.success({
                            message: res.data.msg,
                            onClose: function () {
                                that.getList()
                                that.set = false
                            }
                        });
                    } else {
                        that.$message.error(res.data.msg);
                    }
                });
            },
            // 获取微信绑定二维码
            async getcode() {
                var that = this;
                httpPost('m=index&c=wxbind', {}).then(function (result) {
                    var res = result.data;
                    if (res.error == 0) {
                        that.code_img = res.data.code_url;
                        that.code = true;
                        that.codesetval = setInterval(function () {
                            that.wxbindstatus()
                        }, 2000);
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async wxbindstatus() {
                var that = this;
                httpPost('m=index&c=getwxbindstatus', {}).then(function (result) {
                    var res = result.data;
                    if (res.error == 0) {
                        message.success('绑定成功', function () {
                            clearInterval(that.codesetval);
                            that.code = true;
                        });
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
        },
    };
</script>
<style scoped>
    .moduleSeachmore {
        padding: 0px;
    }

    .moduleElHight .moduleElTable {
        padding: 0;
        margin: 0;
        height: calc(100% - (60px + 0px + 12px + 24px));
        width: 100%;
    }

    .tag-group .el-tag {
        margin-right: 8px;
    }

    .el-tag {
        margin-right: 6px;
    }
</style>