<template>
    <div class="drawerModlue">
        <div class="drawerModInfo drawerModXungeu">
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>名称</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.name" placeholder="请输入名称"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>使用范围</span>
                </div>
                <div class="drawerModInpt">
                    <el-select v-model="info.did" placeholder="请选择">
                        <el-option v-for="(item,index) in dnames" :key="index" :label="item"
                                   :value="index"></el-option>
                    </el-select>
                </div>
            </div>
            <!--<div class="drawerModLis">-->
                <!--<div class="drawerModTite">-->
                    <!--<span>访问次数</span>-->
                <!--</div>-->
                <!--<div class="drawerModInpt">-->
                    <!--<el-input v-model="input" placeholder="请输入内容"></el-input>-->
                <!--</div>-->
            <!--</div>-->
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>简介</span>
                </div>
                <div class="drawerModInpt">
                    <div style="border: 1px solid #ccc;">
                        <div id="toolbar-container"><!-- 工具栏 --></div>
                        <div id="editor-container" style="height: 300px;"><!-- 编辑器 --></div>
                    </div>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>开始时间</span>
                </div>
                <div class="drawerModInpt">
                    <el-date-picker v-model="info.stime_n" type="datetime" placeholder="选择日期" value-format="yyyy-MM-dd HH:mm" :picker-options="pickerOptions" @change="timeSet">
                    </el-date-picker>
                </div>
            </div>
            <!-- <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>开始时间</span>
                </div>
                <div class="drawerModInpt">
                    <el-upload class="avatar-uploader" :action="uploadAction" :show-file-list="false" multiple
                               :on-success="picChange" :accept="pic_accept">
                        <el-button type="primary" icon="el-icon-document-add">上传图片</el-button>
                    </el-upload>
                </div>
            </div> -->
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>封面图片</span>
                </div>
                <div class="drawerModInpt">
                    <el-upload class="avatar-uploader" :action="uploadAction" :show-file-list="false" multiple
                               :on-success="picChange" :accept="pic_accept">
                        <el-button type="primary" icon="el-icon-document-add">上传图片</el-button>
                    </el-upload>
                </div>
                <div class="drawerModInpt" style="margin: 10px 0 0 90px;">
                    <div v-for="(item, index) in oldpicarr" class="t_box" :key="index">
                        <el-image :src="item.url"></el-image>
                        <a href="javascript:void(0);" class="delPic" @click="delOldPic(index,item.id)">删除</a>
                    </div>
                    <div v-for="(item, index) in newpicarr" class="t_box" :key="index">
                        <el-image :src="item"></el-image>
                        <a href="javascript:void(0);" class="delPic" @click="delNewPic(index)">删除</a>
                    </div>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>上架设置</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio v-model="info.state" label="1">立即上架</el-radio>
                    <el-radio v-model="info.state" label="2">定时上架</el-radio>
                    <el-radio v-model="info.state" label="3">暂不上架</el-radio>
                </div>
            </div>
            <div class="drawerModLis" v-if="info.state == '2'">
                <div class="drawerModTite">
                    <span></span>
                </div>
                <div class="drawerModInpt">
                    <el-date-picker v-model="info.statetime_n" type="datetime" placeholder="选择上架时间" value-format="yyyy-MM-dd HH:mm">
                    </el-date-picker>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>回放设置</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio v-model="info.playback" label="1">开启</el-radio>
                    <el-radio v-model="info.playback" label="2">关闭</el-radio>
                </div>
                <div class="drawerModTips">
                    <el-alert title="开启回放，直播结束后直播间将播放回放视频。关闭回放，直播结束后直播间不播放回放视频" type="info" show-icon :closable="false">
                    </el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>前台展示</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio v-model="info.isopen" label="1">开启</el-radio>
                    <el-radio v-model="info.isopen" label="2">关闭</el-radio>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>浏览基数</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.hits_base" @input="info.hits_base=info.hits_base.replace(/[^0-9.]/g,'')" placeholder="请输入内容"></el-input>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="save" :loading="saveLoading">提交</el-button>
        </div>
    </div>
