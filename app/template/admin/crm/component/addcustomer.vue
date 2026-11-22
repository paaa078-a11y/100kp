<template>
    <div class="drawerModlue" v-loading="addloading">
        <div class="drawerModInfo">
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>类型：</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio-group v-model="clue">
                        <el-radio label="1">入库企业</el-radio>
                        <el-radio label="2">线索企业</el-radio>
                    </el-radio-group>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>公司名称：</span>
                </div>
                <div class="drawerModInpt">
                    <el-popover placement="right" width="250" trigger="manual" v-model="same_show">
                        <el-table :data="sameData" height="500px" :empty-text="emptytext" v-loading="loading">
                            <el-table-column width="150" property="name" label="名称"></el-table-column>
                            <el-table-column width="100" property="crm_name" label="所属业务员"></el-table-column>
                        </el-table>
                        <el-input slot="reference" v-model="name" placeholder="请输入公司名称" @input="checkComName" @blur="same_show = false" class="w_350"></el-input>
                    </el-popover>
                </div>
                <div class="drawerModTips">
                    <el-alert title="检索信息为已存在客户" type="info" show-icon :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>联系人员：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="linkman" placeholder="请输入联系人名称" class="w_350"></el-input>
                </div>
            </div>

            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>手机号码：</span>
                </div>
                <div class="drawerModInpt">
                    <el-tooltip :disabled="mobiletip_disabled" v-model="mobile_error" class="item" effect="dark" :hide-after="2000" :content="mobile_msg" placement="right">
                        <el-input class="w_350" type="number" maxlength='11' autocomplete="off" @blur="checkUsername" v-model="mobile" placeholder="请输入手机号" ></el-input>
                    </el-tooltip>
                </div>
                <div class="drawerModTips">
                    <el-alert title="手机号即为登录账号" type="info" show-icon :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>账号密码：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input class="w_350" ref="pwinput" show-password :readonly="pwReadonly" @focus="removeReadonly" @blur="addReadonly" @input="pwInput" autocomplete="off" auto-complete="off" :value="password" placeholder="输入账号密码" ></el-input>
                </div>
                <div class="drawerModTips">
                    <el-alert title="账号登录使用,非必填,默认随机生成" type="info" show-icon :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>所在地区：</span>
                </div>
                <div class="drawerModInpt">
                    <el-cascader class="w_350" :options="cityoptions" @change="cityChoose" :props="{ checkStrictly: true }" clearable ></el-cascader>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>详细地址：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="address"></el-input>
                </div>
            </div>

            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>固定电话：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="linkphone" class="w_350"></el-input>
                </div>
            </div>

            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>企业简介：</span>
                </div>
                <div class="drawerModInpt">
                    <div id="editor—wrapper" style="border: 1px solid #ccc;">
                        <div id="toolbar-container"><!-- 工具栏 --></div>
                        <div id="editor-container" style="height: 260px;"><!-- 编辑器 --></div>
                    </div>
                </div>
            </div>
			<div class="drawerModLis">
				<div class="drawerModTite">
					<span>其他信息：</span>
				</div>
				<div class="drawerModInpt drawerModelecter">
					<el-select v-model="hy" placeholder="所属行业" clearable>
						<el-option v-for="item in industry_index" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
					<el-select v-model="pr" placeholder="企业性质" clearable>
						<el-option v-for="item in jobpr" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
					<el-select v-model="mun" placeholder="企业规模" clearable>
						<el-option v-for="item in job_mun" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
				</div>
			</div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>业务信息：</span>
                </div>
				<div class="drawerModInpt drawerModelecter">
                    <el-select v-model="crm_type" placeholder="客户等级" clearable>
                        <el-option v-for="item in client_type" :key="item.val" :label="item.name" :value="item.val"></el-option>
                    </el-select>
                    <el-select v-model="crm_status" placeholder="客户标签" clearable>
                        <el-option v-for="item in client_status" :key="item.val" :label="item.name" :value="item.val"></el-option>
                    </el-select>
                    <el-select v-model="rating_name" placeholder="会员等级" clearable>
                        <el-option v-for="item in rating_list" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
				</div>
			</div>
			<div class="drawerModLis">
				<div class="drawerModTite">
					<span>业务经理：</span>
				</div>
				<div class="drawerModInpt" style="display: flex;">
                    <el-select v-model="crm_uid" placeholder="请选择业务经理" clearable style="width: 350px;">
                        <el-option v-for="item in crmUser" :key="item.uid" :label="item.name" :value="item.uid"></el-option>
                    </el-select>
                    <div class="drawerModTips" style="padding-left: 0;">
                        <el-alert title="未选择业务经理，客户将绑定在自己名下" type="info" show-icon :closable="false"></el-alert>
                    </div>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>审核状态：</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio-group v-model="r_status">
                        <el-radio label="1">已审核</el-radio>
                        <el-radio label="0">待审核</el-radio>
                    </el-radio-group>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="saveinfo" :loading="saveloading">提交</el-button>
        </div>
    </div>
