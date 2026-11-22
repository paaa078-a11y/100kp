<template>
    <div class="shbox">
        <div class="shinfo">
            <div class="shcomname">{{info.name}}
                <el-tag type="danger" size="mini">{{info.rating_name}}</el-tag>
            </div>
            <template v-if="is_net==1">
                <div class="sh_zwsz_add" v-if="zw_n != ''">参会分类：{{zw_n}}</div>
                <div class="sh_zwsz">
                    <el-button type="primary" size="mini" plain @click="$emit('fpzw')"><i class="el-icon-edit"></i> 展会区域</el-button>
                    <el-button type="primary" size="mini" @click="$emit('setcomjob')"><i class="el-icon-suitcase-1"></i> 参会职位</el-button>
                </div>
            </template>
            <template v-else>
                <div class="sh_zwsz_add">{{zphname}} 参会展位：{{space_n}}</div>
                <div class="sh_zwsz" style="top: 0;">
                    <el-button type="primary" size="mini" plain @click="$emit('setzhi')"><i class="el-icon-edit"></i> 设置展位
                    </el-button>
                    <el-button type="primary" size="mini" @click="$emit('setcomjob')"><i class="el-icon-suitcase-1"></i> 参会职位</el-button>
                </div>
            </template>
            <div class="shcomtel">
                        <span v-if="info.linkman">
                            联系人：{{info.linkman}}<span v-if="info.linkjob">（{{info.linkjob}}）</span>
                        </span>
                <span class="shcomtel_n" v-if="info.linktel">
                            联系电话：{{info.linktel}}
                        </span>
                <span v-if="info.crm_uid != '0'">
                            业务员：{{info.crm_name}}
                        </span>
            </div>
            <div class="shshowall" style="height: calc(100% - 105px);">
                <div class="shshow" style="overflow-y: auto; position: relative; height: 100%;">
                    <div class="shshow_tit"><i class="el-icon-office-building"></i> 基本资料</div>
                    <div class="shshow_p">
                        <div class="" v-if="info.welfare">企业福利：
                            <el-tag style="margin-right: 5px;" v-for="(item,index) in info.welfare_n" :key="index" size="mini">
                                {{item}}
                            </el-tag>
                        </div>
                        <div class="" v-if="info.hy">从事行业：{{info.hy_n}}</div>
                        <div class="" v-if="info.pr">企业性质：{{info.pr_n}}</div>
                        <div class="" v-if="info.mun">企业规模：{{info.mun_n}}</div>
                        <div class="" v-if="info.provinceid">企业地址：{{info.job_city_one}} {{info.job_city_two}}
                            {{info.job_city_three}} {{info.address}}
                        </div>
                        <div class="" v-if="info.content" v-html="info.content"></div>
                    </div>
                    <div class="shshow_tit" v-if="info.job_list.length > 0"><i class="el-icon-suitcase-1"></i>
                        招聘岗位
                    </div>
                    <ul class="shshow_joblist">
                        <li v-for="(item,index) in info.job_list" :key="index">
                            <el-link type="primary" :underline="false">
                                <div class="shshow_job">{{item.name}}</div>
                            </el-link>
                            <div class="shshow_jobinfo">
                                        <span class="shshow_jobxz">
                                            {{item.job_salary}}
                                        </span>
                                <span class="shshow_line" v-if="item.job_exp">| {{item.job_exp == '不限' ? '不限经验' : item.job_exp}}</span>
                                <span class="shshow_line" v-if="item.job_edu">| {{item.job_edu == '不限' ? '不限学历' : item.job_edu}}</span>
                            </div>
                            <span class="shshow_zt" v-if="item.ch_n == '已参会'">{{item.ch_n}}</span>
                            <span class="shshow_zt shshow_ztno" v-else>{{item.ch_n}}</span>
                        </li>
                    </ul>
                </div>
                <div class="shcz">
                    <div class="wxsettip_small ">参会企业审核</div>
                    <template>
                        <el-radio v-model="info.zph.status" label="1">正常</el-radio>
                        <el-radio v-model="info.zph.status" label="2">未通过</el-radio>
                    </template>
                    <div class="wxsettip_small ">审核状态说明</div>
                    <el-input type="textarea" v-model="info.zph.statusbody" :rows="2" placeholder="请输入内容">
                    </el-input>
                    <div class=" shczbth">
                        <el-button type="primary" @click="comStatusSave(info.zph.id)" :disabled="submitLoading">提 交</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        zphname:{
            type:String,
            default :""
        },
        space_n:{
            type:String,
            default :""
        },
        infoold:{
            type:Object,
            default:{}
        },
        zw_n:{
            type:String,
            default:''
        },
        is_net:{
            type: Number,
            default:0
        }
    },
    watch:{
        infoold: {
            handler(val) {
                    this.info = val
            },
            immediate: true,
            deep: true,
        },
    },
    data: function () {
        return {
            submitLoading:false,
            info:{}
        }
    },
    methods:{
        // 设置参会职位
        showComJob() {
            var that = this
            that.job_arr = []
            httpPost('m=neirong&c=zhaopinhui&a=getjoblist', { comid: that.info.uid }).then(function(response) {
                if (response.data.error == 0) {
                    if (response.data.data.length > 0) {
                        that.jobids = that.info.jobid_arr
                        that.job_arr = response.data.data
                        that.drawercomjob = true
                    } else {
                        message.error('该企业没有可作展出的职位！');
                    }
                } else {
                    message.error('该企业没有可作展出的职位！');
                }
            }).catch(function(error) {
                console.log(error);
            })
        },
        comStatusSave(id) {
            let com ={};
            com.params = { pid: id, status: this.info.zph.status, statusbody: this.info.zph.statusbody};
            com.tp =1;
            this.$emit('comsave',com)
        },
    }

}
</script>