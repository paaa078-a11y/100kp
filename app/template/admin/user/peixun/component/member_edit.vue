<template>
    <div class="peixunxiugaid">
        <div class="setDomeAll">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="基本资料" name="basic">
                    <basic :uid="info.uid" :info="info" :member="memberInfo" ref="basic" @child-event="closeEdit"></basic>
                </el-tab-pane>
                <el-tab-pane label="账户信息" name="account">
                    <account :uid="memberInfo.uid" :member="memberInfo" ref="account" @child-event="closeEdit"></account>
                </el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>
<!-- script -->
<script>
    module.exports = {
        props: ['uid'],
        data: function () {
            return {
                activeName: 'basic',
                info: {},
                memberInfo: {},
            }
        },
        components: {
            'basic': httpVueLoader('./member_edit_basic.vue'),
            'account': httpVueLoader('./member_edit_account.vue'),
        },
        created() {
            this.getInfo();
        },
        methods: {
            handleClick(tab, event) {
                // this.$refs[tab.name].init();
            },

            getInfo() {
                let that = this;

                httpPost('m=user&c=peixun_member&a=edit', {uid: that.uid}).then(function (response) {
                    let res = response.data,
                        data = res.data;

                    if (res.error > 0) {
                        message.error(res.msg);
                        return false;
                    }

                    that.info = data.info;
                    that.memberInfo = data.memberInfo;
                })
            },

            closeEdit() {
                this.$emit("child-event");
            },
        },
        watch: {
            uid: function (val, oldVal) {
                this.activeName = 'basic';
                this.getInfo();
            },
        }
    };
</script>

<style scoped>
.peixunxiugaid{
    overflow: hidden;
    position: relative;
    padding: 0 20px;
    height: 100%;
}
.setDomeAll .el-tabs__content{
    padding: 0;
    height: calc(100% - 54px);
    width: 100%;
}
.peixunData{
    padding-top: 10px;
}
</style>