<!--客户信息-->
<template>
    <div class="icontesdt">
        <el-tooltip v-if="info.moblie_status == '1'" class="item" effect="dark" content="手机已认证" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;">手机已绑定</span><br/>
                <span style="line-height: 20px;">点击图标，可弹出绑定手机号</span>
            </div>
            <el-button type="text" @click="sjrz(info.linktel, info.moblie_status, info.uid)">
                <i class="rzicon rzicon_sjyrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-else class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;">手机未绑定</span><br/>
                <span style="line-height: 20px;">点击图标，可弹出绑定手机号</span>
            </div>
            <el-button type="text" @click="sjrz(info.linktel, info.moblie_status, info.uid)">
                <i class="rzicon rzicon_sjwrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="info.wxid != '' || info.wxopenid != '' || info.app_wxid != ''" class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;" v-html="info.wxBindmsg"></span><br/>
                <span style="line-height: 20px;">点击图标，可弹出绑定微信二维码</span>
            </div>
            <el-button type="text" @click="showQrcode(info.uid, info.wxid)">
                <i class="rzicon rzicon_wxyrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-else class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;" v-html="info.wxBindmsg"></span><br/>
                <span style="line-height: 20px;">点击图标，可弹出绑定微信二维码</span>
            </div>
            <el-button type="text" @click="showQrcode(info.uid, info.wxid)">
                <i class="rzicon rzicon_wxwrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="info.yyzz_status == '1'" class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;">企业资质已认证</span><br/>
                <span style="line-height: 20px;">点击图标，可查看企业资质认证</span>
            </div>
            <el-button type="text" @click="yyzzrz(info)">
                <i class="rzicon rzicon_zzyrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-else class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;">企业资质未认证</span><br/>
                <span style="line-height: 20px;">点击图标，可查看企业资质认证</span>
            </div>
            <el-button type="text" @click="yyzzrz(info)">
                <i class="rzicon rzicon_zzwrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="info.email_status == '1'" class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;">邮箱已认证</span><br/>
                <span style="line-height: 20px;">点击图标，可弹出绑定邮箱</span>
            </div>
            <el-button type="text" @click="yxrz(info.linkmail, info.email_status, info.uid)">
                <i class="rzicon rzicon_yxyrz"></i>
            </el-button>
        </el-tooltip>
        <el-tooltip v-else class="item" effect="dark" placement="top-start">
            <div slot="content">
                <span style="line-height: 20px;">邮箱未认证</span><br/>
                <span style="line-height: 20px;">点击图标，可弹出绑定邮箱</span>
            </div>
            <el-button type="text"  @click="yxrz(info.linkmail, info.email_status, info.uid)">
                <i class="rzicon rzicon_yxwrz"></i>
            </el-button>
        </el-tooltip>
		<el-tooltip v-if="info.fact_status == '1'" class="item" effect="dark" placement="top-start">
			<div slot="content">
				<span style="line-height: 20px;">实地已核验</span><br/>
				<span style="line-height: 20px;">点击图标，可弹出实地核验</span>
			</div>
			<el-button type="text" @click="factrz(info)">
				<i class="rzicon rzicon_sdyrz"></i>
			</el-button>
		</el-tooltip>
		<el-tooltip v-else class="item" effect="dark" placement="top-start">
			<div slot="content">
				<span style="line-height: 20px;">实地未核验</span><br/>
				<span style="line-height: 20px;">点击图标，可弹出实地核验</span>
			</div>
			<el-button type="text"  @click="factrz(info)">
				<i class="rzicon rzicon_sdwrz"></i>
			</el-button>
		</el-tooltip>
		<!--执照认证弹窗-->
		<div class="modluDrawer">
		    <el-dialog title="营业执照认证" :visible.sync="zzrztc" :with-header="true" :modal-append-to-body="false" append-to-body :show-close="true" width="450px">
		        <div>
		            <div class="wxsettip_small ">企业名称</div>
		            <el-input placeholder="宿迁鑫潮信息技术有限公司" v-model="yy_comname"></el-input>
		            <div class="wxsettip_small " v-if="config.com_social_credit == '1'">统一社会信用代码</div>
		            <el-input v-if="config.com_social_credit == '1'" v-model="yy_scredit" :disabled="true"></el-input>
		            <div class="wxsettip_small ">认证图片</div>
		            <div class="zzrz_img">
		                <div class="zzrz_imgpreview">
		                    <el-image style="width: 80px; height: 80px" :src="yy_picurl" :preview-src-list="yySrcList">
		                    </el-image>
		                    <div> 执照/代码证</div>
		                </div>
		                <div class="zzrz_imgpreview" v-if="config.com_cert_owner == '1'">
		                    <el-image style="width: 80px; height: 80px" :src="yy_owner_picurl"
		                              :preview-src-list="yySrcList">
		                    </el-image>
		                    <div> 经办人身份证</div>
		                </div>
		                <div class="zzrz_imgpreview" v-if="config.com_cert_wt=='1'">
		                    <el-image style="width: 80px; height: 80px" :src="yy_wt_picurl"
		                              :preview-src-list="yySrcList">
		                    </el-image>
		                    <div>委托书/承诺函</div>
		                </div>
		                <div class="zzrz_imgpreview" v-if="config.com_cert_other == '1'">
		                    <el-image style="width: 80px; height: 80px" :src="yy_other_picurl"
		                              :preview-src-list="yySrcList">
		                    </el-image>
		                    <div>其他材料</div>
		                </div>
		            </div>
		            <div class="wxsettip_small ">审核操作</div>
		            <el-radio v-model="yy_status" label="1">正常</el-radio>
		            <el-radio v-model="yy_status" label="2">未通过</el-radio>
		            <div class="wxsettip_small " v-if="config.com_free_status == '1'">同步操作</div>
		            <el-checkbox v-if="config.com_free_status == '1'" v-model="yy_job_status">所有未审核职位同步审核成功</el-checkbox>
		            <div class="wxsettip_small ">审核说明</div>
		            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="yy_sbody"></el-input>
		        </div>
		        <span slot="footer" class="dialog-footer">
					<el-button @click="zzrztc = false">取 消</el-button>
					<el-button type="primary" @click="yyzzrzSubmit">确 定</el-button>
				</span>
		    </el-dialog>
		</div>
		<!--手机认证弹窗-->
		<div class="modluDrawer">
		    <el-dialog title="手机认证" :visible.sync="sjrztc" :with-header="true" :modal-append-to-body="false" append-to-body :show-close="true" width="450px">
		        <div>
		            <div class="wxsettip_small ">手机号</div>
		            <el-input v-model="sj_mobile"></el-input>
		            <div class="wxsettip_small ">审核操作</div>
					<el-radio v-model="sj_status" :label="true">已认证</el-radio>
		        </div>
		        <span slot="footer" class="dialog-footer">
					<el-button @click="sjrztc = false">取 消</el-button>
					<el-button type="primary" @click="sjrzSubmit">确 定</el-button>
				</span>
		    </el-dialog>
		</div>
		<!--邮箱认证弹窗-->
		<div class="modluDrawer">
		    <el-dialog title="邮箱认证" :visible.sync="yxrztc" :with-header="true" :modal-append-to-body="false" append-to-body :show-close="true" width="450px">
		        <div>
		            <div class="wxsettip_small ">邮箱号</div>
		            <el-input v-model="yx_email"></el-input>
		
		            <div class="wxsettip_small ">审核操作</div>
					<el-radio v-model="yx_status" :label="true">已认证</el-radio>
		        </div>
		        <span slot="footer" class="dialog-footer">
					<el-button @click="yxrztc = false">取 消</el-button>
					<el-button type="primary" @click="yxrzSubmit">确 定</el-button>
				</span>
		    </el-dialog>
		</div>
		<!--微信认证弹窗-->
		<div class="modluDrawer" v-if="wxrztc">
		    <el-dialog title="微信扫码绑定" :visible.sync="wxrztc" :with-header="true" append-to-body :modal-append-to-body="false" :show-close="true" width="300px">
		        <div class="codeFldex">
		            <div>
		                <div class="code_img">
		                    <img :src="code_img" width="200" height="200">
		                </div>
		                <div class="code_p">将二维码发给企业，企业扫码后绑定</div>
		            </div>
		        </div>
		
		    </el-dialog>
		</div>
		<!--实地核验-->
		<div class="modluDrawer" v-if="factshow">
			<el-dialog title="实地核验" :visible.sync="factshow" :with-header="true" append-to-body
				:modal-append-to-body="false" :show-close="true" width="43%">
				<el-upload action="#" :auto-upload="false" multiple :limit="3" list-type="picture-card" :accept="pic_accept"
					:on-change="factChange" :file-list="factfileList" :on-exceed="factexceedFun"
					:on-preview="factPreview"
					:on-remove="factRemove"
					>
					<i slot="default" class="el-icon-plus"></i>
				</el-upload>
				
				<div style="font-size: 12px;color: #8c939d; margin-top:10px;">
					<i class="el-icon-warning-outline"></i>
					<span>只能上传jpg/png/jpeg/gif文件</span>
				</div>
				
				<div class="sdhy_tg">
					<el-checkbox v-model="fact_status">通过实地核验</el-checkbox>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="factshow = false">取 消</el-button>
					<el-button type="primary" @click="saveFact" :disabled="submitLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
		<el-dialog :visible.sync="factImageVisible" :with-header="true" append-to-body :modal-append-to-body="false" :show-close="true">
			<img width="100%" :src="factImageUrl" alt="">
		</el-dialog>
    </div>
