<!---->
<template>
    <div class="drawerModlue">
        <div class="drawerModInfo">
            <div class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">客户名称：</span>
                <div class="hydialModFrom">
                    <el-select v-model="com_uid" :disabled="comdisabled" @change="comSelect" filterable remote reserve-keyword placeholder="输入企业名称" :remote-method="company_search" :loading="company_searchloading">
                        <el-option v-for="item in company_search_list" :key="item.uid" :label="item.name" :value="item.uid">
                            <span style="float: left; color: #333; font-size: 14px;font-weight:bold;">{{ item.name }}</span>
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">支付方式：</span>
                <div class="hydialModFrom">
                    <el-select v-model="order_type" placeholder="请选择">
                        <el-option v-for="(item, key) in canpay" :key="key" :label="item" :value="key"></el-option>
                    </el-select>
                </div>
            </div>
            <div class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">订单类型：</span>
                <div class="hydialModFrom">
                    <el-select v-model="orderType" @change="orderTypeChange" style="width: calc(100% - 30px);" placeholder="请选择">
                        <el-option v-for="(item, key) in orderTypeArr" :key="key" :label="item" :value="key"></el-option>
                    </el-select>
                    <el-popover placement="right" title="标题" width="360" trigger="hover">
                        <div style='overflow: hidden;'>
                            <div style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>录入</div>
                            <div>
                                <div style='overflow: hidden;padding-left:10px;line-height:32px;font-size:14px;display: flex;align-items: center; margin:5px 0;'>
                                    <p style='color:#666;'></p>
                                    <span style='color:#666; background: rgba(120, 186, 50, 0.2); color:#78BA32;border-radius: 5px;display: block;margin-left:15px;padding:0 10px;'></span>
                                </div>
                                <div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                    当业务员录入“会员充值“类型的订单，并且选择择开通业务的模式，管理员在审批订单时，会充值会员到企业账号；其他情况的录入，则不会涉及业务逻辑处理
                                </div>
                            </div>
                        </div>
                        <i slot="reference" class="el-icon-info"></i>
                    </el-popover>
                </div>
            </div>
            <div v-show="orderType == '1'" class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">会员套餐：</span>
                <div class="hydialModFrom">
                    <el-select @change="ratChange" v-model="rid" placeholder="请选择">
                        <el-option v-for="item in ratinglist" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                </div>
            </div>
            <div v-show="orderType == '1'" class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">业务模式：</span>
                <div class="hydialModFrom">
                    <el-radio v-model="deal_model" label="1">开通</el-radio>
                    <el-radio v-model="deal_model" label="2">不开通</el-radio>
                </div>
            </div>
            <div class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">付款金额：</span>
                <div class="hydialModFrom deal_price">
                    <el-input v-model="order_price" type="number" min="0"></el-input>
                </div>
            </div>

            <div class="hydialog_item" style="display: flex; align-items: center;">
                <span class="item_span">合同照片：</span>
                <div class="hydialModFrom">
                    <el-upload class="list_pic" :action="uploadAction" :accept="pic_accept" :multiple="true" :on-remove="handleRemove" :on-change="handleChange" list-type="picture" :auto-upload="false">
                        <el-button size="small" type="primary">点击上传</el-button>
                    </el-upload>
                </div>
            </div>
            <div class="hydialog_item">
                <span class="item_span">备注信息：</span>
                <div class="hydialModFrom">
                    <el-input type="textarea" v-model="order_remark" :rows="4"></el-input>
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
        props: {
            uid_v: {
                type: String,
                default: ''
            },
            name_v: {
                type: String,
                default: ''
            },
        },
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                canpay: [],
                company_searchloading: false,
                company_search_list: [],
                comdisabled: false,
                ratinglist: [],
                orderTypeArr: [],

                crm_uid: '',
                com_uid: '',
                deal_model: '1',
                order_type: '',
                orderType: '',
                rid: '',
                order_price: '',
                order_remark: '',
                picFiles: [],
                saveloading: false,
                uploadAction: baseUrl + 'm=common&c=common_upload'
            }
        },

        mounted() {
            if (this.uid_v && this.name_v) {
                this.company_search_list = [{
                    uid: this.uid_v,
                    name: this.name_v
                }];
                this.com_uid = this.uid_v;
                this.comdisabled = true;
            }
            this.getData();
        },
        methods: {
            async getData() {
                let that = this;

                this.addloading = true;

                httpPost('m=crm&c=crm_index&a=index', {}).then((result) => {

                    this.addloading = false;

                    var res = result.data;
                    if (res.error == 0) {
                        this.canpay = res.data.canpay
                        this.ratinglist = res.data.ratinglist
                        this.orderTypeArr = res.data.orderTypeArr
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async company_search(query) {
                let that = this;
                var company_search_list = [];
                that.company_searchloading = true;
                if (query == '') {
                    return false;
                }
                var params = {
                    keyword: query
                };
                httpPost('m=crm&c=crm_index&a=searchcom', params).then((result) => {
                    that.company_searchloading = false;
                    var res = result.data;

                    if (res.error == 0) {
                        company_search_list = res.data;
                    }
                    that.company_search_list = company_search_list;
                    if (that.company_search_list.length == 0) {
                        that.com_id = '';
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            comSelect: function (e) {
                let that = this;

                var company_search_list = deepClone(this.company_search_list);

                for (let i in company_search_list) {
                    if (company_search_list[i].uid == e) {
                        this.crm_uid = company_search_list[i].crm_uid;
                        break;
                    }
                }
            },
            ratChange: function () {

                let that = this;

                var params = {
                    id: this.rid
                };

                httpPost('m=crm&c=crm_index&a=orderprice', params).then((result) => {

                    var res = result.data;

                    if (res.error == 0) {
                        this.order_price = res.data.service_price;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            orderTypeChange: function () {
                if (this.orderType != '1') {
                    this.order_price = '';
                }
            },

            handleRemove: function (file, fileList) {
                var that = this;
                var picFiles = [];
                for (let i in fileList) {
                    picFiles.push(fileList[i].raw)
                }
                this.picFiles = picFiles;
            },
            handleChange(file, fileList) {
                var that = this;
                var picFiles = [];
                for (let i in fileList) {
                    picFiles.push(fileList[i].raw)
                }
                this.picFiles = picFiles;
            },
            async saveinfo() {
                var that = this;
                if (this.com_uid == '') {
                    message.warning('请填写客户名称！');
                    return;
                }
                if (this.order_type == '') {
                    message.warning('请选择支付方式！');
                    return;
                }
                if (this.orderType == '') {
                    message.warning('请选择订单类型！');
                    return;
                } else if (this.orderType == 1) {
                    if (this.rid == '') {
                        message.warning('请选择会员套餐！');
                        return;
                    }
                }
                var params = new FormData();
                params.append('uid', that.com_uid);
                params.append('crm_uid', that.crm_uid);
                params.append('order_price', that.order_price);
                params.append('order_type', that.order_type);
                params.append('type', that.orderType);
                params.append('rating', that.rid);
                params.append('deal_model', that.deal_model);
                params.append('order_remark', that.order_remark);
                if (that.picFiles.length > 0) {
                    that.picFiles.forEach(function (value, index) {
                        params.append('picFiles[]', value);
                    })
                }
                this.saveloading = true;
                httpPost('m=crm&c=crm_index&a=addDeal', params).then((result) => {
                    this.saveloading = false;
                    var res = result.data;
                    if (res.error == 9) {
                        message.success(res.msg, () => {
                            this.$emit("close-update", {id: that.com_uid});
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
	.drawerModInfo::-webkit-scrollbar{display:none}
	.drawerModlue{overflow:hidden;position:relative;width:100%;height:100%;padding:0}
	.drawerModInfo{overflow-y:auto;height:calc(100% - 80px);width:100%;padding:0}
	.drawerModInfo .hydialog_item{overflow:hidden;position:relative;padding:6px 0;width:100%;display:flex;align-items:center;justify-content:space-between}
	.drawerModInfo .hydialModFrom{overflow:hidden;position:relative;width:calc(100% - 75px)}
	.drawerModInfo .hydialModFrom .el-select{width:100%}
    .deal_price .el-input__inner{padding: 0 0 0 10px;}

</style>