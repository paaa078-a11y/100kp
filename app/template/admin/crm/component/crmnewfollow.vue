<template>
    <div v-loading="addloading" class="crmxieGensunhcy">
        <div class="crmxieGenCont">
            <div style="overflow-y: auto;position: relative; height: calc(100% - 60px);">
                <div class="hydialog_item">
                    <span class="item_span">客户名称：</span>
                    <el-select v-model="com_uid" :disabled='comdisabled' @change="comSelect" filterable remote reserve-keyword placeholder="输入企业名称" :remote-method="company_search" :loading="company_searchloading">
                        <el-option v-for="item in company_search_list" :key="item.uid" :label="item.name" :value="item.uid">
                            <span style="float: left; color: #333; font-size: 14px;font-weight:bold;">{{ item.name }}</span>
                        </el-option>
                    </el-select>
                </div>
                <div class="hydialog_item">
                    <span class="item_span">客户等级：</span>
                    <el-select v-model="public_crm_type" placeholder="请选择">
                        <el-option v-for="item in client_type" :key="item.val" :label="item.name" :value="item.val"></el-option>
                    </el-select>
                </div>
                <div class="hydialog_item">
                    <span class="item_span">客户标签：</span>
                    <el-select v-model="public_crm_status" placeholder="请选择">
                        <el-option v-for="item in client_status" :key="item.val" :label="item.name" :value="item.val"></el-option>
                    </el-select>
                </div>
                <div class="hydialog_item">
                    <span class="item_span">跟进时间：</span>
                    <el-date-picker v-model="public_ftime" type="datetime" @change="ftime_change" value-format="yyyy-MM-dd HH:mm:ss" :picker-options="pickerOptions"></el-date-picker>
                </div>
                <div class="hydialog_item">
                    <span class="item_span">跟进方式：</span>
                    <el-select v-model="public_follow_way" placeholder="请选择">
                        <el-option v-for="item in follow_way" :key="item.val" :label="item.name" :value="item.val"></el-option>
                    </el-select>
                </div>
                <div class="hydialog_item">
                    <span class="item_span">联系人员：</span>
                    <div v-show="!newlinkshow">
                        <el-select v-model="public_link" placeholder="请选择" style="width: 380px;">
                            <el-option v-for="item in crmLink" :key="item.id" :label="`${item.link_man}-${item.link_mobile}`" :value="`${item.link_man}-${item.link_mobile}`"></el-option>
                        </el-select>
                        <el-button v-if="newLinkA" type="primary" size="small" @click="newlinkshow = true">新增</el-button>
                    </div>
                    <div v-show="newlinkshow" class="dilianxgrenyan">
                        <el-input v-model="public_link_man" placeholder="联系人"></el-input>
                        <el-input v-model="public_link_tel" placeholder="联系电话" style="margin: 0 8px;"></el-input>
                        <el-button type="primary" @click="addNewLink" size="small">保存</el-button>
                        <el-button type="primary" @click="newlinkshow = false" size="small">取消</el-button>
                    </div>
                </div>
                <div class="hydialog_item hydialogWidtn">
                    <span class="item_span">跟进内容：</span>
                    <div id="editor—wrapper-follow" class="editoWrapper">
                        <div id="toolbar-container-follow">
                            <!-- 工具栏 -->
                        </div>
                        <div id="editor-container-follow" style="height: 200px;">
                            <!-- 编辑器 -->
                        </div>
                    </div>
                </div>
                <div class="hydialog_item">
                    <span class="item_span">下次跟进：</span>
                    <el-checkbox v-model="public_next_check"></el-checkbox>
                    <el-date-picker v-if="public_next_check" style="margin-left: 6px;" v-model="public_next_time" @change="next_time_change" value-format="yyyy-MM-dd HH:mm:ss" type="datetime" :picker-options="pickerOptions2" placeholder="请选择跟进时间"></el-date-picker>
                </div>
            </div>
            <div class="hydialog_item">
                <span class="item_span"></span>
                <el-button type="primary" style="padding: 13px 60px;" @click="saveinfo" :loading="saveloading">{{fid ? '更新' : '添加'}}</el-button>
            </div>
        </div>
        <!-- <div class="dialog-footer">
			<el-button type="primary" @click="saveinfo" :loading="saveloading">添加</el-button>
		</div> -->
    </div>
