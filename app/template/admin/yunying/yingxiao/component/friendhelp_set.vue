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
                        <td>
                            <div class="TableTite">启用助力</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-switch v-model="config.sy_help_open"
                                           active-value="1" inactive-value="2">
                                </el-switch>
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
                            <div class="TableTite">任务时间限制</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="config.sy_help_time" @input="inputIntNumber($event, 'config', 'sy_help_time')" placeholder="">
                                    <span slot="suffix" class="slotspan">小时</span>
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>按小时计算，例1，为1小时</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">助力权益</div>
                        </td>
                        <td>
                            <div class="TableInpDblue">
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_breakjobnum" @input="inputIntNumber($event, 'config', 'sy_help_breakjobnum')" placeholder="">
                                        <template slot="prepend">刷新职位</template>
                                        <template slot="append">次</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_breakjobnum_zl" @input="inputIntNumber($event, 'config', 'sy_help_breakjobnum_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_jobnum" @input="inputIntNumber($event, 'config', 'sy_help_jobnum')" placeholder="">
                                        <template slot="prepend">发布职位</template>
                                        <template slot="append">份</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_jobnum_zl" @input="inputIntNumber($event, 'config', 'sy_help_jobnum_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_urgentnum" @input="inputIntNumber($event, 'config', 'sy_help_urgentnum')" placeholder="">
                                        <template slot="prepend">紧急职位</template>
                                        <template slot="append">天</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_urgentnum_zl" @input="inputIntNumber($event, 'config', 'sy_help_urgentnum_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_recnum" @input="inputIntNumber($event, 'config', 'sy_help_recnum')" placeholder="">
                                        <template slot="prepend">推荐职位</template>
                                        <template slot="append">天</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_recnum_zl" @input="inputIntNumber($event, 'config', 'sy_help_recnum_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_topnum" @input="inputIntNumber($event, 'config', 'sy_help_topnum')" placeholder="">
                                        <template slot="prepend">置顶职位</template>
                                        <template slot="append">天</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_topnum_zl" @input="inputIntNumber($event, 'config', 'sy_help_topnum_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_inviteresume" @input="inputIntNumber($event, 'config', 'sy_help_inviteresume')" placeholder="">
                                        <template slot="prepend">邀请面试</template>
                                        <template slot="append">份</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_inviteresume_zl" @input="inputIntNumber($event, 'config', 'sy_help_inviteresume_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_downresume" @input="inputIntNumber($event, 'config', 'sy_help_downresume')" placeholder="">
                                        <template slot="prepend">下载简历</template>
                                        <template slot="append">份</template>
                                    </el-input>
                                </div>
                                <div class="TableInpt">
                                    <el-input v-model="config.sy_help_downresume_zl" @input="inputIntNumber($event, 'config', 'sy_help_downresume_zl')" placeholder="">
                                        <template slot="prepend">达标助力</template>
                                        <template slot="append">人</template>
                                    </el-input>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>配置说明：助力权益按照梯次分配，每次好友助力达到设置的数量可以获取对应的权益</span>
                                <span>举例说明：</span>
                                <span>刷新职位1次，达标助力10人 : 代表 当助力人数到达10人时，企业可以获得一次职位刷新的权限.</span>
                                <span>下载简历1份，达标助力50人：当好友继续助力，人数达到50人，在原来已有的职位刷新1次权益基础上又新获得下载简历1份的权益。</span>
                                <span>以此类推，可以多项权益设置相同助力人数，达到助力指标则同时获得多项权益，任务结束按照助力总人数进行统计发放相关权益。</span>
                                <span>Ps：权益、达标助力参数设置为0或空，则该项权益不参与好友助力任务。</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="submit" :disabled="saveLoading">提交</el-button>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {},
    data: function () {
        return {
            config: {},

            saveLoading: false,
        }
    },

    created() {
        this.getData();
    },
    methods: {
        init() {
            this.getData();
        },

        getData() {
            let that = this;
            httpPost('m=yunying&c=yingxiao_friendhelp', {}).then(function (response) {
                let res = response.data,
                    data = res.data;

                that.config = data.config;
            })
        },

        inputIntNumber(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9]/g,'');
            // console.log(this[form])
            // this.$set(this[form], key, val.replace(/[^0-9]/g,''))
        },

        submit() {
            let that = this,
                params = that.config;

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=yunying&c=yingxiao_friendhelp&a=save', params).then(function (response) {
                let res = response.data;

                that.saveLoading = false;
                if (res.error > 0) {
                    message.error(res.msg);
                } else {
                    message.success(res.msg);
                }
            })
        },
    },
};
</script>
<style scoped>
.TableInpDblue {
    overflow: hidden;
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
}

.TableInpDblue .TableInpt {
    padding: 4px 6px;
    width: 250px;
}

.TableInpDblue .el-input-group__prepend {
    background: #f5f7fa;
}
.TableShuom span{
    overflow: hidden;
    display: block;
    padding: 4px 0;
}
</style>