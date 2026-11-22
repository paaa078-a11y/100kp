<template>
    <div class="drawerModlue">
        <div class="drawerModInfo" style="max-height: calc(100% - 80px); overflow-y: auto;">
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>标题：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.title" placeholder="请输入标题"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>副标题：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input type="textarea" rows="3" placeholder="请输入描述" v-model="ruleForm.desc"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>课程现价：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.price" placeholder="请输入价格" @input="inputFloatNumber($event, 'ruleForm', 'price')"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>课程原价：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.prices" placeholder="请输入价格" @input="inputFloatNumber($event, 'ruleForm', 'prices')"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>手机号码：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.telphone" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="请输入号码"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>课程类别：</span>
                </div>
                <div class="drawerModInpt">
                    <el-select v-model="ruleForm.lesson" placeholder="请选择">
                        <el-option v-for="item in lesson" :key="item.id" :label="item.name" :value="item.id">
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>简  介：</span>
                </div>
                <div class="drawerModInpt">
                    <div id="editor—wrapper" style="border: 1px solid #ccc;">
                        <div id="toolbar-container"><!-- 工具栏 --></div>
                        <div id="editor-container" style="height: 300px;"><!-- 编辑器 --></div>
                    </div>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>课程标签：</span>
                </div>
                <div class="drawerModInpt">
                    <el-tag
                            :key="tag"
                            v-for="tag in dynamicTags"
                            closable
                            :disable-transitions="false"
                            @close="handleClose(tag)">
                        {{tag}}
                    </el-tag>
                    <el-input
                            class="input-new-tag"
                            v-if="inputVisible"
                            v-model="inputValue"
                            ref="saveTagInput"
                            size="small"
                            @keyup.enter.native="handleInputConfirm"
                            @blur="handleInputConfirm"
                    >
                    </el-input>
                    <el-button v-else class="button-new-tag" size="small" @click="showInput">+ 添加标签</el-button>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>报名基数：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.bnum" placeholder="请输入报名基数" @input="inputIntNumber($event, 'ruleForm', 'bnum')"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>缩略图：</span>
                </div>
                <div class="drawerModInpt">
                    <el-upload class="upload-demo" :accept="pic_accept"
                               list-type="picture"
                               action=""
                               :auto-upload="false"
                               :on-change="handleChangeCover"
                               :show-file-list="false">
                        <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                        <img class="el-upload-list__item-thumbnail" width="288" height="160" style="padding-left: 5px;"
                             v-if="ruleForm.cover_n" :src="ruleForm.cover_n"/>
                    </el-upload>
                </div>
                <div class="drawerModTips">
                    <el-alert title="尺寸：750*457px" type="info" show-icon :closable="false">
                    </el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>联系人二维码：</span>
                </div>
                <div class="drawerModInpt">
                    <el-upload class="upload-demo" :accept="pic_accept"
                               list-type="picture"
                               action=""
                               :auto-upload="false"
                               :on-change="handleChangeWxQrCode"
                               :show-file-list="false">
                        <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                        <img class="el-upload-list__item-thumbnail" width="100" height="100" style="padding-left: 5px;"
                             v-if="ruleForm.wx_qr_code_n" :src="ruleForm.wx_qr_code_n"/>
                    </el-upload>
                </div>
                <div class="drawerModTips">
                    <el-alert title="尺寸：160*160px" type="info" show-icon :closable="false">
                    </el-alert>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="save" :loading="saveLoading">提交</el-button>
        </div>
    </div>
</template>

