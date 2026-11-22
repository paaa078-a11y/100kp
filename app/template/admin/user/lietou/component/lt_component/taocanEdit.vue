<template>
        <div class="drawerModlue">
            <div class="drawerModInfo drawerModInfoOne">
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>类别名称</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.name" placeholder="请输入内容"></el-input>
                    </div>
                    <div class="drawerModTips">
                        <el-alert title="如：高级会员" type="info" show-icon :closable="false">
                        </el-alert>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>会员模式</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-radio-group v-model="detail.type">
                            <el-radio label="1">套餐模式</el-radio>
                            <el-radio label="2">时间模式</el-radio>
                        </el-radio-group>

                    </div>
                    <div class="drawerModTips">
                        <el-alert title="套餐模式针对下载简历等数量控制，时间模式在服务有效期内，不限数量，可以限制每日上限" type="info" show-icon
                                  :closable="false">
                        </el-alert>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>购买金额</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.service_price" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <span slot="suffix" class="slotspan">元</span>
                        </el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>优惠价格</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.yh_price" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <span slot="suffix" class="slotspan">元</span>
                        </el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>优惠日期</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-date-picker v-model="time" type="daterange" range-separator="至"  value-format="yyyy-MM-dd"
                                        start-placeholder="开始日期" end-placeholder="结束日期">
                        </el-date-picker>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>服务时间</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.service_time" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <span slot="suffix" class="slotspan">天</span>
                        </el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>赠送优惠券</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-select v-model="detail.coupon" placeholder="请选择" clearable>
                            <el-option v-for="item in coupon" :key="item.id" :label="item.name"
                                       :value="item.id">
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>增值服务折扣</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.service_discount" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <span slot="suffix" class="slotspan">折</span>
                        </el-input>
                    </div>
                    <div class="drawerModTips">
                        <el-alert title="折扣针对不同等级会员所购买增值包服务享有的优惠！（0、100表示无折扣，88表示八八折）" type="info" show-icon
                                  :closable="false">
                        </el-alert>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>设置套餐</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.lt_breakjob_num" placeholder="请输入数字" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <template slot="prepend">刷新职位</template>
                            <template slot="append">份</template>
                        </el-input>
                        <el-input v-model="detail.lt_resume" placeholder="请输入数字" style="margin-left: 10px;" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <template slot="prepend">下载简历数</template>
                            <template slot="append">份</template>
                        </el-input>
                    </div>
                    <div class="drawerModTips">
                        <el-alert v-if="detail.type == '2'" title="限制时间内会员每日最大操作次数" type="warning" show-icon :closable="false">
                        </el-alert>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>上架职位</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.lt_job_num" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                            <span slot="suffix" class="slotspan">份</span>
                        </el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>排序</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.sort" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                    </div>
                    <div class="drawerModTips">
                        <el-alert title="小前大后" type="info" show-icon :closable="false">
                        </el-alert>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>显示状态</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-radio-group v-model="detail.display">
                            <el-radio label="1">启用</el-radio>
                            <el-radio label="2">不启用</el-radio>
                        </el-radio-group>

                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>会员类别图标</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-upload class="avatar-uploader" list-type="picture" :accept="pic_accept" action=""
                                   :auto-upload="false" :on-change="handleChangeWxewm" :show-file-list="false">
                            <img v-if="detail.com_pic" :src="detail.com_pic" class="avatar">
                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                        </el-upload>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>会员等级说明</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="detail.explains">
                        </el-input>
                    </div>
                    <div class="drawerModTips">
                        <el-alert title="会员等级备注说明或者优惠活动" type="info" show-icon :closable="false">
                        </el-alert>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>直聊</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="detail.chat_num" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                    </div>
                    <div class="drawerModTips">
                        <el-alert title="功能类似下载简历，一次扣除后，企业可以向个人用户发送信息" type="info" show-icon :closable="false">
                        </el-alert>
                    </div>
                </div>
            </div>
            <div class="setBasicButn" style="border: none;">
                <el-button type="primary" size="medium" @click="save" :disabled="submitLoading">提交</el-button>
            </div>
        </div>
</template>
<script>
module.exports = {
    props:{
        detail_v:Object,
        coupon:Array,
        category:String
    },
    watch: {
        detail_v:{
            handler(val) {
                this.detail = val;
            },
            deep: true,
            immediate: true
        }
    },
    mounted() {
        if (this.detail.time_start && this.detail.time_end){
            this.time = [this.detail.time_start,this.detail.time_end];
        }
    },
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            time:[],
            input: '',
            select: '',
            value: true,
            drawer: false,
            drawer2: false,
            seachbutn: false,
            tableHig: true,
            cityoptions:[],
            items: [
                { type: '', label: '正常' },
            ],
            uri: "m=user&c=",
            ltdata:[],
            ltclass_name:[],
            cityArrModel:[],
            jobVisible:false,
            inputJob:'',
            selectJobName:[],
            checkArrItem:[],
            zhiVisible:false,
            inputZhi:'',
            selectZhiName:[],
            checkArrZhiItem:[],
            radio: '1',
            tableData: [],
            value2: '',
            textarea: '',
            value3: '',
            submitLoading: false,
            detail:[]
        }
    },
    created(){
    },
    methods: {
        saveLietou:function () {
            let _this = this;
            let url  =this.uri +"admin_lt_member&a=save"
            httpPost(url, _this.detail).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg);
                }else {
                    message.error(res.msg);
                }
                // _this.$emit("save-getlist");
            })

        },
        // 上传时触发
        handleChangeWxewm(file, fileList) {
            this.detail.file = file.raw;
            this.$set(this.detail, 'com_pic', file.url);

        },
        save:function(){

            let _this = this;
            let formData = new FormData();
            if (isEmpty(_this.detail.name)){
                message.error('类别名称不能为空！');
                return;

            }
            $.each(_this.detail, function (key, value) {
                formData.append(key, value);
            });
            formData.append('time',this.time.join('~'))
            formData.append('category',this.category);
            let url = this.uri+'admin_lt_rating&a=saveclass';
            _this.submitLoading = true;
            httpPost(url, formData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg,function(){
                        _this.$emit("child-event");
                    });
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                setTimeout(function() {
                    _this.submitLoading = false;
                }, 2000);
            });
        }
    },
};
</script>

<style>
/* 上传样式开始 */
.avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.avatar-uploader .el-upload:hover {
    border-color: #409EFF;
}
.avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 148px;
    height: 148px;
    line-height: 148px;
    text-align: center;
}
.avatar {
    width: 148px;
    height: 148px;
    display: block;
}
</style>