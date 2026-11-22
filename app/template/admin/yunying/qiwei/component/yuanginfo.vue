<template>
    <el-drawer title="员工详情" :visible.sync="drawerinfo" :direction="direction" append-to-body size="80%">
        <div class="moduleSeachs" style="flex-wrap: wrap;">
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-date-picker v-model="time" size="small" type="daterange" range-separator="至" start-placeholder="开始日期"
                    end-placeholder="结束日期" value-format="yyyy-MM-dd" style="margin-right: 10px; text-align: left;" @change="getinfo">
                </el-date-picker>
                <el-button type="primary" icon="el-icon-search" size="mini" @click="getinfo">查询</el-button>
            </div>
            <div class="adminNewEarch">
                <div class="admin_statistics">
                    <span class="admin_statistics_s">聊天总人数：{{ chat_cnt }}</span>
                    <span class="admin_statistics_s">发送消息总数：{{ message_cnt }}</span>
                    <span class="admin_statistics_s">流失总数：{{ negative_feedback_cnt }}</span>
                    <span class="admin_statistics_s">发起申请数：{{ new_apply_cnt }}</span>
                    <span class="admin_statistics_s">新增客户数：{{ new_contact_cnt }}</span>
                </div>
            </div>
        </div>
        <div class="moduleDatashum">
            <div class="shiTopAllTips">
                <span><b>日期范围：</b>支持的最大查询跨度为30天，用户最多可获取最近180天内的数据</span>
                <span><b>1、发起申请数：</b>成员通过「搜索手机号」、「扫一扫」、「从微信好友中添加」、「从群聊中添加」、「添加共享、分配给我的客户」、「添加单向、双向删除好友关系的好友」、「从新的联系人推荐中添加」等渠道主动向客户发起的好友申请数量。</span>
                <span><b>2、新增客户数：</b>成员新添加的客户数量。</span>
                <span><b>3、聊天总数：</b>成员有主动发送过消息的单聊总数。</span>
                <span><b>4、发送消息数：</b>成员在单聊中发送的消息总数。</span>
                <span><b>5、已回复聊天占比：</b>客户主动发起聊天后，成员在一个自然日内有回复过消息的聊天数/客户主动发起的聊天数比例，不包括群聊。</span>
                <span><b>6、平均首次回复时长：</b>客户主动发起聊天后，成员在一个自然日内首次回复的时长间隔为首次回复时长，所有聊天的首次回复总时长/已回复的聊天总数即为平均首次回复时长，不包括群聊。</span>
                <span><b>7、流失客户数：</b>将成员删除或加入黑名单的客户数。</span>
            </div>
        </div>
        <div class="moduleDataXinx">
            <span>员工ID：{{ work_user?work_user.userid:'' }}</span>
            <span>员工手机号：{{ work_user?work_user.mobile:'' }}</span>
        </div>
        <div class="moduleElTable" style="width: 100%; padding: 0 20px;">
            <el-table :data="behavior_data" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column prop="time_n" label="日期" width="120">
                </el-table-column>
                <el-table-column prop="new_apply_cnt" label="发起申请数" width="120">
                </el-table-column>
                <el-table-column prop="new_contact_cnt" label="新增客户数" width="120">
                </el-table-column>
                <el-table-column prop="chat_cnt" label="聊天总数" width="120">
                </el-table-column>
                <el-table-column prop="message_cnt" label="发送消息数" width="120">
                </el-table-column>
                <el-table-column label="已回复聊天占比" width=" ">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <div v-if="props.row.reply_percentage">
                                {{ props.row.reply_percentage }}%
                            </div>
                            <div v-else>
                                0%
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="平均首次回复时长" width=" ">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <div v-if="props.row.avg_reply_time">
                                {{ props.row.avg_reply_time }}分钟
                            </div>
                            <div v-else>
                                0分钟
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="negative_feedback_cnt" label="流失客户数" width="120">
                </el-table-column>
            </el-table>
        </div>
    </el-drawer>
</template>

<script>
module.exports = {
    props: {

    },
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            drawerinfo: false,
            behavior_data: [],
            work_user: {},
            chat_cnt: 0,
            message_cnt: 0,
            negative_feedback_cnt: 0,
            new_apply_cnt: 0,
            new_contact_cnt: 0,
            time: '',
            direction: 'rtl',
        }
    },
    mounted() {

    },
    methods: {
        async getinfo() {
            let that = this;
            var params = { id: that.$parent.curruid }
            if (that.time) {
                params.time = that.time[0] + '~' + that.time[1]
            }
            that.loading = true;
            httpPost('m=yunying&c=workwx_list&a=info', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.behavior_data = res.data.behavior_data;
                    that.chat_cnt = res.data.chat_cnt;
                    that.message_cnt = res.data.message_cnt;
                    that.negative_feedback_cnt = res.data.negative_feedback_cnt;
                    that.new_apply_cnt = res.data.new_apply_cnt;
                    that.new_contact_cnt = res.data.new_contact_cnt;
                    that.work_user = res.data.work_user;
                    that.drawerinfo = true;
                    that.loading = false
                    if (that.behavior_data.length === 0) {
                        that.dataText = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        }
    },
};
</script>
<style scoped>
.moduleSeachs {
    padding: 0 20px;
    width: 100%;
}

</style>