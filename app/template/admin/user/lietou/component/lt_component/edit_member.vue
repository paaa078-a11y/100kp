<template>
    <div class="uploadTable">
        <table class="tableVue">
            <thead>
            <tr align="left">
                <th width="120">名称</th>
                <th width=" ">状态</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div class="TableTite">用户名</div>
                </td>
                <td>
                    <div class="TableInpt">
                        <el-input placeholder="0" v-model="member.username"></el-input>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="TableTite">登录密码</div>
                </td>
                <td>
                    <div class="TableInpt">
                        <el-input placeholder="请输入密码" v-model="password"></el-input>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="TableTite">状态</div>
                </td>
                <td>
                    <div class="TableInpt">
                        <el-radio-group v-model="member.status">
                            <el-radio label="1">正常</el-radio>
                            <el-radio label="2">锁定</el-radio>
                        </el-radio-group>
                    </div>
                </td>
            </tr>
            <tr v-if="member.status == '2'">
                <td>
                    <div class="TableTite">锁定说明：</div>
                </td>
                <td>
                    <div class="TableInpt">
                        <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="member.lock_info">
                        </el-input>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="saveMember" :loading="saveLoading">提交</el-button>
        </div>
    </div>

</template>
<script>
module.exports = {
    props:{
        member:Object,
    },
    data: function () {
        return {
            options:[],
            uri: "m=user&c=",
            password:'',
            saveLoading:false
        }
    },
    methods: {
        saveMember:function(){
            let _this = this;
            let url  =this.uri +"admin_lt_member&a=saveUser"
            let sendData = this.member;
            if (this.password){
                sendData.password = this.password;
            }
            _this.saveLoading = true;
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg);
                }else {
                    message.error(res.msg);
                }
            }).finally(function() {
                setTimeout(function() {
                    _this.saveLoading = false;
                }, 2000);
            });
        },
    },
};
</script>