<script>
let editor = null,editorInterval = null;
const { createEditor, createToolbar } = window.wangEditor;
module.exports = {
    props: ['id'],
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            saveLoading: false,
            fileList: [],
            fileList2: [],
            lesson: [],
            ruleForm: {},

            // 添加标签
            dynamicTags: [],
            inputVisible: false,
            inputValue: ''
        }
    },
    created:function(){
        this.getInfo();
    },
	mounted() {
        this.initEditor();
        
    },
    beforeDestroy() {
        editor = null; 
        editorInterval = null;
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
                            excludeKeys: ['blockquote', 'header1', 'header2', 'header3', '|', 'through', 'todo', '|', 'insertVideo', 'insertTable', 'codeBlock', '|', 'undo', 'redo', '|',]
                        },
                        mode: 'simple'
                    });
                }
            },50);
            
        },
        getInfo() {
            let that = this;
            httpPost('m=yunying&c=skill&a=add', {id: that.id ? that.id : ''}).then(function (response) {
                let res = response.data,
                    data = res.data;

                that.lesson = data.lesson;
                if (that.id) {
                    let info = data.info;
                    let ruleForm = {
                        id: info.id,
                        title: info.title,
                        desc: info.desc,
                        price: info.price,
                        telphone: info.telphone,
                        welfare: info.welfare,
                        prices: info.prices,
                        lesson: info.lesson,
                        bnum: info.bnum,
                    }

                    if (typeof info.cover_n != 'undefined' && info.cover_n) {
                        ruleForm.cover_n = info.cover_n;
                    }
                    if (typeof info.wx_qr_code_n != 'undefined' && info.wx_qr_code_n) {
                        ruleForm.wx_qr_code_n = info.wx_qr_code_n;
                    }
                    that.ruleForm = ruleForm;

                    if (info.introduce !== '') {
                        that.initEditor(info.introduce);
                    }
                    if (info.welfare && info.welfare_n.length > 0) {
                        that.dynamicTags = info.welfare_n;
                    }
                }
            })
        },

        inputIntNumber(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9]/g,'');
        },
        inputFloatNumber(val, form, key) {
            val = val.replace(/[^0-9.]/g,'');// 去除数字和.以外其他字符
            val = val.replace(/^\./g,'');// 验证第一个字符是数字
            val = val.replace(/\.{2,}/g,'.');// 只保留第一个.
            val = val.replace(/^(\d+)\.(\d\d).*$/,'$1.$2');// 只能输入两位小数
            this.$data[form][key] = val;
        },

        save() {
            let that = this,
                params = that.ruleForm,
                formData = new FormData();
            if (typeof params.title == 'undefined' || params.title == '') {
                message.error('请填写标题');
                return;
            }
            if (typeof params.desc == 'undefined' || params.desc == '') {
                message.error('请填写副标题');
                return;
            }
            if (typeof params.price == 'undefined' || params.price === '') {
                message.error('请填写现价');
                return;
            }
            if (typeof params.telphone == 'undefined' || params.telphone == '') {
                message.error('请填写电话');
                return;
            }
            if (typeof params.lesson == 'undefined' || params.lesson == '') {
                message.error('请选择课程类别');
                return;
            }
            params.introduce = editor.getHtml();

            if (params.introduce == '' || params.introduce == '<p><br></p>') {
                message.error('请填写简介内容');
                return;
            }

            if (that.dynamicTags.length > 0) {
                params.welfare = that.dynamicTags;
            }

            if (that.saveLoading) {
                return;
            }
            that.saveLoading = true;

            $.each(params, function(key, value){
                if (key != 'cover_n' && key != 'wx_qr_code_n') {
                    formData.append(key, value);
                }
            });

            httpPost('m=yunying&c=skill&a=save', formData).then(function (response) {
                let res = response.data;

                if (res.error > 0) {
                    that.saveLoading = false;
                    message.error(res.msg);
                } else {
                    that.$emit("child-event");
                    message.success(res.msg, function () {
                        that.saveLoading = false;
                        that.resetForm();
                    });
                }
            })
        },

        // 标签开始
        handleClose(tag) {
            this.dynamicTags.splice(this.dynamicTags.indexOf(tag), 1);
        },

        showInput() {
            this.inputVisible = true;
            this.$nextTick(_ => {
                this.$refs.saveTagInput.$refs.input.focus();
            });
        },

        handleInputConfirm() {
            let inputValue = this.inputValue;
            if (inputValue) {
                this.dynamicTags.push(inputValue);
            }
            this.inputVisible = false;
            this.inputValue = '';
        },
        // 标签结束

        // 上传时触发
        handleChangeCover(file, fileList) {
            this.$set(this.ruleForm, 'cover', file.raw);
            this.$set(this.ruleForm, 'cover_n', file.url);
        },
        handleChangeWxQrCode(file, fileList) {
            this.$set(this.ruleForm, 'wx_qr_code', file.raw);
            this.$set(this.ruleForm, 'wx_qr_code_n', file.url);
        },

        resetForm() {
            this.ruleForm = {};
            this.initEditor('');
            this.fileList = [];
            this.dynamicTags = [];
        },
    },
    watch: {
        id: function (val, oldVal) {
            this.resetForm();
            this.getInfo();
        },
    }
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
    display: none;
}
.el-tag + .el-tag {
    margin-left: 10px;
}
.button-new-tag {
    margin-left: 10px;
    height: 32px;
    line-height: 30px;
    padding-top: 0;
    padding-bottom: 0;
}
.input-new-tag {
    width: 90px;
    margin-left: 10px;
    vertical-align: bottom;
}
.drawerModTite{
    width: 120px;
    text-align: right;
}
.drawerModInpt{
    width: calc(100% - 130px);
}
.drawerModTips {
    width: calc(100% - 130px);
    padding-left: 130px;
}
</style>