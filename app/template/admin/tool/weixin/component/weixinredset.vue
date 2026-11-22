<template>
	<div class="moduleElHight">
		<div class="moduleTable" style="height: max-content; max-height: calc(100% - 80px);">
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
						<div class="TableTite">微信红包发送人昵称</div>
					</td>
					<td>
						<div class="TableInpt">
							<el-input placeholder="请输入内容" v-model="configdata.sy_wxredpack_nick"></el-input>
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
						<div class="TableTite">服务器IP</div>
					</td>
					<td>
						<div class="TableInpt">
							<el-input placeholder="请输入内容" v-model="configdata.sy_wxredpack_ip"></el-input>
						</div>
					</td>
					<td>
						<div class="TableShuom">
							<span>如：127.0.0.1</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="TableTite">红包备注</div>
					</td>
					<td>
						<div class="TableInpt">
							<el-input placeholder="请输入内容" v-model="configdata.sy_wxredpack_remark"></el-input>
						</div>
					</td>
					<td>
						<div class="TableShuom">
							<span>微信高版本暂无法展示备注信息</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="TableTite">产品类型</div>
					</td>
					<td>
						<div class="TableInpt">
							<el-radio-group v-model="configdata.sy_wxredpack_type">
								<el-radio label="1">现金红包</el-radio>
								<el-radio label="2">商家红包</el-radio>
							</el-radio-group>
						</div>
					</td>
					<td>
						<div class="TableShuom">
							<span>商户平台-产品中心申请开通</span>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<div class="setBasicButn" style="border: none;">
			<el-button type="primary" size="medium" @click="post">提交</el-button>
		</div>
	</div>
</template>
<script>
	module.exports = {
		props: {
			config: {
				type: Object,
				default: {}
			},
		},
		watch: {
			config: {
				handler(val, oldVal) {
					this.configdata = val;
				},
				immediate: true,
				deep: true,
			}
		},
		data: function () {
			return {
				configdata: {}
			}
		},
		methods: {
			post: function () {
				this.$emit('post-set', {type: 'redset', config: this.configdata})
			}
		},
	};
</script>
<style scoped></style>