</template>

<script>

module.exports = {
    props: {
        row: {
            type: Object,
            default: null
        },
		config: {
		    type: Object,
		    default: null
		},
    },
    watch: {
        row: {
            handler(val, oldVal) {
                this.info = val;
            },
            immediate: true,
			deep:true
        },
		
    },
    data: function () {
        return {
			setval : null,
            info:{},
			
			sj_mobile: '',
			sj_status: '',
			sj_uid: '',
			sjrztc: false,
			
			yx_email:'',
			yx_status:'',
			yx_uid:'',
			yxrztc:false,
			
			wxrztc:false,
			code_img:'',
			
			yy_picurl: '',
			yy_owner_picurl: '',
			yy_wt_picurl: '',
			yy_other_picurl: '',
			yy_uid: '',
			yy_status: '',
			yy_scredit: '',
			yy_comname: '',
			yySrcList: [],
			yy_job_status: false,
			yy_sbody: '',
			zzrztc:false,
			
			pic_accept: localStorage.getItem("pic_accept"),
			factshow:false,
			factuploadAction: baseUrl + 'm=user&c=company&a=upfactpic',
			factfileList: [],
			fact_picurl:[],
			fact_delid:[],
			fact_status:false,
			fact_uid:'',
			submitLoading:false,
			factImageVisible:false,
			factImageUrl:'',
		}
    },
	
    methods: {
        // 获取微信绑定二维码，显示微信绑定弹窗
        showQrcode: function (comid, wxid) {
            var that = this
            if (wxid != '') {
                message.error('企业已绑定微信');
                return false;
            }
            httpPost('m=user&c=company&a=comcert', {acwxbind: 1, comid: comid}).then(function (result) {
                var res = result.data;
                if (res.error == 0) {
                    that.code_img = res.data.code_url;
                    that.wxrztc = true;
                    that.setval = setInterval(function () {
                        that.wxbindstatus(comid)
                    }, 2000);
                } else {
                    message.error(res.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
		// 微信绑定绑定检测
		async wxbindstatus(comid) {
		    var that = this;
		    httpPost('m=user&c=company&a=getacbindstatus', {comid: comid}).then(function (result) {
		        var res = result.data;
		        if (res.error == 0) {
		            message.success('绑定成功', function () {
		                clearInterval(that.setval);
		            });
		        }
		    }).catch(function (e) {
		        console.log(e)
		    })
		},
        // 营业执照认证弹窗
        yyzzrz: function (data) {
            var that = this
            that.yy_comname = data.name
            if (data.yyzzurl) {
                that.yy_picurl = data.yyzzurl
            }
            if (data.owner_cert_url) {
                that.yy_owner_picurl = data.owner_cert_url
            }
            if (data.wt_cert_url) {
                that.yy_wt_picurl = data.wt_cert_url
            }
            if (data.other_cert_url) {
                that.yy_other_picurl = data.other_cert_url
            }
            that.yySrcList = []
            if (that.yy_picurl) {
                that.yySrcList.push(that.yy_picurl)
            }
            if (that.yy_owner_picurl) {
                that.yySrcList.push(that.yy_owner_picurl)
            }
            if (that.yy_wt_picurl) {
                that.yySrcList.push(that.yy_wt_picurl)
            }
            if (that.yy_other_picurl) {
                that.yySrcList.push(that.yy_other_picurl)
            }
            that.yy_uid = data.uid
            that.yy_status = data.yyzz_status
            that.yy_scredit = data.social_credit
            httpPost('m=user&c=company&a=comcert', {uid: data.uid, sbody: 1}).then(function (result) {
                if (result.data.error == 0) {
                    that.yy_sbody = result.data.data.sbody
                    that.zzrztc = true
                } else {
                    message.error('获取审核说明失败');
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        // 营业执照弹窗提交
        yyzzrzSubmit: function () {
            var that = this
            var params = {
                r_status: that.yy_status,
                uid: that.yy_uid,
                statusbody: that.yy_sbody,
                name:that.yy_comname
            }
            if (that.config.com_free_status == '1') {
                params.job_status = that.yy_job_status == true ? 1 : 0
            }
            httpPost('m=user&c=company&a=comcert', params).then(function (result) {
                if (result.data.error == 0) {
                    message.success(result.data.msg, function () {
                        that.zzrztc = false;
                        that.$emit("close-update");
                    });
                } else {
                    message.error(result.data.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        // 邮箱认证弹窗
        yxrz: function (email, status, uid) {
            var that = this
            that.yx_email = email
            that.yx_status = status == '1' ? true : false
            that.yx_uid = uid
            that.yxrztc = true
        },
        // 邮箱认证弹窗提交
        yxrzSubmit: function () {
            var that = this
            var params = {
                comemail: that.yx_email,
                estatus: that.yx_status == true ? 1 : 0,
                uid: that.yx_uid
            }
            httpPost('m=user&c=company&a=comcert', params).then(function (result) {
                if (result.data.error == 0) {
                    message.success(result.data.msg, function () {
                        that.yxrztc = false;
						that.$emit("close-update");
                    });
                } else {
                    message.error(result.data.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        // 手机认证弹窗
        sjrz: function (mobile, status, uid) {
            var that = this
            that.sj_mobile = mobile
            that.sj_status = status == '1' ? true : false
            that.sj_uid = uid
            that.sjrztc = true
        },
        // 手机认证弹窗提交
        sjrzSubmit: function () {
            var that = this
            var params = {
                comlinktel: that.sj_mobile,
                mstatus: that.sj_status == true ? 1 : 0,
                uid: that.sj_uid
            }
            httpPost('m=user&c=company&a=comcert', params).then((result)=>{
                if (result.data.error == 0) {
                    message.success(result.data.msg, function () {
                        that.sjrztc = false;
						that.$emit("close-update");
                    });
                } else {
                    message.error(result.data.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
		factrz:function(data){
			var that = this;
			let factpics = data.factpics;
			this.fact_delid = [];
			this.factfileList = [];
			for (let key in factpics) {
				this.factfileList.push({name:'',url:factpics[key]['pic_n'],id:factpics[key]['id']});
			}
			that.fact_uid = data.uid;
			that.fact_status = data.fact_status=='1'?true:false;
			this.factshow = true;
		},
		factRemove(file, fileList) {
			if(typeof file.id!='undefined' && file.id){
				this.fact_delid.push(file.id);
			}
			this.fact_picurl = fileList
		},
		factChange(file, fileList) {
			this.fact_picurl = fileList
		},
		factexceedFun(files, fileList) {
			this.$message.error('最多只能上传3张图片!');
		},
		factPreview(file){
			this.factImageUrl = file.url;
			this.factImageVisible = true;
		},
		saveFact: function () {
			let _this = this;
			var fact_status = _this.fact_status?1:0;
			var fact_picurl = this.fact_picurl;
			var params = new FormData();
			params.append('uid',_this.fact_uid);
			params.append('fact_status',fact_status);
			params.append('fact_delid',_this.fact_delid);
			
			for (let j in fact_picurl) {
				if (fact_picurl[j].raw) {
					params.append('newpic[]', fact_picurl[j].raw);
				}
			}
			_this.submitLoading = true;
			httpPost('m=user&c=company&a=savefact', params, {
				headers: { 'Content-Type': 'multipart/form-data' },
			}).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					_this.submitLoading = false;
					_this.factshow = false;
					_this.fact_picurl = [];
					_this.factfileList = [];
					_this.fact_delid = [];
					_this.fact_uid = '';
					
					message.success(res.msg,()=>{
						_this.$emit("close-update");
					})
					
				} else {
					message.error(res.msg);
					_this.submitLoading = false;
				}
			}).catch(function (error) {
				console.log(error);
			})
		},
    },
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
    display: none;
}
.drawerModlue{
    overflow: hidden;
    position: relative;
    width: 100%;
    height: 100%;
}
.drawerModInfo{
    overflow-y: auto;
    height: calc(100% - 80px);
}
.icontesdt .el-button+.el-button{
    margin-left: 0;
}

.icontesdt .el-button{
    padding: 0;
}
</style>