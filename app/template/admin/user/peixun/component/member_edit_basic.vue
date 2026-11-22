<template>
    <div class="setUpload">
        <div class="uploadTable">
            <div class="peixunData">
                <span>登录次数： {{member.login_hits}}</span>
                <span>注册IP：{{member.reg_ip}}</span>
                <span>登录IP： {{member.login_ip}}</span>
                <span>注册时间：{{member.reg_date_n}}</span>
                <span>最近登录时间：{{member.login_date_n}}</span>
            </div>
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="180">名称</th>
                        <th width="480">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="TableTite">机构全称</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.name" placeholder="请输入机构全称">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">培训方向</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-select v-model="ruleForm.sid" placeholder="请选择" style="width: 100%;">
                                    <el-option v-for="sitem in subjectList" :key="sitem.id" :label="sitem.name"
                                        :value="sitem.id">
                                    </el-option>
                                </el-select>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">机构性质</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-select v-model="ruleForm.pr" placeholder="请选择" style="width: 100%;">
                                    <el-option v-for="pritem in jobPrList" :key="pritem.id" :label="pritem.name"
                                               :value="pritem.id">
                                    </el-option>
                                </el-select>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">机构规模</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-select v-model="ruleForm.mun" placeholder="请选择" style="width: 100%;">
                                    <el-option v-for="munitem in jobMunList" :key="munitem.id" :label="munitem.name"
                                               :value="munitem.id">
                                    </el-option>
                                </el-select>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">机构地址</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-select v-model="ruleForm.provinceid" placeholder="请选择" @change="changeCity($event, 1)">
                                    <el-option v-for="item in provinceList" :key="item.id" :label="item.name"
                                        :value="item.id">
                                    </el-option>
                                </el-select>
                                <el-select v-model="ruleForm.cityid" placeholder="请选择" style="margin: 0 8px;" @change="changeCity($event, 2)">
                                    <el-option v-for="item in cityList" :key="item.id" :label="item.name"
                                        :value="item.id">
                                    </el-option>
                                </el-select>
                                <el-select v-model="ruleForm.threecityid" placeholder="请选择" clearable>
                                    <el-option v-for="item in regionList" :key="item.id" :label="item.name"
                                        :value="item.id">
                                    </el-option>
                                </el-select>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">机构地址</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.address" placeholder="请输入机构地址">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">联系人</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.linkman" placeholder="请输入联系人">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">手机号码</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.moblie" placeholder="请输入手机号码"  @input="inputIntNumber($event, 'ruleForm', 'moblie')">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">E-mail</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.email" placeholder="请输入E-mail">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">联系电话</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.linkphone" placeholder="请输入联系电话" @input="inputLinkphone($event, 'ruleForm', 'linkphone')">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">联 系 QQ</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.linkqq" placeholder="请输入联系QQ" @input="inputIntNumber($event, 'ruleForm', 'linkqq')">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">成立时间</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-date-picker v-model="ruleForm.sdate" type="date" placeholder="选择日期">
                                </el-date-picker>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>成立时间</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">机构网址</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.website" placeholder="请输入机构网址" @input="inputWebsite($event, 'ruleForm', 'website')">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>机构网址</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">企业简介</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <div id="editor—wrapper" style="border: 1px solid #ccc;">
                                    <div id="toolbar-container"><!-- 工具栏 --></div>
                                    <div id="editor-container" style="height: 300px;"><!-- 编辑器 --></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>企业简介</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="save" :disabled="saveLoading">提交</el-button>
        </div>
    </div>
</template>

