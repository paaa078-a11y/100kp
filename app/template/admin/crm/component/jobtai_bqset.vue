<!---->
<template>
	<div class="moduleElHight" style="padding: 0 20px;">
        <div style="display:flex;">
            <el-alert title="筛选不同的客户标签，设置跟进周期" type="success" :closable="false" style="margin-bottom: 10px;"></el-alert>
            <el-button type="primary" size="mini" icon="el-icon-edit" style="height: 35px; margin-left: 20px;" @click="openAdd('')">配置新计划</el-button>
        </div>
        <div class="moduleElTable">
            <el-table :data="list" border style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" 
				v-loading="loading" :empty-text="emptytext">
                <el-table-column prop="crm_status_n" label="客户标签" min-width="260"></el-table-column>
                <el-table-column prop="fu_cycle" label="跟进周期（天）" width="150" align="center"></el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openAdd(scope.row)">修改</el-button>
                            <el-button size="mini" type="danger" @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- 计划添加/修改 -->
        <div class="modluDrawer">
			<el-dialog :title="formData.id ? '计划修改' : '计划增加'" width="480px" :visible.sync="dialogAdd" append-to-body>
				<div class="wxsettip_small ">客户标签：</div>
				<el-checkbox-group v-model="formData.crm_status">
									<el-checkbox v-for="item in crm_status" :label="item.id" v-if="crm_status_check.indexOf(item.id) == -1 || row.crm_status.indexOf(item.id) > -1">{{ item.name }}</el-checkbox>
								</el-checkbox-group>
				<div class="wxsettip_small ">跟进周期：</div>
				<el-input v-model="formData.fu_cycle" placeholder="请输入跟进周期" @input="inputIntNumber($event, 'formData', 'fu_cycle')">
					<template slot="append">天</template>
				</el-input>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogAdd = false">取 消</el-button>
					<el-button type="primary" @click="saveinfo" :loading="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
        </div>
    </div>
</template>

<script>

module.exports = {
	props: {
        crm_uid: {
            type: String,
            default: ''
        }
	},
	data: function () {
		return {
			emptytext: '暂无数据',
			loading: false,
			list: [],

			crm_status: [],
			crm_status_check: [],

			row: {
				crm_status: []
			}, // 弹出的那条记录

			add: false, // 判断是否还能添加 默认不能
			dialogAdd: false,
			formData: {
				crm_status: [],
				fu_cycle: ''
			},

			saveLoading: false,
		}
	},
	mounted() {
		this.getData();
	},
	methods: {
		getData() {
			let that = this;

			that.loading = true;
			that.emptytext = "数据加载中";

			httpPost('m=crm&c=crm_index&a=getBqset', {crm_uid: that.crm_uid}).then((result) => {
				that.loading = false;

				var res = result.data;
				if (res.error == 0) {
					that.crm_status = res.data.crm_status;
					that.list = res.data.list;
					that.add = res.data.add;
					that.crm_status_check = res.data.crm_status_arr;
				}
				if (that.list.length == 0) {
					that.emptytext = "暂无数据";
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		
		openAdd(row) {
			if (row == '') {
				if (this.list.length > 0 && !this.add) {
					message.error('已配置所有客户标签，如要添加新计划，请调整其他计划中的客户标签');
					return false;
				}
				this.row = {
					crm_status: []
				};
				this.formData = {
					crm_status: [],
					fu_cycle: '',
				};
			} else {
				this.row = row;
				this.formData = {
					id: row.id,
					crm_status: row.crm_status,
					fu_cycle: row.fu_cycle
				}
			}
			this.formData.crm_uid = this.crm_uid;

			this.dialogAdd = true;
		},

		inputIntNumber(val, form, key) {
			this.$data[form][key] = val.replace(/[^0-9]/g,'');
		},

		async saveinfo() {
			let that = this;
			if (this.formData.crm_status.length == 0) {
				message.warning('请选择客户标签'); return false;
			}
			if (this.formData.fu_cycle == '') {
				message.warning('请输入跟进周期'); return false;
			}
			if (this.formData.fu_cycle <= 0) {
				message.warning('跟进周期必须大于0'); return false;
			}
			
			var param = this.formData;
			this.saveLoading = true;
			httpPost('m=crm&c=crm_index&a=saveBqset', param).then((result) => {
				var res = result.data;
				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.getData();
					that.dialogAdd = false;
					message.success(res.msg, () => {
						that.saveLoading = false;
					});
				}
			}).catch(function (e) {
				console.log(e)
			})
		},

		del: function (row) {
			let that = this;
			let params = {};
			params.id = row.id;
            params.crm_uid = that.crm_uid;
			delConfirm(that, params, () => {
				httpPost('m=crm&c=crm_index&a=delBqset', params).then(function (response) {
					let res = response.data;
					if (res.error === 0) {
						message.success('删除成功');
						that.getData();
					} else {
						message.error('删除失败');
					}
				}).catch(function (error) {
					console.log(error);
				});
			}, '您确定要删除当前计划？');
		},
	},
};
</script>
<style>
.el-dialog__body {
	padding: 0 20px;
}
</style>