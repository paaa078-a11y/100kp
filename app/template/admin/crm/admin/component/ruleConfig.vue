<template>
    <div class="moduleElHight">
        <div style="display:flex;">
            <el-alert title="筛选不同会员等级，设定跟进提醒周期、释放周期、成交限定时间、领取客户时间限定以及过期未续费会员的释放保护周期" type="success" :closable="false" style="margin-bottom: 10px;"></el-alert>
            <el-button type="primary" size="mini" icon="el-icon-edit" style="height: 35px; margin-left: 20px;" @click="addRule">配置新规则</el-button>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" border style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading" :empty-text="emptytext">
                <el-table-column prop="rating_name" label="会员类型" min-width="260"></el-table-column>
                <el-table-column prop="follow_day" label="跟进提醒（天）" width="150" align="center"></el-table-column>
                <el-table-column prop="release_day" label="释放周期（天）" width="150" align="center"></el-table-column>
                <el-table-column prop="deal_day" label="成交限定（天）" width="150" align="center"></el-table-column>
                <el-table-column prop="claim_day" label="领取限定（天）" width="150" align="center"></el-table-column>
                <el-table-column prop="bh_day" label="保护周期（天）" width="150" align="center"></el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="editRule(scope)">修改</el-button>
                            <el-button size="mini" type="danger" @click="delRule(scope)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- 添加/修改 弹窗 -->
        <div class="modluDrawer">
            <el-drawer title="规则详情" :visible.sync="ruleBoxShow" :modal-append-to-body="false" size="800px">
                <edit-rule :info="ruleInfo" :rating_list="ratingList" @child-event="closeRuleBox" ref="editRule"></edit-rule>
            </el-drawer>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                tableData: [],
                add: 1,

                ruleBoxShow: false,
                ruleInfo: {},
                ratingList: []
            }
        },
        components: {
            'edit-rule': httpVueLoader('./editRule.vue'),
        },
        mounted() {
            this.getRuleConfig();
        },
        methods: {
            async getRuleConfig() {
                let that = this;
                that.loading = true;
                that.emptytext = "数据加载中";
                let res = await httpPost('m=crm&c=config&a=ruleConfig');
                if (res.data.error == 0) {

                    let data = res.data.data;
                    that.tableData = data.list;
                    that.add = data.add;
                    that.ratingList = data.ratingList;
                    if (data.expiredVip == 1){
                        that.ratingList.push({id: '999', name: '过期会员'});
                    }
                    that.loading = false;
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }
            },
            addRule: function(){
                let _this = this;
				if(this.add == '1'){
					_this.ruleInfo = {
					    ratingIdArr: [],
					    ratingList: []
					};
					this.$nextTick(function () {
					
					    this.$refs.editRule.setRatingArr();
					})
					_this.ruleBoxShow = true;
				}else{
					message.error('已配置所有会员类型，如要添加新规则，请调整其他规则中的会员类型');
				}
            },
            editRule: function (scope) {
                let _this = this;
                _this.ruleInfo = JSON.parse(JSON.stringify(scope.row));
                this.$nextTick(function () {
                    this.$refs.editRule.setRatingArr();
                })
                _this.ruleBoxShow = true;
            },
            closeRuleBox() {
                this.ruleBoxShow = false;
                this.getRuleConfig();
            },
            delRule: function (scope) {
                let _this = this;
                let params = {};
                params.id = scope.row.id;
                _this.$confirm('您确定要删除当前规则？', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    modal: true,
                    type: 'warning'
                }).then(() => {
                    httpPost('m=crm&c=config&a=delRule', params).then(function (response) {
                        let res = response.data;
                        if (res.error === 0) {
                            message.success('删除成功！');
                            _this.getRuleConfig();
                        } else {
                            message.error('删除失败！');
                        }
                    }).catch(function (error) {
                        console.log(error);
                    });
                }).catch(() => {

                });
            }
        },
    };
</script>
<style scoped></style>