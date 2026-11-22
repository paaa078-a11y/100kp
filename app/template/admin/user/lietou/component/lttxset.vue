<template>
	
	<div class="setUpload">
	        <div class="uploadTable">
	            <table class="tableVue">
	                <thead>
	                    <tr align="left">
	                        <th width="180">名称</th>
	                        <th width="260">状态</th>
	                        <th>说明</th>
	                    </tr>
	                </thead>
	                <tbody>
						<tr>
						    <td>
						        <div class="TableTite">默认LOGO</div>
						    </td>
						    <td>
						       <div class="TableUpload">

                                   <el-upload class="upload-demo" :accept="pic_accept" :action="uploadurl" :on-success="handleQrcodeSuccess"
                                              list-type="picture" :show-file-list="false" :data="{ name: 'sy_lt_icon', path: 'logo', imgid: 'imgicon', pytoken: pytoken }">
                                       <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                       <img class="el-upload-list__item-thumbnail" width="140" style="padding-left: 5px;" v-if="sy_lt_icon"
                                            :src="sy_lt_icon"/>
                                   </el-upload>
						       </div>
						    </td>
						    <td>
						        <div class="TableShuom">
						            <span>企业没有上传logo时展示的图片</span>
						        </div>
						    </td>
						</tr>
	                </tbody>
	            </table>
	        </div>
	        <div class="setBasicButn" style="border: none; height: 80px;">
	            <el-button type="primary" size="medium" @click="save" :loading="saveLoading">提交</el-button>
	        </div>
	    </div> 
	
	
	

</template>
    
<script>
module.exports = {
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            input: '',
            value1: true,
            textarea: '',
            sy_lt_icon:'',
            uri: "m=user&c=",
            uploadurl: baseUrl + 'm=index&c=layui_upload',
            pytoken: localStorage.getItem("pytoken"),
            saveLoading: false
        }
    },
    created(){
        this.getUrl();
    },
    methods: {
        getUrl:function () {
            let url = this.uri+'admin_ltset&a=getLogo';
            let _this = this;
            httpPost(url, {}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.sy_lt_icon = res.data.sy_lt_icon;
                    // 有些参数需要
                }
            })
        },
        save:function () {
            let url = this.uri+'admin_ltset&a=logo';
            let _this = this;
            _this.saveLoading = true;
            httpPost(url, {}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg,_this.getUrl());
                    // 有些参数需要
                }else{
                    message.error(res.msg);
                }
            }).finally(function () {
                setTimeout(function () {
                    _this.saveLoading = false;
                }, 2000);
            });
        },
        handleQrcodeSuccess(res) {
            if (res.code == 0) {
                this.sy_lt_icon = res.data.url
            } else {
                message.error(res.msg);
            }
        },
    },
};
</script>
<style scoped></style>