<!---->
<template>
	<div class="drawerModlue">
		<div class="drawerModInfo">
			<el-alert title="注意：提交后将不能修改！" type="info" show-icon></el-alert>
			<div class="hydialog_item">
				<span class="item_span">日志标题：</span>
				<div class="hydialModFrom">
					<el-input v-model="logtitle"></el-input>
				</div>
			</div>


			<div class="hydialog_item">
				<span class="item_span">日志内容：</span>
				<div class="hydialModFrom">
					<el-input type="textarea" v-model="logcontent" :rows="5"></el-input>
				</div>
			</div>

		</div>
		<div class="setBasicButn" style="border: none;">
			<el-button type="primary" size="medium" @click="saveinfo" :loading="saveloading">提交</el-button>
		</div>
	</div>
</template>

<script>

module.exports = {

	data: function () {
		return {
			logtitle: '',
			logcontent: '',
			saveloading: false,
		}
	},

	mounted() {

	},
	methods: {

		async saveinfo() {

			var param = {
				logtitle: this.logtitle,
				logcontent: this.logcontent,
			};
			this.saveloading = true;
			httpPost('m=crm&c=workLogYwy&a=add', param).then((result) => {
				this.saveloading = false;
				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, () => {
						this.$emit("close-update");
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (e) {
				console.log(e)
			})
		},

	},
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
	display: none;
}

.drawerModlue {
	overflow: hidden;
	position: relative;
	width: 100%;
	height: 100%;
	padding: 0;
}

.drawerModInfo {
	overflow-y: auto;
	height: calc(100% - 80px);
	width: 100%;
	padding: 0;
}

.drawerModInfo .hydialog_item {
	overflow: hidden;
	position: relative;
	padding: 6px 0;
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.drawerModInfo .hydialModFrom {
	overflow: hidden;
	position: relative;
	width: calc(100% - 75px);
}

.drawerModInfo .hydialModFrom .el-select {
	width: 100%;
}
</style>