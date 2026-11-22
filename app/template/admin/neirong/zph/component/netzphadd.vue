<template>
    <div style="margin: 0 20px; overflow: hidden; position: relative; height: 100%;">
        <div class="moduleTable" style="max-height: calc(100% - 80px);">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="400">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">招聘会标题</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入招聘会标题" v-model="info.title">
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
                        <div class="TableTite">使用范围</div>
                    </td>
                    <td>
                        <div class="TableSelect" style="display: flex;align-items: center;">
                            <el-select v-model="info.did" placeholder="请选择">
                                <el-option v-for="(item,index) in dnames" :key="index" :label="item"
                                           :value="index"></el-option>
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
                        <div class="TableTite">展会区域</div>
                    </td>
                    <td>
                        <div class="TableSelect" style="display: flex;align-items: center;">
                            <el-select v-model="info.zw" placeholder="请选择">
                                <el-option v-for="item in spaces" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>网络招聘会参展企业列表展示的展会区域</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">开始时间</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-date-picker
                                    v-model="info.starttime"
                                    type="datetime"
                                    style="width: 100%;"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    placeholder="选择日期时间">
                            </el-date-picker>
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
                        <div class="TableTite">结束时间</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-date-picker
                                    v-model="info.endtime"
                                    type="datetime"
                                    style="width: 100%;"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    placeholder="选择日期时间">
                            </el-date-picker>
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
                            <el-input placeholder="请输入联系电话" v-model="info.phone">
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
                        <div class="TableTite">显示状态</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-radio v-model="info.is_open" label="1">开启</el-radio>
                            <el-radio v-model="info.is_open" label="0">隐藏</el-radio>
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
                        <div class="TableTite">PC略缩图</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-upload class="avatar-uploader" :accept="pic_accept" :action="uploadAction" :show-file-list="false"
                                       :on-change="thumbChange">
                                <img style="width:200px;" v-if="info.pic_n" :src="info.pic_n" class="avatar">
                                <el-button v-else type="primary" icon="el-icon-document-add">上传图片</el-button>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span><i class="el-icon-warning"></i>尺寸：200PX*120PX</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">PC横幅</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-upload class="avatar-uploader" :accept="pic_accept" :action="uploadAction" :show-file-list="false"
                                       :on-change="bannerChange">
                                <img style="width:200px;" v-if="info.banner_n" :src="info.banner_n" class="avatar">
                                <el-button v-else type="primary" icon="el-icon-document-add">上传图片</el-button>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span><i class="el-icon-warning"></i>尺寸：1920PX*380PX</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">移动端略缩图</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-upload class="avatar-uploader" :accept="pic_accept" :action="uploadAction" :show-file-list="false"
                                       :on-change="thembwapChange">
                                <img style="width:200px;" v-if="info.pic_wap_n" :src="info.pic_wap_n"
                                     class="avatar">
                                <el-button v-else type="primary" icon="el-icon-document-add">上传图片</el-button>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span><i class="el-icon-warning"></i>尺寸：250PX*160PX</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">移动端横幅</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-upload class="avatar-uploader" :accept="pic_accept" :action="uploadAction" :show-file-list="false"
                                       :on-change="bannerwapChange">
                                <img style="width:200px;" v-if="info.banner_wap_n" :src="info.banner_wap_n"
                                     class="avatar">
                                <el-button v-else type="primary" icon="el-icon-document-add">上传图片</el-button>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span><i class="el-icon-warning"></i>尺寸：830PX*510PX</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">顶部滚动标题</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input type="textarea" row="2" placeholder="请输入顶部滚动标题" v-model="info.toptitle">
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
                        <div class="TableTite">招聘会介绍</div>
                    </td>
                    <td colspan="2">
                        <div style="border: 1px solid #ccc;">
                            <div id="toolbar-container"><!-- 工具栏 --></div>
                            <div id="editor-container" style="height: 300px;"><!-- 编辑器 --></div>
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
                        <div class="TableTite">浏览数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入浏览数" v-model="info.hits">
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
                        <div class="TableTite">企业基数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入企业基数" v-model="info.pnum">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>前台数据展示使用；参会企业 = 实际参会企业数量 + 企业基数</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">职位基数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入职位基数" v-model="info.jnum">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>前台数据展示使用；参会职位 = 实际参会职位数量 + 职位基数</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">招聘基数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入招聘基数" v-model="info.zpnum">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>前台数据展示使用；招聘人数 = 实际参会招聘数量 + 招聘基数</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">投递基数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入投递基数" v-model="info.anum">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>前台数据展示使用；投递简历 = 实际参会投递数量 + 投递基数</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">求职者基数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入求职者基数" v-model="info.unum">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>前台数据展示使用；参会求职者 = 实际参会求职者数量 + 求职者基数</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="save" :disabled="submitLoading">提交</el-button>
        </div>
    </div>
