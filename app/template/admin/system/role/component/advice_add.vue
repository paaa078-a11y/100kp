<template>
    <div style="margin:10px;">
        <div class="moduleTable">
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
                        <div class="TableTite">适用端</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-radio-group v-model="plat">
                                <el-radio label="1">企业端</el-radio>
                                <el-radio label="2">用户端</el-radio>
                                <el-radio label="3">管理后台</el-radio>
                            </el-radio-group>
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
                        <div class="TableTite">标题</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入标题" v-model="title">
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
                        <div class="TableTite">描述</div>
                    </td>
                    <td colspan="2">
                        <div style="border: 1px solid #ccc;">
                            <div id="toolbar-container-comdesc"><!-- 工具栏 --></div>
                            <div id="editor-container-comdesc" style="height: 300px;"><!-- 编辑器 --></div>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span></span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="save">提交</el-button>
        </div>
    </div>
</template>
<script>
    let editor_desc = null,toolbar_desc = null,editorInterval = null;
    const {createEditor, createToolbar} = window.wangEditor;
    module.exports = {
        props: {
            info: {
                type: Object,
                default: function(){
                    return {}
                }
            },
        },
        data: function () {
            return {
                id: 0,
                title: '',
                plat: '',
                content: '',
                islook: false
            }
        },
        watch:{
            info: {
                handler(val) {
                    var that = this
                    this.id = val.id ? val.id : 0
                    this.title = val.title ? val.title : ''
                    this.plat = val.plat ? '' + val.plat : ''
                    this.content = val.content ? val.content : ''
                    this.initEditor(this.content)
                },
                immediate: true,
                deep: true,
            },
        },
        beforeDestroy() {
            editor_desc = null; 
            toolbar_desc = null;
            editorInterval = null;
        },
        methods: {
            initEditor(content=null) {
                clearInterval(editorInterval);
                editorInterval = setInterval(()=>{
                    if (editor_desc !== null){
                        clearInterval(editorInterval);
                        if(content!==null){
                            editor_desc.setHtml(content);
                        }
                    }else{
                        let editorConfig = {
                            MENU_CONF: {
                                uploadImage: {
                                    server: baseUrl + 'm=index&c=uploadfile&dir=advice',
                                    fieldName: 'file'
                                }
                            }
                        };
                        // 企业简介
                        if (!editor_desc) {
                            editor_desc = createEditor({
                                selector: '#editor-container-comdesc',
                                html: '',
                                config: editorConfig,
                                mode: 'simple'
                            });
                        }
                        if (!toolbar_desc) {
                            toolbar_desc = createToolbar({
                                editor: editor_desc,
                                selector: '#toolbar-container-comdesc',
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
                if(that.title == '') {
                    message.error('请输入标题');
                    return false;
                }
                if(that.plat == '') {
                    message.error('请选择适用端');
                    return false;
                }
                // 去除html标签后判断内容是否为空
                var regex = /(<([^>]+)>)/ig
                var content = editor_desc.getHtml().replace(regex, "")
                if (content == "") {
                    message.error('请输入描述！')
                    return false;
                }
                var params = {
                    id: that.id,
                    title: that.title,
                    plat: that.plat,
					content:editor_desc.getHtml(),
                };
                httpPost('m=system&c=role_advice&a=save', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.$parent.$parent.drawer = false
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