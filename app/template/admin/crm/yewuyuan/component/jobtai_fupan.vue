<!---->
<template>
	<div class="" v-loading="loading" style="padding-bottom: 20px;">
		<div>
			复盘日期：<el-date-picker v-model="formData.date" type="date" value-format="yyyy-MM-dd" @change="getData"
				:clearable="false"></el-date-picker>
		</div>
		<div class="fp_nbox">
			<div class="fp_b_list">通话量 <div class="fp_n">{{ fupanData.call_num }}</div>
			</div>
			<div class="fp_b_list">有效通话量 <div class="fp_n">{{ fupanData.call_num_valid }}</div>
			</div>
			<div class="fp_b_list">拓新量 <div class="fp_n">{{ fupanData.tuoxin_num }}</div>
			</div>
		</div>
		<div class="fp_nbox">
			<div class="fp_b_list">跟进量 <div class="fp_n">{{ fupanData.follow_num }}</div>
			</div>
			<div class="fp_b_list">成交额 <div class="fp_n">{{ fupanData.deal_num }}</div>
			</div>
			<div class="fp_b_list">新增岗位 <div class="fp_n">{{ fupanData.job_num }}</div>
			</div>
		</div>
	</div>
</template>

<script>
var now = new Date();
module.exports = {
	props: {
	},
	data: function () {
		return {
			loading: false,
			formData: {
				date: formatDate(new Date(now.setDate(now.getDate() - 1))), // 默认昨天
			},
			fupanData: {},
		}
	},
	mounted() {
		this.getData();
	},
	methods: {
		async getData() {
			let that = this,
				formData = that.formData;

			this.loading = true;

			httpPost('m=crm&c=crm_index&a=getFupanData', formData).then((response) => {
				that.loading = false;
				let res = response.data;

				if (res.error === 0) {
					this.fupanData = res.data;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
	},
};
</script>
<style scoped></style>