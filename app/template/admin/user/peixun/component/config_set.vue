<template>
	<div class="setUpload">
		<div class="uploadTable">
			<table class="tableVue">
				<thead>
					<tr align="left">
						<th width="180">名称</th>
						<th width="400">状态</th>
						<th>说明</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="TableTite">审核信息</div>
						</td>
						<td>
							<div class="tc_checkbox">
								<el-checkbox v-model="ruleForm.px_status" label="培训会员" true-label="0" false-label="1"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox v-model="ruleForm.px_cert_status" label="培训认证" true-label="1" false-label="0"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox v-model="ruleForm.px_logo_status" label="培训LOGO" true-label="1" false-label="0"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox v-model="ruleForm.px_show_status" label="机构环境" true-label="1" false-label="0"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox v-model="ruleForm.px_banner_status" label="机构横幅" true-label="1" false-label="0"></el-checkbox>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								开启则需审核后展示
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">强制操作</div>
						</td>
						<td>
							<div class="tc_checkbox">
								<el-checkbox v-model="ruleForm.px_enforce_info" label="完善信息" true-label="1" false-label="0"></el-checkbox>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								强制操作完成才能发布信息
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="setBasicButn" style="border: none; height: 80px;">
			<el-button type="primary" size="medium" @click="submit" :disabled="saveLoading">提交</el-button>
		</div>
	</div>
</template>

<script>
module.exports = {
	data: function () {
		return {
			saveLoading: false,

			ruleForm: {}
		}
	},
	mounted() {

	},
	created() {
		this.getData();
	},
	methods: {
		init() {
			this.getData();
		},

		getData() {
			let that = this;

			httpPost('m=user&c=peixun_config').then(function (response) {
				let res = response.data,
					data = res.data;

				that.ruleForm = data.config;
			})
		},

		submit() {
			let that = this,
				ruleForm = that.ruleForm;

			if (that.saveLoading) {
				return false;
			}
			that.saveLoading = true;

			httpPost('m=user&c=peixun_config&a=config', ruleForm).then(function (response) {
				let res = response.data;

				that.saveLoading = false;
				if (res.error > 0) {
					message.error(res.msg);
				} else {
					message.success(res.msg);
				}
			})
		},
	},
};
</script>
<style scoped></style>