</template>

<script>
    let editor = null, jobtoolbar = null,editorInterval = null;
    const {createEditor, createToolbar} = window.wangEditor;
    module.exports = {
        props: {
            mobile_v: {
                type: String,
                default: ''
            }
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                mobile: '',

                same_show: false,
                sameData: [],

                mobiletip_disabled: true,
                mobile_error: false,
                mobile_msg: '',
                pwReadonly: true,
                cityoptions: [],

                crmUser: [],
                rating_list: [],
                industry_index: [],
                jobpr: [],
                job_mun: [],
                client_status: [],
                client_type: [],

                clue: '1',
                name: '',
                linkman: '',
                password: '',
                address: '',
                provinceid: '',
                cityid: '',
                three_cityid: '',
                linkphone: '',
                hy: '',
                pr: '',
                mun: '',
                crm_type: '',
                crm_status: '',
                rating_name: '',
                crm_uid: '',
                r_status: '1',
                addloading: false,
                saveloading: false,
            }
        },
        watch: {
            mobile_v: {
                handler(n, v) {
                    if (n != '') {
                        this.mobile = n.trim();
                    }
                },
                deep: true
            },
        },
		beforeDestroy() {
			editor = null; 
			jobtoolbar = null;
			editorInterval = null;
		},
        created: function () {
            this.cityoptions = cityCascader();
            this.getInfo();
        },
        mounted() {
            this.initEditor();
        },
        methods: {
			initEditor: function () {
				clearInterval(editorInterval);
				editorInterval = setInterval(()=>{
					if (editor !== null){
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
						editor = createEditor({
						    selector: '#editor-container',
						    html: '',
						    config: editorConfig,
						    mode: 'simple'
						});
						
						jobtoolbar = createToolbar({
						    editor,
						    selector: '#toolbar-container',
						    config: {
						        excludeKeys: ['blockquote', 'header1', 'header2', 'header3', '|', 'through', 'color', 'bgColor', 'insertLink', 'todo', '|', 'insertVideo', 'insertTable', 'codeBlock', '|', 'undo', 'redo', '|',]
						    },
						    mode: 'simple'
						});
					}
				},300);
				
			},
            async getInfo() {
                let that = this;

                this.addloading = true;

                httpPost('m=crm&c=crm_my_customer&a=add', {}).then((result) => {

                    this.addloading = false;

                    var res = result.data;
                    if (res.error == 0) {
                        that.crmUser = res.data.crmUser;
                        that.rating_list = res.data.rating_list;
                        that.industry_index = res.data.industry_index;
                        that.jobpr = res.data.jobpr;
                        that.job_mun = res.data.job_mun;
                        that.client_status = res.data.client_status;
                        that.client_type = res.data.client_type;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async checkComName() {

                let that = this;
				if (this.name.length < 4){
                    return false;
                }
                var param = {
                    companyName: this.name
                };
                that.loading = true;
                this.same_show = false;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=checkComName', param).then((result) => {


                    var res = result.data;
                    if (res.error == 0) {
                        that.sameData = res.data;
                        this.same_show = that.sameData.length > 0 ? true : false;
                        that.loading = false;
                        if (that.sameData.length === 0) {
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },

            async checkUsername() {
                let that = this;

                var param = {
                    moblie: this.mobile
                };

                httpPost('m=crm&c=crm_my_customer&a=check', param).then((result) => {

                    var res = result.data;
                    if (res.error == 0) {
                        that.mobiletip_disabled = true;
                        that.mobile_error = false;
                        that.mobile_msg = '';
                    } else {
                        that.mobiletip_disabled = false;
                        that.mobile_error = true;
                        that.mobile_msg = res.msg;
                        setTimeout(function () {
                            that.mobiletip_disabled = true;
                            that.mobile_error = false;
                            that.mobile_msg = '';
                        }, 2000);
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async saveinfo() {

                var content = editor.getHtml();
                if (this.mobile == '' && this.clue == 1) {
                    message.warning('手机号码不能为空！');
                    return false;
                }
                if (this.name == '' && this.clue == 1) {
                    message.warning('公司名称不能为空！');
                    return false;
                }
                if (this.mobile != '' && isjsMobile(this.mobile) == false) {
                    message.warning('手机号码格式错误！');
                    return false;
                }
                if (this.linkman == '' && this.clue == 1) {
                    message.warning('联系人不能为空！');
                    return false;
                }
                if (this.clue == 2 && this.mobile == '' && this.name == '' && this.provinceid == '' && this.cityid == '' && this.address == '' && this.linkman == '' && this.linkphone == '' && content == '') {
                    message.warning('至少填写一条有效线索！');
                    return false;
                }

                var param = {
                    clue: this.clue,
                    moblie: this.mobile,
                    name: this.name,
                    linkman: this.linkman,
                    password: this.password,
                    address: this.address,
                    provinceid: this.provinceid,
                    cityid: this.cityid,
                    three_cityid: this.three_cityid,
                    linkphone: this.linkphone,
                    hy: this.hy,
                    pr: this.pr,
                    mun: this.mun,
                    crm_type: this.crm_type,
                    crm_status: this.crm_status,
                    rating_name: this.rating_name,
                    crm_uid: this.crm_uid,
                    content: content,
                    r_status: this.r_status
                };
                this.saveloading = true;
                httpPost('m=crm&c=crm_my_customer&a=addsave', param).then((result) => {
                    this.saveloading = false;
                    var res = result.data;
                    if (res.error == 0) {
                        message.success(res.msg, () => {

                            this.$emit("close-update");
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            pwInput: function (e) {
                if (e.length == '' && this.password != '') {
                    this.$refs.pwinput.blur();
                    this.pwReadonly = true;
                    this.password = '';
                }
                this.password = e;
            },
            removeReadonly: function () {
                setTimeout(() => {
                    this.pwReadonly = false;
                }, 200)
            },
            addReadonly: function () {
                setTimeout(() => {
                    this.pwReadonly = true;
                }, 200)
            },
            cityChoose: function (val) {

                var provinceid = cityid = three_cityid = '';

                if (val.length > 0) {
                    provinceid = val[0];
                    cityid = val.length > 1 ? val[1] : '';
                    three_cityid = val.length > 2 ? val[2] : '';
                }

                this.provinceid = provinceid;
                this.cityid = cityid;
                this.three_cityid = three_cityid;

            },
        },
    };
</script>
<style scoped>
    .drawerModInfo::-webkit-scrollbar {display: none;}
    .drawerModlue {overflow: hidden;position: relative;width: 100%;height: 100%;}
    .drawerModInfo {overflow-y: auto;height: calc(100% - 80px);}
	.w_350{width: 350px;}
</style>