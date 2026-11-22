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
							<div class="TableTite">活动名称</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input placeholder="请输入内容" v-model="configdata.sy_wxredpack_actname4">

								</el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span> </span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">是否开启</div>
						</td>
						<td>
							<div class="TableButn">
								<el-switch v-model="configdata.sy_wxredpack_isopen4"  
									active-value="1" inactive-value="0"></el-switch>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>开关打开时的带背景色 </span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">场景</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-select v-model="configdata.sy_wxredpack_pro4" size="small" slot="prepend"
									placeholder="搜索类型">
									<el-option v-for="v in configdata.redArr" :key="v.val" :label="v.name"
										:value="v.val"></el-option>
								</el-select>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<div>发放红包使用场景，默认场景为“其他”，如红包金额大于200或者小于1元时，请到<el-link
										href="https://pay.weixin.qq.com" target="_blank"
										type="primary">微信支付平台</el-link>中申请修改场景红包限额(申请有效期为1年)</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">随机红包金额</div>
						</td>
						<td>
							<div class="jianlidian">
								<div class="TableInpt">
									<el-input placeholder="请输入数字" @input="formatMoney('sy_wxredpack_money4')" v-model="configdata.sy_wxredpack_money4"> <span
											slot="suffix" class="slotspan">元</span>
									</el-input>
								</div>
								<div class="hxian"></div>
								<div class="TableInpt">
									<el-input placeholder="请输入数字" @input="formatMoney('sy_wxredpack_money_max4')" v-model="configdata.sy_wxredpack_money_max4"> <span
											slot="suffix" class="slotspan">元</span>
									</el-input>
								</div>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>“其他”场景可设置红包金额1-200元；非“其他”场景，金额最低0.3元，否则将发放失败</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">红包祝福语</div>
						</td>
						<td>
							<div class="TableInpt">
								<el-input placeholder="请输入内容" v-model="configdata.sy_wxredpack_wishing4" :maxlength="255">

								</el-input>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>如：感谢您关注XX人才网</span>
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

	mounted() {

	},
	methods: {
		formatMoney:function(key){
			formatMoney(this.configdata[key],this.configdata,key,this);
		},
		post: function () {
			this.$emit('post-set', { type: 4, config: this.configdata })
		}
	},
};
</script>
<style scoped></style>