</template>
<script>
    let editor = null, toolbar = null,editorInterval = null;
    const {createEditor, createToolbar} = window.wangEditor;
    module.exports = {
        props: {
            cdata: {
                type: Object,
                default: function(){
                    return {}
                }
            },
            spacearr: {
                type: Array,
                default: function(){
                    return []
                }
            },
            dnamearr: {
                type: Object,
                default: function(){
                    return {}
                }
            }
        },
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                submitLoading:false,
                info: {},
                spaces: [],
                dnames: {},
                thumblist: [],
                bannerlist: [],
                wapthumblist: [],
                wapbannerlist: [],
                zwarr: [],
                uploadAction: baseUrl + 'm=common&c=common_upload'
            }
        },
        watch: {
            cdata: {
                handler(val, oldVal) {
                    this.info = val;
                },
                immediate: true,
                deep: true,
            },
            spacearr: {
                handler(val, oldVal) {
                    this.spaces = val;
                },
                immediate: true,
                deep: true,
            },
            dnamearr: {
                handler(val, oldVal) {
                    this.dnames = val;
                },
                immediate: true,
                deep: true,
            }
        },
        beforeDestroy() {
            editor = null; 
            toolbar = null; 
            editorInterval = null;
        },
        methods: {
            // pc缩略图
            thumbChange(file) {
                var tmp = deepClone(this.info)
                // 预览文件处理
                tmp.pic_n = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.thumblist[0] = file.raw;
                this.info = tmp
            },
            //PC横幅
            bannerChange(file) {
                var tmp = deepClone(this.info)
                // 预览文件处理
                tmp.banner_n = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.bannerlist[0] = file.raw;
                this.info = tmp
            },
            // wap缩略图
            thembwapChange(file) {
                var tmp = deepClone(this.info)
                // 预览文件处理
                tmp.pic_wap_n = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.wapthumblist[0] = file.raw;
                this.info = tmp
            },
            // wap横幅
            bannerwapChange(file) {
                var tmp = deepClone(this.info)
                // 预览文件处理
                tmp.banner_wap_n = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.wapbannerlist[0] = file.raw;
                this.info = tmp
            },
            initEditor() {
                var that = this
                clearInterval(editorInterval);
                editorInterval = setInterval(()=>{
                    if (editor !== null){
                        clearInterval(editorInterval);
                        if (that.info.body) {
                            editor.setHtml(that.info.body);
                        } else {
                            editor.setHtml('');
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
                        // 招聘会介绍
                        if (!editor) {
                            editor = createEditor({
                                selector: '#editor-container',
                                html: '',
                                config: editorConfig,
                                mode: 'simple'
                            });
                        }
                        if (!toolbar) {
                            toolbar = createToolbar({
                                editor: editor,
                                selector: '#toolbar-container',
                                config: {
                                    excludeKeys: ['blockquote', 'header1', 'header2', 'header3', '|', 'through', 'todo', '|', 'insertVideo', 'insertTable', 'codeBlock', '|', 'undo', 'redo', '|',]
                                },
                                mode: 'simple'
                            });
                        }
                    }
                },300);
            },
            async save() {
                let that = this;
                if (that.info.title == '') {
                    message.error('请填写招聘会名称');
                    return false;
                }
                if (!that.info.starttime) {
                    message.error('请填写开始时间');
                    return false;
                }
                if (!that.info.endtime) {
                    message.error('请填写结束时间');
                    return false;
                }
                that.info.body = editor.getHtml()
             
                if (that.thumblist.length) {
                    that.info.sl = this.thumblist[0];
                }
                if (that.bannerlist.length) {
                    that.info.hf = this.bannerlist[0];
                }
                if (that.wapthumblist.length) {
                    that.info.wapsl = this.wapthumblist[0];
                }
                if (that.wapbannerlist.length) {
                    that.info.waphf = this.wapbannerlist[0];
                }
                let params = that.info;
                var formData = new FormData();
                Object.keys(params).forEach((key) => {
                    if (Array.isArray(params[key])) {
                        params[key].forEach((v) => {
                            formData.append(key + '[]', v);
                        });
                    } else {
                        formData.append(key, params[key]);
                    }
                });
                httpPost('m=neirong&c=zphnet&a=save', formData).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.$parent.$parent.draweradd = false
                            that.thumblist = [];
                            that.bannerlist = [];
                            that.wapthumblist = [];
                            that.wapbannerlist = [];
                            that.$parent.$parent.getList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                });
            },
        },
    };
</script>
<style scoped>
    .avatar-uploader .el-upload {
        border: 1px dashed #d9d9d9;
        border-radius: 6px;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .avatar-uploader .el-upload:hover {
        border-color: #409EFF;
    }

    .avatar-uploader-icon {
        font-size: 28px;
        color: #8c939d;
        width: 148px;
        height: 148px;
        line-height: 148px;
        text-align: center;
    }

    .avatar {
        width: 148px;
        height: 148px;
        display: block;
    }
</style>