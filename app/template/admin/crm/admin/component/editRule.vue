<template>
    <div class="tableDome" style="padding: 0 20px; width: 100%;">
        <div class="moduleTable">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="100">名称</th>
                    <th width="600">内容</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">会员类型</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-checkbox-group v-model="ruleInfo.ratingIdArr">
                                <el-checkbox v-for="item in ratingArr" :label="item.value" :key="item.label">
                                    {{item.label}}
                                </el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">数据周期</div>
                    </td>
                    <td style="display: flex;flex-flow: column;">
                        <div class="guizheshoes">
                            <el-input v-model="ruleInfo.follow_day" type="number" min="0">
                                <template slot="prepend">跟进提醒</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-alert type="success" title="超过X天未跟进，自动提醒跟进" :closable="false" class="alert_el"></el-alert>
                        </div>
                        <div class="guizheshoes">
                            <el-input v-model="ruleInfo.release_day" type="number" min="0">
                                <template slot="prepend">释放周期</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-alert type="warning" title="超过X天未跟进，自动释放至资源池（释放周期 大于 跟进提醒）" :closable="false" class="alert_el"></el-alert>
                        </div>
                        <div class="guizheshoes">
                            <el-input v-model="ruleInfo.deal_day" type="number" min="0">
                                <template slot="prepend">成交限定</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-alert type="success" title="已领取为起始时间，当前会员等级在规定周期内未发生改变，自动释放至资源池" :closable="false" class="alert_el"></el-alert>
                        </div>
                        <div class="guizheshoes">
                            <el-input v-model="ruleInfo.claim_day" type="number" min="0">
                                <template slot="prepend">领取限定</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-alert type="info" title="资源释放后，上一任CRM在规定周期内禁止重复领取" :closable="false" class="alert_el"></el-alert>
                        </div>
                        <div class="guizheshoes">
                            <el-input v-model="ruleInfo.bh_day" type="number" min="0">
                                <template slot="prepend">保护周期</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-alert type="error" title="满足释放规则的未续费客户，X天内不会释放，超过X天，释放到公海" :closable="false" class="alert_el"></el-alert>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="saveRule" :disabled="btDisabled">提交</el-button>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            info: {
                type: Object,
                default: ''
            },
            rating_list: {
                type: Array,
                default: []
            }
        },
        watch: {
            info: {
                handler(val) {
                    this.ruleInfo = val;
                },
                immediate: true,
                deep: true,
            },
            rating_list: {
                handler(val) {
                    this.ratingList = val;
                },
                immediate: true,
                deep: true,
            }
        },
        data: function () {
            return {
                ruleInfo: {
                    ratingIdArr: [],
                    ratingList: []
                },
                ratingArr: [],
                ratingList: [],
                btDisabled: false
            }
        },
        methods: {
            setRatingArr: function () {
                let that = this;
                that.ratingArr = [];
                if (that.ratingList != null) {
                    that.ratingList.forEach(function (item) {
                        that.ruleInfo.ratingList.push(item);
                    })
                }
                if (that.ruleInfo.ratingList.length > 0) {
                    that.ruleInfo.ratingList.forEach(function (item) {
                        var obj = {value: item.id, label: item.name};
                        if (JSON.stringify(that.ratingArr).indexOf(JSON.stringify(obj)) == -1) {
                            that.ratingArr.push(obj);
                        }
                    });
                }
            },
            saveRule: function () {
                let that = this;
                if (that.ruleInfo.ratingIdArr.length == 0){
                    message.error('请选择会员类型');
                    return false;
                }
                if (parseInt(that.ruleInfo.release_day) > 0 && parseInt(that.ruleInfo.follow_day) > parseInt(that.ruleInfo.release_day)){

                    message.error('跟进提醒时间要在释放周期之前哦~');
                    return false;
                }
                let params = deepClone(that.ruleInfo);
                delete params.rating;
                delete params.ratingList;
                that.btDisabled = true;
                httpPost('m=crm&c=config&a=saveRule', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {

                            that.$emit("child-event");
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).finally(function(){
                    that.btDisabled = false
                });
            }
        }
    };
</script>
<style>
    .moduleTable {
        max-height: calc(100% - 80px)
    }
    .el-input__inner {
        padding: 0 0 0 10px;
    }
    .guizheshoes .el-input-group{
        width: 190px;
    }
    .guizheshoes .el-input-group__prepend{
        padding: 0 12px;
    }
    .alert_el{
        margin-left: 10px;
    }
    .guizheshoes{
        overflow: hidden;
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
        padding: 5px 0;
    }
    .guizheshoes .el-alert{
        width: calc(100% - 200px);
    }
</style>