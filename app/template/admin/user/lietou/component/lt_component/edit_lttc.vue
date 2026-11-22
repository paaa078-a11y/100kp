<template>
    <div class="uploadTable">
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
                        <div class="TableTite">会员级别</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-select v-model="statis.rating" placeholder="请选择" @change="getratingFun">
                                <el-option key="0" label="请选择" value="0"></el-option>
                                <el-option v-for="item in ratingarr" :key="item.id.toString()" :label="item.name"
                                           :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </td>

                </tr>
                <tr>
                    <td>
                        <div class="TableTite">发布职位数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="0" v-model="statis.lt_job_num" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">个 </el-input>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">刷新职位数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="0" v-model="statis.lt_breakjob_num"  onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"  >个 </el-input>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">下载简历数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="0" v-model="statis.lt_down_resume" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">个 </el-input>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">直聊对象数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="0" v-model="statis.chat_num" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">个 </el-input>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">发布职位数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-date-picker v-model="statis.vipetime" type="date" value-format="yyyy-MM-dd" placeholder="选择日期">
                            </el-date-picker>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="saveRating" :loading="saveLoading">提交</el-button>
        </div>
    </div>

</template>
<script>
module.exports = {
    props:{
        ratingarr:Array,
        uid:String,
    },
    data: function () {
        return {
            options:[],
            uri: "m=user&c=",
            password:'',
            statis:{},
            saveLoading: false
        }
    },
    watch: {
        uid:{
            handler(val) {
                this.ltInfo();
            },
            deep: true,
            immediate: true
        }
    },
    methods: {
        ltInfo:function () {
            let _this = this;
            let url  =this.uri +"admin_lt_member&a=ltInfo"
            httpPost(url, {id:_this.uid}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.statis = res.data;
                    console.log(_this.statis);
                }else {
                    message.error(res.msg);
                }
            })
        },
        saveRating:function(){
            let _this = this;
            let url  =this.uri +"admin_lt_member&a=saveRating"
            let sendData = this.statis;
            sendData.uid = this.uid;
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
        getratingFun:function (e) {
            let _this = this;
            let url  =this.uri +"admin_lt_member&a=getrating"
            let id = e;
            if (id == '0'){
                _this.statis.lt_job_num = '0';
                _this.statis.lt_breakjob_num = '0';
                _this.statis.lt_down_resume = '0';
                _this.statis.chat_num = '0';
                _this.statis.vipetime = '';
                _this.statis.vip_etime = '';
                return false;
            }
            httpPost(url, {id : id}).then(function (response) {
                let res = response.data;
                _this.statis.lt_job_num = res.lt_job_num;
                _this.statis.lt_breakjob_num = res.lt_breakjob_num;
                _this.statis.lt_down_resume = res.lt_down_resume;
                _this.statis.chat_num = res.chat_num;
                _this.statis.vipetime = res.vipetime;
                _this.statis.vip_etime = res.vip_etime;
            })
        }
    },
};
</script>