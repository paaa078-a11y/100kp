<template>
	<div class="moduleElHight" v-if="islook">
		<div class=" moduleTable">

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
							<div class="TableTite">聊天功能</div>
						</td>
						<td>
							<div class="TableButn">
								<el-switch v-model="config.sy_chat_open" active-value="1" inactive-value="2"></el-switch>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>开启后前端才能显示</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">个人使用条件</div>
						</td>
						<td>
							<div class="TableButn">
								<el-radio v-model="config.sy_chat_limit" label="1">拥有简历</el-radio>
								<el-radio v-model="config.sy_chat_limit" label="2">申请职位</el-radio>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>个人和企业聊天需要满足的条件</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">聊天消费模式</div>
						</td>
						<td>
							<div class="TableButn">
								<el-radio-group v-model="config.sy_chat_rates">
									<el-radio label="1">完全免费</el-radio>
									<el-radio label="2">回复免费</el-radio>
									<el-radio label="3">回复收费</el-radio>
								</el-radio-group>
								<div v-if="config.sy_chat_rates == '2'" class="chat_hf_showbox">
									<div class="chat_hf_show">
										<div class="chat_hf_showone">过期会员能否免费回复</div>
										<el-switch v-model="config.sy_chat_exmerep" active-value="2"
											inactive-value="1"></el-switch>
									</div>
									<div class="chat_hf_show">
										<div class="chat_hf_showone chat_hf_showname">免费期回复天数</div>
										<el-input-number v-model="config.sy_chat_replyday" size="small"></el-input-number>
									</div>
									<div>

										<el-alert title="回复免费模式下，可以设定期限，N天内的回复免费，超过的收费。0或空时,没有限制天数。" type="warning"
											show-icon></el-alert>
									</div>
								</div>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>完全免费是指企业主动发起、回复聊天都免费,回复免费是指企业主动发起聊天要付费，回复聊天免费,回复收费是指企业主动发起、回复聊天都要付费</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">自动回复限制</div>
						</td>
						<td>
							<div class="TableButn">
								<template v-if="ratings.length>0">
								    <el-checkbox-group v-model="config.sy_chat_zdrat">
								        <div v-for="(item, index) in ratings" class="tc_checkbox" :key="index">
								            <el-checkbox :label="item.id">{{ item.name }}</el-checkbox>
								        </div>
								    </el-checkbox-group>
								</template>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>企业会员中只有选中的会员类型才能设置和被动触发自动回复</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">微信小程序聊天内容检测</div>
						</td>
						<td>
							<div class="TableButn">
								<el-switch v-model="config.sy_chat_concheck" active-value="2"
									inactive-value="1"></el-switch>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>开启后，微信小程序内发送的聊天内容，将采用微信提供的内容安全接口来检测。</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">换电话/换微信</div>
						</td>
						<td>
							<div class="TableButn">
								<el-switch v-model="config.sy_chat_exphone" active-value="2" inactive-value="1"
									size="small"></el-switch>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>开启后聊天页面有换电话，换微信按钮</span>
							</div>
						</td>
					</tr>

					<tr>
						<td>
							<div class="TableTite">聊天代替词</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input v-model="config.sy_chat_name" placeholder="聊天代替词"></el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>聊一聊、云聊、沟通</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">聊天记录展示天数</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input-number v-model="config.sy_chat_day" size="small"></el-input-number>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>前端展示聊天历史记录天数，默认30天</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">聊天撤回时限</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input-number v-model="config.sy_chat_recalltime" size="small"></el-input-number>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>单位：分钟。聊天发送后，X分钟内可撤回。目前文字、图片、语音消息可撤回。设置为0时，无法撤回。</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">App Key</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input v-model="config.sy_chat_appkey"></el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span><el-link type="primary" href="https://u.phpyun.com"
										target="_blank">前往申请聊天秘钥</el-link></span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">App Secret</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input v-model="config.sy_chat_appsecret"></el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span><el-link type="primary" href="https://u.phpyun.com"
										target="_blank">前往申请聊天秘钥</el-link></span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">服务状态</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input v-model="config.servstatus" :readonly="true"></el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">

							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">防骗提醒</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input type="textarea" v-model="config.sy_chat_warn"></el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">

							</div>
						</td>
					</tr>
				</tbody>
				<tr>
					<td>
						<div class="TableTite">聊天图标</div>
					</td>
					<td>
						<el-upload class="avatar-uploader" :action="uploadUrl" :auto-upload="true" :show-file-list="false"
							:multiple="false" :data="uploadData" :accept="pic_accept" :limit="1"
							:on-success="uploadSuccess" list-type="picture">
							<div v-if="config.sy_chat_logo">
								<img :src="config.sy_chat_logo" width="120px" height="auto" class="avatar">
								<div slot="tip" class="el-upload__tip">点击上传图片</div>
							</div>
							<i v-else class="el-icon-plus avatar-uploader-icon"></i>
						</el-upload>

					</td>
					<td>
						<div class="TableShuom">
							点击上传图片
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="setBasicButn" style="border: none;">
			<el-button type="primary" size="medium" :loading="post_loading" @click="postSet">提交</el-button>
		</div>

	</div>
</template>

<script>
module.exports = {
	data: function () {
		return {
			pic_accept: localStorage.getItem("pic_accept"),
			islook: false,
			config: {},
			ratings:[],
			post_loading: false,

			fileList: [],
			uploadUrl: localStorage.getItem("baseUrl") + '?m=index&c=layui_upload',
			uploadData: {
				name: 'sy_chat_logo',
				path: 'logo',
				imgid: 'imgicon',
				source: 'back',
				pytoken: localStorage.getItem("pytoken")
			},
		}
	},
	mounted() {
		this.getInfo();
	},
	methods: {
		async getInfo() {
			let that = this;

			httpPost('m=tool&c=chatset&a=index', {}).then((result) => {

				var res = result.data;
				if (res.error == 0) {
					that.config = res.data.config;
					that.ratings = res.data.ratings;
				}
				that.islook = true;
			}).catch(function (e) {
				console.log(e)
			})
		},
		async postSet() {

			let that = this;

			if (that.config.sy_chat_open == 1) {
				if (that.config.sy_chat_name == '') {
					message.warning('请填写聊天代替词！');
					return false;
				}
				var regPos = /^[0-9]+.?[0-9]*$/;
				if (!regPos.test(that.config.sy_chat_day)) {
					message.warning('请填写正确的展示天数！');
					return false;
				}
				if (that.config.sy_chat_appkey == '') {
					message.warning('请填写appkey！');
					return false;
				}
				if (that.config.sy_chat_appsecret == '') {
					message.warning('请填写appsecret！');
					return false;
				}
			}

			that.post_loading = true;

			httpPost('m=tool&c=chatset&a=save', that.config).then((result) => {

				that.post_loading = false;

				var res = result.data;

				message.success(res.msg, that.getInfo);

			}).catch(function (e) {
				console.log(e)
			})
		},
		uploadSuccess: function (res, file) {
			if (res.code == 0) {
				this.config.sy_chat_logo = res.data.url
			} else {
				message.error(res.msg);
			}
		}

	},
};
</script>
<style scoped>
.moduleTable {
	max-height: calc(100% - (100px + 10px));
}
</style>