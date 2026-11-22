<template>
    <div class="drawerModlue" v-loading="addloading">
        <div class="drawerModInfo">
            <div class="hydialog_item">
                <span class="item_span">客户名称：</span>
                <div class="hydialModFrom">
                    <el-select v-model="com_uid" :disabled="comdisabled" @change="comSelect" filterable remote reserve-keyword placeholder="输入企业名称" :remote-method="company_search" :loading="company_searchloading">
                        <el-option v-for="item in company_search_list" :key="item.uid" :label="item.name" :value="item.uid">
                            <span style="float: left; color: #333; font-size: 14px;font-weight:bold;">{{ item.name }}</span>
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="hydialog_item">
                <span class="item_span">任务类型：</span>
                <div class="hydialModFrom">
                    <el-select v-model="taskType" placeholder="请选择">
                        <el-option v-for="item in task_type" :key="item.val" :label="item.name" :value="item.val"></el-option>
                    </el-select>
                </div>
            </div>
            <div v-show="taskType != '22'" class="hydialog_item">
                <span class="item_span">执行人员：</span>
                <div class="hydialModFrom">
                    <el-select v-model="taskHuid" placeholder="请选择">
                        <el-option v-for="item in adminUserList" :key="item.uid" :label="item.name" :value="item.uid"></el-option>
                    </el-select>
                </div>
            </div>
            <div v-show="taskType == '22'" class="hydialog_item">
                <span class="item_span">任务时间：</span>
                <div class="hydialModFrom">
                    <el-date-picker v-model="taskTime" type="datetime" value-format="yyyy-MM-dd HH:mm:ss"></el-date-picker>
                </div>
            </div>
            <div class="hydialog_item">
                <span class="item_span">任务描述：</span>
                <div class="hydialModFrom">
                    <el-input type="textarea" v-model="taskRemark" :rows="3"></el-input>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="saveinfo" :loading="saveloading">提交</el-button>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            uid_v: {
                type: String,
                default: ''
            },
            name_v: {
                type: String,
                default: ''
            },
            id_v: {
                type: String,
                default: ''
            }
        },
        data: function () {
            return {
                company_searchloading: false,
                company_search_list: [],
                task_type: [],
                adminUserList: [],

                com_uid: '',
                taskHuid: '',
                taskType: '',
                taskRemark: '',
                taskTime: '',
                comname: '',

                comdisabled: false,
                addloading: false,
                saveloading: false,
            }
        },
        mounted() {
            this.com_uid = this.uid_v;
            if (this.uid_v && this.name_v == '') {
                message.warning("请先完善客户基本信息内容！", () => {
                    this.$emit("close-update");
                });
                return false;
            }
            if (this.uid_v && this.name_v) {
                this.company_search_list = [{
                    uid: this.uid_v,
                    name: this.name_v
                }];
                this.comdisabled = true;
            }
            this.taskType = this.id_v;
            this.taskTime = formatDatetime(new Date());
            this.getData();
        },
        methods: {
            async getData() {
                let that = this;
                this.addloading = true;
                httpPost('m=crm&c=crm_index&a=index', {}).then((result) => {
                    this.addloading = false;
                    var res = result.data;
                    if (res.error == 0) {

                        that.task_type = res.data.task_type;
                        that.adminUserList = res.data.adminUserList;
                        that.taskHuid = res.data.auid.toString();
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async company_search(query) {

                let that = this;
                var company_search_list = [];
                that.company_searchloading = true;
                if (query == '') {
                    return false;
                }
                var params = {
                    keyword: query
                };
                httpPost('m=crm&c=crm_index&a=searchcom', params).then((result) => {
                    that.company_searchloading = false;
                    var res = result.data;
                    if (res.error == 0) {
                        company_search_list = res.data;
                    }
                    that.company_search_list = company_search_list;
                    if (that.company_search_list.length == 0) {
                        that.com_id = '';
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            comSelect: function (e) {
            },
            async saveinfo() {
                if (this.taskType == '') {
                    message.warning('请选择任务类型！');
                    return false;
                }
                if (this.com_uid == '') {
                    message.warning('客户数据错误，请重新填写客户名称！');
                    return false;
                }
                if (this.taskHuid == '') {
                    message.warning('请选择执行人员！');
                    return false;
                }
                var stime = '';
                if (this.taskType == '22' && this.taskTime != '') {
                    var now = new Date().getTime();
                    var stime = new Date(this.taskTime).getTime();
                    if (stime < now) {
                        message.warning('跟进任务时间需在当前时间之后，请重新填写');
                        return false;
                    }
                    stime = stime / 1000;
                }
                if (this.taskRemark == '') {
                    message.warning('请简要地填写任务描述内容！');
                    return false;
                }
                var param = {
                    comid: this.com_uid,
                    type: this.taskType,
                    content: this.taskRemark,
                    stime: stime,
                    taskHuid: this.taskHuid,
                };
                this.saveloading = true;
                httpPost('m=crm&c=task&a=add', param).then((result) => {
                    this.saveloading = false;
                    var res = result.data;
                    if (res.error == 9) {
                        message.success(res.msg, () => {
                            this.$emit("close-update");
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            }
        }
    };
</script>
<style scoped>
    .drawerModInfo::-webkit-scrollbar{display:none}
    .drawerModlue{overflow:hidden;position:relative;width:100%;height:100%;padding:0}
    .drawerModInfo{overflow-y:auto;height:calc(100% - 80px);width:100%;padding:0}
    .drawerModInfo .hydialog_item{overflow:hidden;position:relative;padding:6px 0;width:100%;display:flex;align-items:center;justify-content:space-between}
    .drawerModInfo .hydialModFrom{overflow:hidden;position:relative;width:calc(100% - 75px)}
    .drawerModInfo .hydialModFrom .el-select{width:100%}
</style>