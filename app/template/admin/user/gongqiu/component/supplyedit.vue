<template>
    <div style="height: 100%">
        <div class="drawerModInfo drawerModInfoOne">
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>姓名</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.name" placeholder="请输入姓名"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>性别</span>
                </div>
                <div class="drawerModInpt">
                    <el-select v-model="info.sex" placeholder="请选择">
                        <el-option v-for="(item,key) in user_sex" :key="key" :label="item" :value="key">
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>所在地区</span>
                </div>
                <div class="drawerModInpt drawerModLect">
                    <el-select v-model="info.provinceid" placeholder="请选择" @change="changeCity($event, 1)">
                        <el-option v-for="item in provinceList" :key="item.id" :label="item.name"
                                   :value="item.id">
                        </el-option>
                    </el-select>
                    <el-select v-model="info.cityid" placeholder="请选择" style="margin: 0 8px;" @change="changeCity($event, 2)">
                        <el-option v-for="item in cityList" :key="item.id" :label="item.name"
                                   :value="item.id">
                        </el-option>
                    </el-select>
                    <el-select v-model="info.three_cityid" placeholder="请选择" clearable>
                        <el-option v-for="item in regionList" :key="item.id" :label="item.name"
                                   :value="item.id">
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>提供服务</span>
                </div>
                <div class="drawerModInpt">
                    <el-input type="textarea" :rows="2" placeholder="请输入服务内容" v-model="info.services">
                    </el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>技能特长</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.speciality" placeholder="请输入特长"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>薪资待遇</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.salary" placeholder="请输入薪资" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                        <span slot="suffix" class="slotspan">元</span>
                    </el-input>
                </div>
            </div>

            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>头像</span>
                </div>
                <div class="drawerModInpt">
                    <el-upload :action="uploadAction" :on-change="uploadChangePic" :accept="pic_accept"
                                :show-file-list="false">
						<el-image v-if="info.photo_n" style="width:146px;height: 146px;" :src="info.photo_n" ></el-image>
						<i class="el-icon-plus" v-else></i>
                    </el-upload>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>联系手机</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="info.moblie" placeholder="请输入手机号" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>个人简介</span>
                </div>
                <div class="drawerModInpt">
                    <el-input type="textarea" :rows="5" placeholder="请输入个人简介" v-model="info.content">
                    </el-input>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" :disabled="saveLoading" @click="save">提交</el-button>
        </div>
    </div>
</template>

<script>
module.exports = {
	props: {
		uid_v: {
			type: String,
			default: ''
		}
	},
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
			uid:'',
			info: [],
			cache: {},
			user_sex:[],
			file: [],
            provinceList: [],
            cityList: [],
            regionList: [],
			saveLoading: false,
			uploadAction: baseUrl + 'm=common&c=common_upload'
        }
    },
	watch: {
		uid_v: {
			handler(n) {
				if (n != '') {
					this.uid = n.trim();
					this.getInfo();
				}
			},
			deep: true,
			immediate: true
		},
	},
    methods: {
		save(){
			let that = this;
			let params = new FormData();
			if (that.info.name == ''){
				message.error('姓名不能为空！');
				return false;
			}
			if (that.info.sex == ''){
				message.error('请选择性别！');
				return false;
			}
            if (that.info.provinceid == ''){
                message.error('请选择所在地区！');
                return false;
            }
			if (that.cityList.length >0 && that.info.cityid == ''){
                message.error('请选择所在地区！');
                return false;
			}
			if (that.info.services == ''){
				message.error('请填写提供服务！');
				return false;
			}
			if (that.info.salary == ''){
				message.error('请填写薪资待遇！');
				return false;
			}
			if (that.info.speciality == ''){
				message.error('请填写技能特长！');
				return false;
			}
			if (that.info.moblie == ''){
				message.error('请填写联系手机！');
				return false;
			}else if (!isjsMobile(that.info.moblie)){
				message.error('联系手机格式错误！');
				return false;
			}
			if (that.info.content == ''){
				message.error('请填写个人简介！');
				return false;
			}
			if (that.file.length !== 0){
				params.append('file',that.file);
			}
			params.append('submit', '提交');
			params.append('uid', that.uid);
			params.append('name', that.info.name);
			params.append('sex', that.info.sex);
			params.append('moblie', that.info.moblie);
			params.append('provinceid', that.info.provinceid);
			params.append('cityid', that.info.cityid);
			params.append('three_cityid', that.info.three_cityid);
			params.append('services', that.info.services);
			params.append('salary', that.info.salary);
			params.append('content', that.info.content);
			params.append('speciality', that.info.speciality);
			that.saveLoading = true;
			httpPost('m=user&c=supply_user&a=savegqInfo', params).then(function (response) {
				let res = response.data;
				if (res.error > 0){
					message.error(res.msg);
				}else{
					message.success(res.msg,function(){
						that.infoBox = false;
						that.$emit("child-event-getlist");
					});
				}
			}).finally(function () {
				setTimeout(function () {
				    that.saveLoading = false;
				}, 2000);
			});
		},

        // 地区联动
        getCity(level) {
            let that = this;

            httpPost('m=common&c=cache&a=getCity', {
                provinceid: that.info.provinceid,
                cityid: that.info.cityid,
                level: typeof level !== "undefined" ? level : ''
            }, {hideloading: true}).then(function (response) {
                let res = response.data,
                    data = res.data;

                if (typeof data.provinceList !== "undefined") {
                    that.provinceList = data.provinceList;
                }
                if (typeof data.cityList !== "undefined") {
                    that.cityList = data.cityList;
                }
                if (typeof data.regionList !== "undefined") {
                    that.regionList = data.regionList;
                }
            })
        },

        changeCity(val, level) {
            if (level == 1) {
                this.info.provinceid = val;
                this.info.cityid = '';
                this.info.three_cityid = '';
            } else if (level == 2) {
                this.info.cityid = val;
                this.info.three_cityid = '';
            }
            this.getCity(level);
        },

		getInfo(){
			let that = this;
			let params = {};
			if (that.uid != '') {
			    params.uid = that.uid;
			}
			httpPost('m=user&c=supply_user&a=edit', params).then(function (response) {
			    let res = response.data,
					data = res.data;
					
				that.info = data.info;
				that.user_sex = data.user_sex;
				that.cache = data.cache;

                that.getCity();
			}).catch(function (error) {
			    console.log(error);
			});
		},
        uploadChangePic(file) {
			if (file.status !== 'success') return;
			this.info.photo_n = URL.createObjectURL(file.raw);
			// 复刻文件信息
			this.file = file.raw;
		},
    },

};
</script>
<style scoped>
	.drawerModTite{width: 90px;}
    .drawerModInfo {
        overflow-y: auto;
        height: initial;
        max-height: calc(100% - (80px + 12px));
    }
	.drawerModInfoOne .drawerModInpt{width: calc(100% - 110px);}
.drawerModInfoOne .drawerModInpt .el-button {
    margin-left: 0;
}
</style>