<script>
let editor = null,editorInterval = null;
const { createEditor, createToolbar } = window.wangEditor;
module.exports = {
    props: {
        uid: String,
        info: Object,
        member: Object
    },
    data: function () {
        return {
            ruleForm: {},

            saveLoading: false,

            jobPrList: [],
            jobMunList: [],
            subjectList: [],
            cityList: [],
            provinceList: [],
            regionList: [],
        }
    },
    mounted() {
        
        this.initEditor();
    },
    beforeDestroy() {
        editor = null; 
        editorInterval = null;
    },
    created() {
        this.getCache();
        this.setRuleForm();
    },
    methods: {
        initEditor: function (content=null) {
            clearInterval(editorInterval);
            editorInterval = setInterval(()=>{
                if (editor !== null){
                    clearInterval(editorInterval);
                    if(content!==null){
                        editor.setHtml(content);
                    }
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
                    
                    let toolbar = createToolbar({
                        editor,
                        selector: '#toolbar-container',
                        config: {
                            'toolbarKeys': [
                                "bold",
                                "underline",
                                "italic",
                                "clearStyle",
                                "|",
                                "bulletedList",
                                "numberedList",
                                "justifyLeft",
                                "justifyRight",
                                "justifyCenter"
                            ]
                        },
                        mode: 'simple'
                    });
                }
            },300);
            
        },
        getCache() {
            let that = this;

            httpPost('m=user&c=peixun_member&a=getCache', {}, {hideloading: true}).then(function (response) {
                let res = response.data,
                    data = res.data;

                that.jobPrList = data.jobPrList;
                that.jobMunList = data.jobMunList;
                that.subjectList = data.subjectList;
            })
        },

        getCity(level) {
            let that = this;

            httpPost('m=common&c=cache&a=getCity', {
                provinceid: that.info.provinceid,
                cityid: that.info.cityid,
                level: typeof level !== "undefined" ? level : ''
            }, {hideloading: true}).then(function (response) {
                let res = response.data,
                    data = res.data;

                if (typeof data.provinceList !== "undefined") {
                    that.provinceList = data.provinceList;
                }
                if (typeof data.cityList !== "undefined") {
                    that.cityList = data.cityList;
                }
                if (typeof data.regionList !== "undefined") {
                    that.regionList = data.regionList;
                }
            })
        },

        changeCity(val, level) {
            if (level == 1) {
                this.info.provinceid = val;
                this.info.cityid = '';
                this.info.threecityid = '';
            } else if (level == 2) {
                this.info.cityid = val;
                this.info.threecityid = '';
            }
            this.getCity(level);
        },

        setRuleForm() {
            let info = this.info,
                member = this.member;

            if (typeof info.uid !== 'undefined') {
                this.getCity();

                this.ruleForm = {
                    uid: info.uid,
                    name: info.name,
                    sid: info.sid !== '' && info.sid > 0 ? info.sid : '',
                    pr: info.pr !== '' && info.pr > 0 ? info.pr : '',
                    mun: info.mun !== '' && info.mun > 0 ? info.mun : '',
                    address: info.address,
                    linkman: info.linkman,
                    moblie: member.moblie,
                    email: member.email,
                    linkphone: info.linkphone,
                    linkqq: info.linkqq,
                    sdate: info.sdate,
                    website: info.website,
                    provinceid: info.provinceid !== '' && info.provinceid > 0 ? info.provinceid : '',
                    cityid: info.cityid !== '' && info.cityid > 0 ? info.cityid : '',
                    threecityid: info.threecityid !== '' && info.threecityid > 0 ? info.threecityid : ''
                }

                this.initEditor(info.content);
            }
        },

        inputIntNumber(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9]/g,'');
        },
        inputWebsite(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9-a-zA-Z.:/?=_]/g, '');
        },
        inputLinkphone(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9-]/g, '');
        },

        save() {
            let that = this,
                params = that.ruleForm,
                content = editor.getHtml();

            let emailReg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
            if (params.email && emailReg.test(params.email) == false) {
                message.warning('邮箱格式错误');
                return false;
            }

            let mobileReg = /^[1][3456789]\d{9}$/;
            if (params.moblie && mobileReg.test(params.moblie) == false) {
                message.warning('手机格式错误');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            params.content = content;

            httpPost('m=user&c=peixun_member&a=editSave', params).then(function (response) {
                let res = response.data;

                if (res.error > 0) {
                    that.saveLoading = false;
                    message.error(res.msg);
                } else {
                    that.$emit("child-event");
                    message.success(res.msg, function () {
                        that.saveLoading = false;
                    });
                }
            })
        }
    },
    watch: {
        uid: function (val, oldVal) {
            this.ruleForm = {};
            this.initEditor('');
            this.setRuleForm();
        }
    }
};
</script>
<style scoped></style>