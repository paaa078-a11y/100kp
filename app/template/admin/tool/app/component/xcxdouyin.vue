<template>
	<div class="moduleElHight">
		<div class="tableDome_tip">
			<el-alert
					title="抖音小程序设置"
					type="success"
					description="">
				设置后，联系授权客服提交代码！<el-link href="https://developer.open-douyin.com/" type="primary" target="_blank">抖音开放平台</el-link>
			</el-alert>
		</div>
		<div class="moduleTable">
			<table class="tableVue">
				<thead>
					<tr align="left">
						<th width="200">名称</th>
						<th width="500">状态</th>
						<th>说明</th>
					</tr>
				</thead>
				<tbody>

					<tr>
						<td>
							<div class="TableTite">appId</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input placeholder="请输入appId" v-model="config.sy_tt_appId">
								</el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>必须配置，请到开发管理-开发设置中查看</span>
							</div>
						</td>
					</tr>

					<tr>
						<td>
							<div class="TableTite">appSecret</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input placeholder="请输入appSecret" v-model="config.sy_tt_secret">
								</el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>用于授权手机号登录功能。请到开发管理-开发设置中查看</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">手机号授权</div>
						</td>
						<td>
							<div class="TableButn">
								<el-switch v-model="config.sy_tt_phoneauth">
								</el-switch>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>关闭后使用账号授权</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">抖音小程序二维码</div>
						</td>
						<td>
							<div class="TableUpload">
								<el-upload class="upload-demo" :accept="pic_accept" :action="uploadurl" :on-success="handleQrcodeSuccess"
										   list-type="picture" :show-file-list="false" :data="qrcodeparams">
									<el-button slot="trigger" size="small" type="primary">点击上传</el-button>
									<img class="el-upload-list__item-thumbnail" width="140" style="padding-left: 5px;" v-if="sy_ttxcxu_qcode"
										 :src="sy_ttxcxu_qcode"/>
								</el-upload>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>抖音小程序二维码</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="setBasicButn" style="border: none;">
			<el-button type="primary" size="medium" @click="save" :loading="saveLoading">提交</el-button>
		</div>

	</div>
</template>

<script>
module.exports = {
	data: function () {
		return {
            pic_accept: localStorage.getItem("pic_accept"),
            config: {
                sy_tt_appId: '',
                sy_tt_secret: '',
                sy_tt_phoneauth: false
            },
            sy_ttxcxu_qcode: '',
            uploadurl: baseUrl + 'm=index&c=layui_upload',
            qrcodeparams: {name: 'sy_ttxcxu_qcode', imgid: 'imgtt', path: 'logo', pytoken: localStorage.getItem("pytoken")},
            saveLoading: false,
		}
	},
	mounted() {

	},
	methods: {
        handleQrcodeSuccess(res) {
            if (res.code == 0) {
                this.sy_ttxcxu_qcode = res.data.url
            } else {
                this.$message.error(res.msg);
            }
        },
        async getinfo() {
            let that = this;
            httpPost('m=tool&c=xcxset&a=ttindex', {}).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.config = res.data.config
                    that.sy_ttxcxu_qcode = res.data.sy_ttxcxu_qcode
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        save() {
            let that = this;
            that.saveLoading = true;
            httpPost('m=tool&c=xcxset&a=ttsave', that.config).then(function (res) {
                if (res.data.error == 0) {
                    message.success(res.data.msg);
                } else {
                    message.error(res.data.msg);
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
<style scoped></style>