</template>
<script setup>
let editor_follow = null,editorInterval = null;
const { createEditor, createToolbar } = window.wangEditor;

module.exports = {
    props: {
        fid: {
            type: [String, Number],
            default: '',
        },
        uid_v: {
            type: String,
            default: ''
        },
        crm_status_v: {
            type: String,
            default: ''
        },
        crm_type_v: {
            type: String,
            default: ''
        },
        name_v: {
            type: String,
            default: ''
        },
        taskid_v: {
            type: String,
            default: ''
        },
        calllog_id: {
            type: String,
            default: ''
        }
    },
    watch: {
        fid: {
            handler(n, v) {
                this.getInfo();
            },
            immediate: true
        },
        uid_v: {
            handler(n, v) {
                this.com_uid = n;
                this.comSelect('');
            },
            immediate: true
        },
        crm_status_v: {
            handler(n, v) {
                this.public_crm_status = Number(n) > 0 ? n : '';
            },
            immediate: true
        },
        crm_type_v: {
            handler(n, v) {
                this.public_crm_type = Number(n) > 0 ? n : '';
            },
            immediate: true
        },
        name_v: {
            handler(n, v) {
                this.name = n;

            },
            immediate: true
        },
        taskid_v: {
            handler(n, v) {
                this.taskid = n;
            },
            immediate: true
        },
        calllog_id: {
            handler(n, v) {
                this.call_log_id = n;
            },
            immediate: true
        },
    },
	beforeDestroy() {
		editor_follow = null; 
		editorInterval = null;
	},
    data: function() {
        return {
            name: '',
            taskid: '',
            com_uid: '',
            crm_uid: '',
            public_crm_status: '',
            public_crm_type: '',
            public_ftime: '',
            public_follow_way: '',
            public_next_check: false,
            public_next_time: '',
            public_link_man: '',
            public_link_tel: '',
            public_link: '',
            call_log_id: '',

            addloading: false,
            client_type: [],
            client_status: [],
            follow_way: [],
            crmLink: [],
            newLinkA: false,
            newlinkshow: false,
            company_searchloading: false,
            company_search_list: [],
            comdisabled: false,
            saveloading: false,

            pickerOptions: {
                disabledDate(time) {
                    const date = new Date();
                    var day7 = date.getTime() - 3600 * 1000 * 24 * 7;
                    if (time.getTime() >= date.getTime() || time.getTime() <= day7) {
                        return true;
                    };
                }
            },
            pickerOptions2: {
                disabledDate(time) {
                    var today = formatDate(new Date()) + ' 00:00:00';
                    var date = new Date(today).getTime();

                    return time.getTime() < date;
                }
            },
        }
    },

    mounted() {
		this.initEditor();
        if (this.com_uid != '' && this.name != '') {
            this.company_search_list = [{
                uid: this.com_uid,
                name: this.name
            }];
            this.comdisabled = true;
        }
        this.public_ftime = formatDatetime(new Date());
        this.getData();
    },
	
    methods: {
		initEditor: function () {
			clearInterval(editorInterval);
			editorInterval = setInterval(()=>{
                if (editor_follow !== null){
					clearInterval(editorInterval);
				}else{
					let editorConfig = {
					    MENU_CONF: {
					        uploadImage: {
					            server: baseUrl + 'm=index&c=uploadfile',
					            fieldName: 'file'
					        }
					    }
					};
					editor_follow = createEditor({
					    selector: '#editor-container-follow',
					    html: '',
					    config: editorConfig,
					    mode: 'simple'
					});
					
					let toolbar = createToolbar({
					    editor: editor_follow,
					    selector: '#toolbar-container-follow',
					    config: {
					        excludeKeys: ['blockquote', 'header1', 'header2', 'header3', '|', 'through', 'color', 'bgColor', 'insertLink', 'todo', '|', 'insertVideo', 'insertTable', 'codeBlock', '|', 'undo', 'redo', '|', ]
					    },
					    mode: 'simple'
					});
				}
			},300);
			
		},
        ftime_change: function(e) {
            var now = new Date().getTime();
            var date = new Date(this.public_ftime).getTime();
            if (date > now) {
                this.public_ftime = formatDatetime(new Date());
            }
        },
        next_time_change: function(e) {
            var now = new Date().getTime();
            var date = new Date(this.public_next_time).getTime();
            if (date < now) {
                this.public_next_time = formatDatetime(new Date());
            }
        },
        defaultVal: function() {
            this.com_uid = '';
            this.crm_uid = '';
            this.public_crm_status = '';
            this.public_crm_type = '';
        },
        async getData() {
            let that = this;

            this.addloading = true;

            httpPost('m=crm&c=crm_index&a=index', {}).then((result) => {

                this.addloading = false;

                var res = result.data;
                if (res.error == 0) {

                    that.client_status = res.data.client_status;
                    that.client_type = res.data.client_type;
                    that.follow_way = res.data.follow_way;
                    if (that.follow_way.length > 0 && !this.fid) {
                        that.public_follow_way = that.follow_way[0].val;
                    }
                }
            }).catch(function(e) {
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
                    that.defaultVal();
                }
            }).catch(function(e) {
                console.log(e)
            })
        },
        comSelect: function(e) {
            let that = this;

            var company_search_list = deepClone(this.company_search_list);
            if (e != '') {
                for (let i in company_search_list) {
                    if (company_search_list[i].uid == e) {
                        this.crm_uid = company_search_list[i].crm_uid;
                        this.public_crm_status = company_search_list[i].crm_status > 0 ? company_search_list[i].crm_status : '';
                        this.public_crm_type = company_search_list[i].crm_type > 0 ? company_search_list[i].crm_type : '';
                        break;
                    }
                }
            }


            if (this.com_uid) {

                var params = {
                    uid: this.com_uid
                };

                httpPost('m=crm&c=crm_index&a=getCrmLink', params).then((result) => {

                    var res = result.data;
                    var crmLink = [];
                    if (res.error == 0) {
                        crmLink = res.data;
                    }

                    that.crmLink = crmLink;

                    if (crmLink.length > 0 && !that.fid) {
                        that.public_link = `${crmLink[0].link_man}-${crmLink[0].link_mobile}`;
                        // that.public_link_man = crmLink[0].link_man;
                        // that.public_link_tel = crmLink[0].link_mobile;
                    }

                    that.newLinkA = true;
                }).catch(function(e) {
                    console.log(e)
                })
            }
        },
        addNewLink: function() {

            let that = this;
            var crmLink = deepClone(this.crmLink);
            if (this.com_uid == '') {
                message.warning('客户信息错误');
                return;
            }

            if (this.public_link_man == '') {
                message.warning('请填写联系人');
                return;
            }
            if (this.public_link_tel == '') {
                message.warning('请填写联系人手机号码');
                return;
            } else if (!isjsMobile(this.public_link_tel)) {
                message.warning('请填写正确的手机号码');
                return;
            }

            var params = {
                uid: this.com_uid,
                link_man: this.public_link_man,
                link_mobile: this.public_link_tel,
            };

            httpPost('m=crm&c=crm_my_customer&a=addCrmLink', params).then((result) => {

                var res = result.data;

                if (res.error == 0) {

                    crmLink.push({
                        id: new Date().getTime(),
                        link_man: that.public_link_man,
                        link_mobile: that.public_link_tel
                    });
                    that.public_link = `${that.public_link_man}-${that.public_link_tel}`;
                    that.crmLink = crmLink;
                    that.newlinkshow = false;

                } else if (res.error == 8) {
                    message.error(res.msg);
                    return;
                } else if (res.error == 7) {
                    message.confirm(res.msg, function() {
                        that.saveNewLink(params);
                    }, '继续添加', '提示', 'warning', true, '更换号码', function() {
                        that.public_link_tel = '';
                    });

                }

            }).catch(function(e) {
                console.log(e)
            })
        },
        async saveNewLink(params) {
            let that = this;
            var crmLink = deepClone(this.crmLink);
            params.add = 1;

            httpPost('m=crm&c=crm_my_customer&a=addCrmLink', params).then((result) => {

                var res = result.data;

                if (res.error == 9) {

                    crmLink.push({
                        id: new Date().getTime(),
                        link_man: params.link_man,
                        link_mobile: params.link_mobile
                    });
                    that.public_link = `${params.link_man}-${params.link_mobile}`;
                    that.crmLink = crmLink;
                    that.newlinkshow = false;

                } else if (res.error == 8) {
                    message.error(res.msg);
                    return;
                }
            }).catch(function(e) {
                console.log(e)
            })
        },
        async saveinfo() {
            var param = {};

            param.uid = this.com_uid;
            param.type = this.public_follow_way;
            param.crm_status = this.public_crm_status;
            param.crm_type = this.public_crm_type;

            var ftime = this.public_ftime;
            if (ftime == '' || ftime == undefined || !ftime) {
                message.warning('请填写跟进时间！');
                return false;
            } else {
                param.ftime = ftime;
            }

            var public_link = this.public_link;
            var linkArr = public_link.split('-');
            var newLinkMan = linkArr[0];
            var newLinkTel = linkArr[1];

            if (newLinkMan == '' || newLinkTel == '') {
                message.warning('请选择客户联系人员！');
                return false;
            } else {

                param.link_man = newLinkMan;
                param.link_tel = newLinkTel;
            }

            param.remark = editor_follow.getHtml();

            var follow = this.public_next_check ? 1 : 0;
            param.follow = follow;
            if (follow == 1) {
                param.next_time = this.public_next_time;
            }

            if (this.call_log_id != '') {
                param.call_log_id = this.call_log_id;
            }
            param.id = this.fid;
            this.saveloading = true;
            httpPost('m=crm&c=crm_my_customer&a=addFollow', param).then((result) => {
                this.saveloading = false;
                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, () => {

                        this.$emit("close-update");
                    });
                } else {
                    message.error(res.msg);
                }
            }).catch(function(e) {
                console.log(e)
            })
        },
        getInfo() { // 编辑信息获取方法
            if (!this.fid) {
                return false;
            }
            let _this = this;
            httpPost('m=crm&c=crm_my_customer&a=getFollowInfo', {
                id: _this.fid,
                uid: _this.com_uid
            }).then(function(res) {
                var data = res.data;
                if (data.error == 0) {
                    let info = data.data;
                    var edsinv = setInterval(()=>{
                        if (editor_follow !== null){
                            editor_follow.setHtml(info.content);
                            clearInterval(edsinv);
                        }
                    },300);
                    _this.public_follow_way = info.type;
                    _this.public_crm_status = info.crm_status > 0 ? info.crm_status : '';
                    _this.public_crm_type = info.crm_type > 0 ? info.crm_type : '';
                    _this.public_ftime = formatDatetime(new Date(info.ftime * 1000));
                    _this.public_link = `${info.link_man}-${info.link_tel}`;
                    _this.public_next_check = info.taskid > 0 ? true : false;
                    if (_this.public_next_check) {
                        _this.public_next_time = formatDatetime(new Date(info.n_time * 1000));
                    }
                    _this.call_log_id = info.call_log_id;
                } else {
                    message.error(data.msg);
                    return false;
                }
            }).catch(function(e) {

            })
        },
    }
}
</script>
<style scoped>
.dialog_item {
    margin-top: 25px;
    display: flex;
}

.item_span {
    width: 90px;
    text-align: right;
    display: block;
    font-size: 14px;
    color: #454545;
}

.dialog-footer {
    padding: 30px 0 0;
    text-align: right;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}
</style>