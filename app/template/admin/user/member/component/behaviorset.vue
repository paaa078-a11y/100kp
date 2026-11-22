<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <el-alert title="本页面可设置行为轨迹开关，也可以根据运行需求，筛选不同模块进行记录。" type="success"
                      :closable="false">
            </el-alert>
        </div>
        <div class="moduleTable" style="height: calc(100% - 30px); max-height: initial;">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="150">名称</th>
                    <th width="500">状态</th>
                    <!-- <th>说明</th> -->
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">会员行为轨迹</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-radio-group v-model="save.sy_web_log_switch">
                                <el-radio label="1">是</el-radio>
                                <el-radio label="2">否</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <!-- <td>
                        <div class="TableShuom">
                            <span> </span>
                        </div>
                    </td> -->
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">个人行为模块</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-checkbox-group v-model="userModelArr">
                                <el-checkbox v-for="(value,key) in userModel" :key="key" :label="key">{{ value }}
                                </el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </td>
                    
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">企业行为模块</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-checkbox-group v-model="comModelArr">
                                <el-checkbox v-for="(value,key) in comModel" :key="key" :label="key">{{ value }}
                                </el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </td>
                    
                </tr>
                </tbody>
            </table>
            <div class="setBasicButn" style="border: none;">
                <el-button type="primary" size="medium" @click="saveFun" :disabled="saveLoading">提交</el-button>
            </div>

        </div>
    </div>
</template>
    
<script>
module.exports = {
    data: function () {
        return {
            input3: '',
            select: '',
            value: true,
            value1: '',
            checked: '',
            drawer: false,
            drawer2: false,
            currentPage4: 4,
            userModel:[],
            comModel:[],
            userModelArr:[],
            comModelArr:[],
            uri: "m=user&c=",
            sy_web_log_switch:'',
            save:{
                web_user_model:'',
                web_com_model:'',
                sy_web_log_switch:'',
            },
			
			saveLoading: false,
        }
    },
    created() {
        this.getConfig();
    },
    methods: {
        getConfig:function(){
            let _this = this;
            let url = _this.uri + 'admin_weblog&a=config';
            httpPost(url, _this.search).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.userModel = res.data.userModel;
                    _this.comModel = res.data.comModel;
                    _this.save.sy_web_log_switch = res.data.sy_web_log_switch;
                    _this.userModelArr = res.data.userModelArr;
                    _this.comModelArr = res.data.comModelArr;
                }
            })
        },
        saveFun:function () {
            let _this = this;
            let url = _this.uri + 'admin_weblog&a=setConfig';
            this.save.web_user_model = this.userModelArr.join(',')
            this.save.web_com_model = this.comModelArr.join(',')
			_this.saveLoading = true;
            httpPost(url, _this.save).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success('操作成功',_this.getConfig);
                }
            }).finally(function () {
                setTimeout(function () {
                    _this.saveLoading = false;
                }, 2000);
            });
        }

    },
};
</script>
<style scoped>
 
</style>