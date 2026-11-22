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
							<div class="TableTite">企业会员模式</div>
						</td>
						<td>
							<div class="TableButn">
								<el-radio v-model="radio" label="1">时间模式</el-radio>
								<el-radio v-model="radio" label="2">套餐模式</el-radio>
								<el-radio v-model="radio" label="2">同时开启</el-radio>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span> 时间模式在设置的时间内，不受套餐数限制，但是有每日上限操作，套餐模式在设置的时间内，受套餐数限制，用完即止。</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">套餐消费模式</div>
						</td>
						<td>
							<div class="TableButn">
								<el-radio v-model="radio" label="1">抵扣消费</el-radio>
								<el-radio v-model="radio" label="2">金额消费</el-radio>
								<el-radio v-model="radio" label="2">积分消费</el-radio>
								<el-radio v-model="radio" label="2">套餐消费</el-radio>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<div class="tc_checktip">
									抵扣消费：账户积分抵扣部分或者全部金额操作！在会员套餐使用完的情况下，会员可以通过积分抵扣金额，或者购买会员的方式进行消费的模式 </div>
								<div class="tc_checktip">金额消费：在会员套餐使用完的情况下，会员通过消费金额，或者购买会员的方式进行套餐相关的操作 </div>
								<div class="tc_checktip">积分消费：在会员套餐使用完的情况下，会员通过购买消费账户积分，或者购买会员的方式进行套餐相关的操作 </div>
								<div class="tc_checktip">套餐消费：在会员套餐使用完的情况下，会员仅可购买会员才可以继续进行套餐内操作 </div>



							</div>
						</td>
					</tr>

					<tr>
						<td>
							<div class="TableTite">可单独购买</div>
						</td>
						<td>
							<div class="tc_checkbox">
								<el-checkbox label="上架职位"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位置顶"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位推荐"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位紧急"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位刷新"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="下载简历"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="邀请面试"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="招聘会报名"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="创建子账号"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="视频面试"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="网络招聘会报名"></el-checkbox>
							</div>


							<div class="tc_checkbox">
								<el-checkbox label="直聊"></el-checkbox>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								已选中的项目可以在套餐外单独使用金额、积分等购买，未选中则必须购买相应的套餐或增值包
							</div>
						</td>
					</tr>



					<tr>
						<td>
							<div class="TableTite">强制金额消费项目</div>
						</td>
						<td>
							<div class="tc_checkbox">
								<el-checkbox label="会员套餐"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="增值服务"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="上架职位"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位推荐"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位紧急"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="职位刷新"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="下载简历"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="邀请面试"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="招聘会报名"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="创建子账号"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="视频面试"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="网络招聘会报名"></el-checkbox>
							</div>

						</td>
						<td>
							<div class="TableShuom"> 已选中的项目仅可使用金额购买</div>
						</td>
					</tr>

					<tr>
						<td>
							<div class="TableTite">职位推广取消</div>
						</td>
						<td>
							<div class="TableButn">
								<el-radio v-model="radio" label="1">开启</el-radio>
								<el-radio v-model="radio" label="2">关闭</el-radio>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>开启职位推广取消功能：企业会员主动关闭职位推广，剩余推广套餐，返还到企业账号（例如：设置置顶10天，推广了5天，关闭推广，剩余5天返回到企业账户</span>
							</div>
						</td>
					</tr>


					<tr>
						<td>
							<div class="TableTite">购买会员套餐累加</div>
						</td>
						<td>
							<div class="tc_checkbox">
								<el-checkbox label="金牌会员"></el-checkbox>
							</div>
							<div class="tc_checkbox">
								<el-checkbox label="年会员"></el-checkbox>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>当前会员套餐未到期，购买新会员，选中的套餐数据按累加计算（套餐数量和套餐时间）。职位上架数量不会累加</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="TableTite">会员职位免审核</div>
						</td>
						<td>
							<div class="TableButn">
								<div class="tc_checkbox">
									<el-checkbox label="金牌会员"></el-checkbox>
								</div>
								<div class="tc_checkbox">
									<el-checkbox label="年会员"></el-checkbox>
								</div>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>勾选后，该会员类型职位免审核
								</span>
							</div>
						</td>
					</tr>


					<tr>
						<td>
							<div class="TableTite">企业注册默认等级</div>
						</td>
						<td>
							<div class="TableSelect">
								<el-select v-model="value" placeholder="请选择">
									<el-option v-for="item in options" :key="item.value" :label="item.label"
										:value="item.value">
									</el-option>
						 	</el-select>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>勾选后，该会员类型职位免审核
								</span>
							</div>
						</td>
					</tr>
                   <tr>
						<td>
							<div class="TableTite">会员到期后默认为</div>
						</td>
						<td>
							<div class="TableSelect">
								<el-select v-model="value" placeholder="请选择">
									<el-option v-for="item in options" :key="item.value" :label="item.label"
										:value="item.value">
									</el-option>
								</el-select>
							</div>
						</td>
						<td>
							<div class="TableShuom">
								<span>如设置的不是过期会员，用户现有会员等级到期后按选择的等级设置套餐数量、到期时间
								</span>
							</div>
						</td>
					</tr>

				</tbody>
			</table>
		</div>
		<div class="setBasicButn" style="border: none; height: 80px;">
			<el-button type="primary" size="medium">提交</el-button>
		</div>
	</div>




</template>

<script>
	module.exports = {
		data: function() {
			return {
				input: '',
				value1: true,
				textarea: ''
			}
		},

		mounted() {

		},
		methods: {

		},
	};
</script>
<style scoped></style>
