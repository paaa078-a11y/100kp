<template>
    <div style="height: 100%">
        <div class="drawerModInfo drawerModInfoOne">
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>项目编号</span>
                </div>
                <div class="drawerModInpt">
                    <span>{{info.id}}</span>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>项目名称</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.name" placeholder="请输入内容"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>价格预算</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.salary" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                        <span slot="suffix" class="slotspan">元</span>
                    </el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>预计工期</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.edate" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                        <span slot="suffix" class="slotspan">天</span>
                    </el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>接单截止时间</span>
                </div>
                <div class="drawerModInpt">
                    <el-date-picker v-model="info.etime_n" type="date" placeholder="选择日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>项目需求</span>
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
                    <span>联系人</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.link_man" placeholder="请输入内容"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>联系电话</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.link_moblie" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>浏览次数</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.hits" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>状 态</span>
                </div>
                <div class="drawerModInpt">
                    <el-link type="warning" :underline="false" v-if="info.etime <= time">已过期</el-link>
					<el-link type="warning" :underline="false" v-else-if="info.r_status != 1">已锁定</el-link>
					<el-link type="primary" :underline="false" v-else-if="info.status == 1">已审核</el-link>
					<el-link type="success" :underline="false" v-else-if="info.status == 2">通过</el-link>
					<el-link type="danger" :underline="false" v-else>待审核</el-link>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none;height: 80px;">
            <el-button type="primary" size="medium" @click="save" :disabled="saveLoading">提交</el-button>
        </div>
    </div>
</template>

<script>
	let editor = null,editorInterval = null;
	const { createEditor, createToolbar } = window.wangEditor;
module.exports = {
	props: {
		id_v: {
			type: String,
			default: ''
		}
	},
    data: function () {
        return {

			id: '',
			info:[],
			time:'',

			saveLoading: false
        }
    },
	watch: {
		id_v: {
			handler(n) {
				if (n != '') {
					this.id = n.trim();
					this.getInfo();
				}
			},
			deep: true,
			immediate: true
		},
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
					    config: {excludeKeys: ['blockquote', 'header1', 'header2', 'header3', '|', 'through', 'todo', '|', 'insertVideo', 'insertTable', 'codeBlock', '|', 'undo', 'redo', '|',]},
					    mode: 'simple'
					});
				}
			},300);
			
		},
		save(){
			let that = this;
			if (that.info.name == ''){
				message.error('项目名称不能为空！');
				return false;
			}
			if (that.info.salary == ''){
				message.error('价格预算不能为空！');
				return false;
			}
			if (that.info.edate == ''){
				message.error('预计工期不能为空！');
				return false;
			}
			if (that.info.etime == ''){
				message.error('接单日期不能为空！');
				return false;
			}
			if (that.info.link_man == ''){
				message.error('联系人不能为空！');
				return false;
			}
			if (that.info.link_moblie == ''){
				message.error('联系电话不能为空！');
				return false;
			}else if(!isjsMobile(that.info.link_moblie)){
				message.error('联系电话格式错误！');
				return false;
			}
			var regex = /(<([^>]+)>)/ig
			var content = editor.getHtml().replace(regex, "")
			if (content == "") {
				message.error('项目需求不能为空！')
				return false;
			} else {
				that.info.content = editor.getHtml()
			}
			let params = {
				update: 1,
				name: that.info.name,
				salary: that.info.salary,
				edate: that.info.edate,
				etime: that.info.etime_n,
				content: that.info.content,
				link_man: that.info.link_man,
				link_moblie: that.info.link_moblie,
				hits: that.info.hits,
				id: that.id
			};
			that.saveLoading = true;
			httpPost('m=user&c=supply_task&a=savetask', params).then(function(res) {
			    if (res.data.error > 0) {
			        message.error(res.data.msg);
			    } else {
			        message.success(res.data.msg, function () {
			           that.$emit("child-event");
			        });
			    }
			}).finally(function () {
				setTimeout(function () {
				    that.saveLoading = false;
				}, 2000);
			});
		},
		getInfo() {
		    let that = this;
		    let params = {};
		    if (that.id) {
		        params.id = that.id;
		    }
		    httpPost('m=user&c=supply_task&a=info', params).then(function (response) {
		        let res = response.data;
		        if (res.data.info) {
		            that.info = res.data.info;
		            if (that.info.content !== '') {
		                that.initEditor(that.info.content);
		            }
					that.time = res.data.time;
		        }
		    }).catch(function (error) {
		        console.log(error);
		    });
		},

    },

};
</script>
<style scoped>
.drawerModInfo {
    overflow-y: auto;
    height: initial;
    max-height: calc(100% - (80px + 12px));
}
.drawerModInfoOne .drawerModInpt .el-button {
    margin-left: 0;
}
</style>