</template>
<script>
    let editor = null, toolbar = null,editorInterval = null;
    const {createEditor, createToolbar} = window.wangEditor;
    module.exports = {
        props: {
            id: {
                type: String,
                default: ''
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
                pickerOptions: {
                    disabledDate(time) {
                        // 禁止选择过去日期（不能选择今天）
                        return time.getTime() < Date.now();
                    },
                },
                xid: '',
                info: {isopen: '1', playback: '1', state: '1', did: '0'},
                dnames: {},
                picfilelist: [],
                oldpicarr: [],
                newpicarr: [],
                saveLoading: false,
                pic_accept: localStorage.getItem("pic_accept"),
                uploadAction: baseUrl + 'm=common&c=common_upload'
            }
        },
        mounted() {

        },
		beforeDestroy() {
			editor = null; 
			toolbar = null;
			editorInterval = null;
		},
        watch: {
            id: {
                handler(val, oldVal) {
                    this.xid = val;
                    this.newpicarr = []
                    if (val) {
                        this.getInfo()
                    } else {
                        this.info = {isopen: '1', playback: '1', state: '1', did: '0'}
                        this.oldpicarr = []
                    }
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
        methods: {
            timeSet(){

            },
            // 删除数据库已有的宣讲会图片
            delOldPic(index, pid){
                delConfirm(this, {pid:pid,index:index}, this.dodelpic);
            },
            dodelpic(params){
                var that = this
                httpPost('m=neirong&c=xjhlive&a=delPic', {pid: params.pid}).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function(){
                            that.$delete(that.oldpicarr, params.index)
                        });
                    } else {
                        message.error(res.msg);
                    }
                });
            },
            delNewPic(index){
                this.$delete(this.newpicarr, index)
                this.$delete(this.picfilelist, index)
            },
            // pc缩略图
            picChange(response, file, fileList) {
                // 预览文件处理
                this.newpicarr.push(URL.createObjectURL(file.raw))
                // 复刻文件信息
                this.picfilelist.push(file.raw)
            },
            getInfo(){
                var that = this
                httpPost('m=neirong&c=xjhlive&a=add', {id: that.xid}).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        that.info = res.data.info
                        if (that.info.picarr) {
                            that.oldpicarr = that.info.picarr
                        }
                        that.newpicarr = []
                        that.initEditor();
                    } else {
                        message.error(res.msg);
                    }
                });
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
						// 宣讲会介绍
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
                    message.error('请填写名称');
                    return false;
                }
                // 去除html标签后判断内容是否为空
                var regex = /(<([^>]+)>)/ig
                var result = editor.getHtml().replace(regex, "");
                if (!result) {
                    that.$message.error('请输入简介');
                    return false;
                }
                if (!that.info.stime_n) {
                    message.error('请填写开始时间');
                    return false;
                }
                if (that.oldpicarr.length == 0 && that.newpicarr.length == 0) {
                    message.error('请上传请上传封面图片');
                    return false;
                }
                if (that.info.state == '2' && !that.info.statetime_n) {
                    message.error('请选择定时上架时间');
                    return false;
                }
                that.info.body = editor.getHtml()
                var params = new FormData();
                params.append('body', that.info.body);
                params.append('content', that.info.content);
                params.append('did', that.info.did);
                params.append('hits_base', that.info.hits_base);
                params.append('id', that.info.id);
                params.append('isopen', that.info.isopen);
                params.append('name', that.info.name);
                params.append('playback', that.info.playback);
                params.append('state', that.info.state);
                params.append('statetime_n', that.info.statetime_n);
                params.append('stime_n', that.info.stime_n);
                // params.append('oldpicarr', JSON.stringify(that.oldpicarr))
                if (that.picfilelist.length) {
                    this.picfilelist.forEach((item) => {
                        params.append('pic[]', item)
                    })
                }
                that.saveLoading = true;
                httpPost('m=neirong&c=xjhlive&a=save', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.info = {isopen: '1', playback: '1', state: '1', did: '0'}
                            that.oldpicarr = []
                            that.newpicarr = []
                            that.$parent.$parent.draweradd = false
                            that.$parent.$parent.getList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).finally(function () {
                    setTimeout(function () {
                        that.saveLoading = false;
                    }, 2000);
                });
            },
        },

    };
</script>
<style scoped>
    .drawerModInfoOne .drawerModInpt .el-button {
        margin-left: 0;
    }
    .t_box {
        width: 150px;
        height: 90px;
        border: 1px solid #eee;
        position: relative;
        overflow: hidden;
        margin-right: 20px;
    }
    .t_box .el-image{
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
    }
    .t_box .el-image img{
        width: 100%;
        display: block;
        height: auto;
    }
    .t_box .delPic {
        position: absolute;
        right: 8px;
        top: 8px;
        padding: 3px 8px;
        line-height: 15px;
        font-size: 12px;
        background: #72bb38;
        color: #fff;
        border-radius: 20px;
    }
    .drawerModXungeu{
        overflow-y: auto;
        height: calc(100% - 80px);
        max-height: initial;
    }
</style>