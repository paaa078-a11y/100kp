<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="180">名称</th>
                        <th width="560">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">
                            <div class="TableTiteTwos">发送对象</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">用户类型</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-radio-group v-model="formData.utype" @change="utypeChange">
                                    <el-radio label="allbind">全部</el-radio>
                                    <el-radio label="1">个人用户</el-radio>
                                    <el-radio label="2">企业用户</el-radio>
                                    <el-radio label="3">猎头用户</el-radio>
                                    <el-radio label="4">培训用户</el-radio>
                                    <el-radio label="5">自定义用户</el-radio>
                                </el-radio-group>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="formData.utype == '1'">
                        <td>
                            <div class="TableTite">通知对象</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-radio-group v-model="formData.utz">
                                    <el-radio label="">不限</el-radio>
                                    <el-radio label="expect">未创建简历</el-radio>
                                </el-radio-group>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="formData.utype == '2'">
                        <td>
                            <div class="TableTite">通知对象</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-radio-group v-model="formData.ctz">
                                    <el-radio label="">不限</el-radio>
                                    <el-radio label="endvip">会员7天内到期</el-radio>
                                    <el-radio label="jobadd">未发布职位</el-radio>
                                </el-radio-group>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="formData.utype == '5'">
                        <td>
                            <div class="TableTite">账户</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="formData.userarr" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>多个用户请用,(半角)隔开</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">用户活跃度</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-radio-group v-model="formData.activ">
                                    <el-radio label="all">全部</el-radio>
                                    <el-radio label="7">一周</el-radio>
                                    <el-radio label="30">一月</el-radio>
                                    <el-radio label="90">三月</el-radio>
                                    <el-radio label="190">半年</el-radio>
                                    <el-radio label="365">一年以上</el-radio>
                                </el-radio-group>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div class="TableTiteTwos">自定义发送内容</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">微信消息模板ID</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="formData.wxtplid" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>选择合适的消息模板，填写对应模板ID 可参照文档</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">消息标题</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="formData.first" placeholder=""></el-input>
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
                            <div class="TableTite">正文标题</div>
                        </td>
                        <td>
                            <div class="TableInpDblue">
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyword1" placeholder="字段名">
                                        <template slot="prepend">标题1：</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyworddata1" placeholder="请输入内容"></el-input>
                                </div>
                            </div>
                            <div class="TableInpDblue">
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyword2" placeholder="字段名">
                                        <template slot="prepend">标题2：</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyworddata2" placeholder="请输入内容"></el-input>
                                </div>
                            </div>
                            <div class="TableInpDblue">
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyword3" placeholder="字段名">
                                        <template slot="prepend">标题3：</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyworddata3" placeholder="请输入内容"></el-input>
                                </div>
                            </div>
                            <div class="TableInpDblue">
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyword4" placeholder="字段名">
                                        <template slot="prepend">标题4：</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyworddata4" placeholder="请输入内容"></el-input>
                                </div>
                            </div>
                            <div class="TableInpDblue">
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyword5" placeholder="字段名">
                                        <template slot="prepend">标题5：</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="formData.keyworddata5" placeholder="请输入内容"></el-input>
                                </div>
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
                            <div class="TableTite">备注</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="formData.remark" placeholder=""></el-input>
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
                            <div class="TableTite">链接</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="formData.url" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>链接地址</span>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="send">发送</el-button>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        list: Object
    },
    data: function () {
        return {
            formData: {}
        }
    },

    mounted() {

    },
    methods: {
        init() {
            this.resetData(); // 初始化数据
        },

        resetData() {
            this.formData = {
                utype: 'allbind',
                utz: '',
                ctz: '',
                activ: 'all',
                userarr: '',
                wxtplid: '',
                first: '',
                keyword1: '',
                keyworddata1: '',
                keyword2: '',
                keyworddata2: '',
                keyword3: '',
                keyworddata3: '',
                keyword4: '',
                keyworddata4: '',
                keyword5: '',
                keyworddata5: '',
                remark: '',
                url: ''
            }
        },

        utypeChange(val) {
            if (val != '5') {
                this.formData.userarr = '';
            }
        },

        send() {
            let that = this,
                formData = this.formData;

            if (formData.utype == 5 && formData.userarr == '') {
                message.error('请输入用户名');
                return false;
            }
            if (formData.wxtplid == '') {
                message.error('请输入微信模板消息ID');
                return false;
            }
            if (formData.first == '') {
                message.error('请输入消息标题');
                return false;
            }
            if (formData.keyword1 == '' || formData.keyworddata1 == '') {
                message.error('请至少填写一栏正文内容');
                return false;
            }

            that.sendWxMsg(formData, 1, '正在发送，请稍候。。。', 3);
        },

        sendWxMsg(params, page, msg, status) {
            let that = this;
            if (status == "3") {
                if (!this.sendLoading) {
                    this.sendLoading = this.$loading({
                        lock: true,
                        text: msg,
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.6)'
                    })
                }

                params.page = page;
                httpPost('m=yunying&c=yingxiao_tuiguang&a=wxtpl', params, {hideloading: true}).then(function (response) {
                    let res = response.data;

                    if (res.error == 3) {
                        page++;
                        that.sendWxMsg(params, page, res.msg, res.error);
                    } else if (res.error > 0) {
                        that.sendLoading.close();
                        message.error(res.msg, function () {
                            that.sendLoading = null;
                        });
                    } else {
                        that.sendLoading.close();
                        message.confirm(res.msg, function () {
                            that.resetData();
                        }, '', '', '', false);
                    }
                })
            } else {
                that.sendLoading.close();
                message.confirm(msg, function () {
                    that.resetData();
                }, '', '', '', false);
            }
        },
    },
};
</script>
<style scoped>
.TableInpDblue {
    overflow: hidden;
    position: relative;
    padding: 3px 0;
}

.TableInpDblue .TableInpt {
    padding: 4px 0;
}
.TableInpDblue .el-input-group__prepend {
    background: #f7f7f7;
}
</style>