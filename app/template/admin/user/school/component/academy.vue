<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
				<div class="moduleInptList">
					<el-input size="small" placeholder="输入你要搜索的关键字" @keyup.enter.native="search" v-model="searchForm.keyword" class="input-with-select" clearable>
						<el-select v-model="searchForm.type" slot="prepend" placeholder="院校名称">
			               <el-option label="院校名称" value="1"></el-option>
			               <el-option label="联系电话" value="2"></el-option>
						</el-select>
					</el-input>
				</div>
				<div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="tableSeachInptsmall moduleInptList">
				    <el-select v-model="searchForm[searchItem.param]" size="small" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
				        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue" ></el-option>
				    </el-select>
				</div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
				<div class="moduleInptList">
					<el-button type="primary" icon="el-icon-document-add" size="mini" @click="addschool">新增院校</el-button>
				</div>
            </div>
        </div>
        <!-- <div class="admin_datatip">
            <i class="el-icon-document"></i>
            数据统计：共 400 条
            <span class="admin_datatip_n">未审核：32 条 </span>
            <span class="admin_datatip_n">未通过：3 条</span>
            <span class="admin_datatip_n">已锁定：1 条</span>
            <span class="admin_datatip_n">搜索结果： 400 条</span>
        </div> -->
        <div class="moduleElTable modulElTableOnes">
            <el-table :data="list" border style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
					<p>{{dataText}}</p>
				</template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="ID" width="90">
                </el-table-column>
                <el-table-column prop="schoolname" label="院校名称" min-width="180">
                </el-table-column>
                <el-table-column label="所在城市" width="140">
					<template slot-scope="scope">
						{{scope.row.provinceid_n}}<span v-if="scope.row.cityid_n">-{{scope.row.cityid_n}}</span>
					</template>
                </el-table-column>
                <el-table-column label="logo" width="130">
					<template slot-scope="scope">
						<el-tooltip placement="right" v-if="scope.row.photo_n">
							<div slot="content">
								<el-image width="100%" :src="scope.row.photo_n" alt=""></el-image>
							</div>
							<el-image width="20" height="20" src="../../../admin/images/admin_n_img.png" alt=""></el-image>
						</el-tooltip>
						<span v-else>无</span>
					</template>
                </el-table-column>
                <el-table-column prop="school_categty_n" label="学科类别" width="140">
                </el-table-column>
                <el-table-column prop="school_department_n" label="主管部门" width="140">
                </el-table-column>
                <el-table-column prop="school_level_n" label="办学层次" width="140">
                </el-table-column>
                <el-table-column prop="school_phone" label="联系电话" width="140">
                </el-table-column>
                <el-table-column prop="lastupdate_n" label="更新时间" width="140">
                </el-table-column>
                <el-table-column label="站点" width="140">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <span>{{cache['Dname'][scope.row.did]}}</span>
                            <el-button type="text" @click="shareDid(scope.row)" style="padding: 0;">分配</el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="150">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="detail(scope.row)">详情</el-button>
							<el-button size="mini" @click="edit(scope.row)">修改</el-button>
							<el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                   :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                   layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
		<!--分配站点弹窗-->
		<div class="modluDrawer">
			<el-dialog title="分配站点" :visible.sync="drawerfpzd" append-to-body width="450px">
				<div class="wxsettip_small ">用户名</div>
				<el-input placeholder="院校名称" v-model="schoolname" :disabled="true"></el-input>
				<div>
					<div class="wxsettip_small ">切换站点</div>
					<div class="wxsettip_Sealect">
						<el-select v-model="schooldid" size="small" slot="prepend" placeholder="请选择" filterable>
							<el-option label="主站" value="0"></el-option>
							<el-option v-for="(item,index) in cache.site_domain" :key="index" :label="item.webname" :value="item.id">
							</el-option>
						</el-select>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="drawerfpzd = false">取 消</el-button>
					<el-button type="primary" @click="fpzdSubmit(1)" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
        <!-- 弹窗 -->
		<el-drawer title="院校详情" :visible.sync="schoolDetail" :modal-append-to-body="false" size="40%">
			<div class="moduleSchools">
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>院校编号</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.id}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>院校名称</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.schoolname}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>所在城市</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.provinceid_n}}<span v-if="curr_school.cityid_n">-{{curr_school.cityid_n}}</span>
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>学科类别</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.school_categty_n}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>主管部门</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.school_department_n}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>办学层次</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.school_level_n}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>标签</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.schooltag}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>联系电话</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.school_phone}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>地址</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.address}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>官网</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.schoolinternet}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>添加时间</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.downtime}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>更新时间</span>
					</div>
					<div class="drawerModInpt">
						{{curr_school.lastupdate}}
					</div>
				</div>
				<div class="drawerModLis">
					<div class="drawerModTite">
						<span>LOGO</span>
					</div>
					<div class="drawerModInpt">
						<el-image :src="curr_school.photo_n" style="max-width: 200px; max-height: 200px" :preview-src-list="[curr_school.photo_n]">
						</el-image>
					</div>
				</div>
			</div>
		</el-drawer>
        <el-drawer :title="schoolboxtitle" :visible.sync="schoolBox" :append-to-body="true" size="40%">
            <div class="moduleSchools">
                <div class="drawerModLis" v-if="curr_school.id > 0">
                    <div class="drawerModTite">
                        <span>招聘编号</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="curr_school.id" disabled></el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>院校名称</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="curr_school.schoolname" placeholder="请输入内容"></el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>所在城市</span>
                    </div>
                    <div class="drawerModInpt drawerModLect">
						<el-select v-model="curr_school.provinceid" placeholder="请选择" @change="changeCity($event, 1)">
						    <el-option v-for="item in provinceList" :key="item.id" :label="item.name"
						        :value="item.id">
						    </el-option>
						</el-select>
						<el-select v-model="curr_school.cityid" placeholder="请选择" style="margin: 0 8px;" @change="changeCity($event, 2)">
						    <el-option v-for="item in cityList" :key="item.id" :label="item.name"
						        :value="item.id">
						    </el-option>
						</el-select>
						<el-select v-model="curr_school.three_cityid" placeholder="请选择" clearable>
						    <el-option v-for="item in regionList" :key="item.id" :label="item.name"
						        :value="item.id">
						    </el-option>
						</el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>学科类别</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-select v-model="curr_school.school_categty" placeholder="请选择">
                            <el-option v-for="item in cache.schoolata.school_categty" :key="item" :label="cache.schoolclass_name[item]" :value="item">
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>主管部门</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-select v-model="curr_school.school_department" placeholder="请选择">
                            <el-option v-for="item in cache.schoolata.school_department" :key="item" :label="cache.schoolclass_name[item]" :value="item">
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>办学层次</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-select v-model="curr_school.school_level" placeholder="请选择">
                            <el-option v-for="item in cache.schoolata.school_level" :key="item" :label="cache.schoolclass_name[item]" :value="item">
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>标签</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-select v-model="curr_school.schooltag" placeholder="请选择">
                            <el-option v-for="item in cache.schoolata.school_tag" :key="item" :label="cache.schoolclass_name[item]" :value="item">
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>联系电话</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="curr_school.school_phone" placeholder="请输入内容" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>地址</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="curr_school.address" placeholder="请输入内容"></el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>邮箱</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="curr_school.schoolemail" placeholder="请输入内容"></el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>官网</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="curr_school.schoolinternet" placeholder="请输入内容"></el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>LOGO</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-upload class="avatar-uploader" :accept="pic_accept" :action="uploadAction" :show-file-list="false" :on-change="picChange">
                            <img style="width:120px;height: 120px;" v-if="curr_school.photo_n" :src="curr_school.photo_n" class="avatar">
                            <el-button v-else type="primary" icon="el-icon-document-add">上传LOGO</el-button>
                        </el-upload>
                    </div>
                </div>
                <div class="setBasicButn" style="border: none;">
                    <el-button type="primary" size="medium" @click="saveschool" :disabled="saveLoading">提交</el-button>
                </div>
            </div>
        </el-drawer>

    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
        	loading: false,
        	dataText: '数据加载中',
            searchList: {},
            searchForm: {
                type: '1',
                keyword: ''
            },
            page: 1,
            limit: 0,
            list: [],
            total: 0,
            pageSizes: [],

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],

			cache:{
				Dname: {},
				city_index:[],
				city_name:[],
				city_type:[],
				schoolata:{
					school_categty:[],
					school_department:[],
					school_level:[],
					school_tag:[],
				},
				schoolclass_name: [],
				site_domain:{}
			},
			drawerfpzd: false,
			schooldid: '0',
			schoolname:'',
			schoolid: '',

			saveLoading: false,

			schoolBox: false,
			curr_school: {},
			file:[],
			cityList: [],
			provinceList: [],
			regionList: [],
			schoolboxtitle: '',

			schoolDetail:false,
			uploadAction: baseUrl + 'm=common&c=common_upload',

			prevPage: 0
        }
    },
    mounted() {
        var that = this
        setTimeout(function () {
            that.getCacheFun();
        }, 200)
    },
    methods: {
    	getCacheFun:function(){
            let that = this;
            httpPost('m=user&c=school_academy&a=getCacheData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.cache = res.data.cache;
                    that.searchList = res.data.search_list;
                }
            })
        },
		detail(row){
			this.curr_school = row;
			this.schoolDetail = true;
		},
		saveschool(){
			let that = this;
			let formData = new FormData();
			if (that.curr_school.schoolname =='' || that.curr_school.schoolname == undefined){
				message.error('请填写院校名称！');
				return false;
			}
			if (that.curr_school.provinceid == '' || that.curr_school.provinceid == undefined){
			    message.error('请选择所在地区！');
			    return false;
			}
			if (that.cityList.length >0 && that.curr_school.cityid == '' || that.curr_school.cityid == undefined){
			    message.error('请选择所在地区！');
			    return false;
			}
			if (that.curr_school.school_categty == '' || that.curr_school.school_categty == undefined){
				message.error('请选择学科类别！');
				return false;
			}
			if (that.curr_school.school_department == '' || that.curr_school.school_department == undefined){
				message.error('请选择主管部门！');
				return false;
			}
			if (that.curr_school.school_level == '' || that.curr_school.school_level == undefined){
				message.error('选择办学层次！');
				return false;
			}
			if (that.curr_school.schooltag == '' || that.curr_school.schooltag == undefined){
				message.error('请选择标签！');
				return false;
			}
			if (that.curr_school.school_phone == '' || that.curr_school.school_phone == undefined){
				message.error('请填写联系电话！');
				return false;
			}
			if (that.curr_school.address == '' || that.curr_school.address == undefined){
				message.error('请填写地址！');
				return false;
			}
			if (that.curr_school.schoolemail == '' || that.curr_school.schoolemail == undefined){
				message.error('请填写邮箱！');
				return false;
			}else{
				var regs = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
				if (!regs.test(that.curr_school.schoolemail)) {
					message.error('邮箱格式错误！');
					return false;
				}
			}
			if (that.curr_school.schoolinternet == '' || that.curr_school.schoolinternet == undefined){
				message.error('请填写官网！');
				return false;
			}
			if (that.curr_school.photo_n == '' || that.curr_school.photo_n == undefined){
				message.error('选择上转LOGO！');
				return false;
			}
			if (that.file.length !== 0){
				formData.append('file',that.file);
			}
			if (that.curr_school.id > 0){
				formData.append('id', that.curr_school.id);
			}
			formData.append('schoolname', that.curr_school.schoolname);
			formData.append('provinceid', that.curr_school.provinceid);
			formData.append('cityid', that.curr_school.cityid);
			formData.append('three_cityid', that.curr_school.three_cityid);
			formData.append('school_department', that.curr_school.school_department);
			formData.append('school_level', that.curr_school.school_level);
			formData.append('school_categty', that.curr_school.school_categty);
			formData.append('school_phone', that.curr_school.school_phone);
			formData.append('address', that.curr_school.address);
			formData.append('schoolemail', that.curr_school.schoolemail);
			formData.append('schoolinternet', that.curr_school.schoolinternet);
			formData.append('schooltag', that.curr_school.schooltag);
			that.saveLoading = true;
			httpPost('m=user&c=school_academy&a=saveacademy', formData).then(function (response) {
				let res = response.data;
				if (res.error==0){
					message.success(res.msg,function(){
						that.schoolBox = false;

						that.getList();
					})
				}else{
					message.error(res.msg);
				}
			}).finally(function () {
				setTimeout(function () {
				    that.saveLoading = false;
				}, 2000);
			});
		},
		addschool(){
			this.curr_school = {
				photo_n: '',
				provinceid: '',
				cityid: '',
				three_cityid: '',
			};
			this.schoolboxtitle = "添加院校";
			this.getCity();
			this.schoolBox = true;
		},
		edit(row){
			this.curr_school = row;
			this.curr_school.three_cityid = this.curr_school.three_cityid == 0? '':this.curr_school.three_cityid;
			this.curr_school.cityid = this.curr_school.cityid == 0? '':this.curr_school.cityid;
			
			this.schoolboxtitle = "修改院校";
			this.getCity();
			this.schoolBox = true;
		},
		fpzdSubmit: function(type){
			let that = this;
			if (that.schoolid == ''){
				message.error('请选择要分配的院校');
			}
			let params = {
				uid: that.schoolid,
				did: that.schooldid
			}
			that.saveLoading = true;
			httpPost('m=user&c=school_academy&a=checksitedid', params).then(function (response) {
				let res = response.data;
				if (res.error==0){
					message.success(res.msg,function(){
						that.drawerfpzd = false;
						that.getList();
					})
				}else{
					message.error(res.msg);
				}

			}).finally(function () {
				setTimeout(function () {
				    that.saveLoading = false;
				}, 2000);
			});
		},
		shareDid(row){
			this.schoolid = row.id;
			this.schooldid = '' + row.did;
			this.schoolname = row.schoolname;
			this.drawerfpzd = true;
		},
        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
        },
        search() {
            this.page = 1;
            this.getList();
        },
        getList() {
            let that = this,
                searchForm = that.searchForm,
                params = {
                    page: that.page,
                    limit: that.limit,
                };
            	that.loading = true;

            httpPost('m=user&c=school_academy', {...params, ...searchForm}, {hideloading: true}).then(function (response) {
                let res = response.data,
                    data = res.data;

                that.list = data.list;
                that.total = parseInt(data.total);
                that.pageSizes = data.page_sizes;
                if (that.limit === 0) {
                    that.limit = parseInt(data.limit); // 取系统配置默认数量
                }
                if (that.page > data.page) {
                    that.page = parseInt(data.page); // 最后一页被删除后，取最新的页数
                }
				
				that.loading = false;
				if(that.prevPage != that.page){
                    that.prevPage = that.page;
                    that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
				if (that.list.length === 0) {
                    that.dataText = "暂无数据";
                }
            })
        },
        handleSelectionChange(val) {
            if (val.length == 0) {
                this.checkedAll = false;
                this.checkedAllIndeterminate = false;
            } else {
                if (val.length === this.list.length) {
                    this.checkedAll = true;
                    this.checkedAllIndeterminate = false;
                } else {
                    this.checkedAll = false;
                    this.checkedAllIndeterminate = true;
                }
            }
            this.multipleSelection = val;
        },
        batch(type) {
            if (this.multipleSelection.length == 0 && type == 'del') {
	            message.error('请选择要删除的数据');
	            return false;
	        }else if (this.multipleSelection.length == 0){
	            message.error('请选择要操作的数据项');
	            return false;
	        }

            let idArr = [];
            this.multipleSelection.forEach(function(item) {
                idArr.push(item.id);
            })
            this.idArr = idArr;

            if (type == 'del') {
                this.del();
            }
        },
        checkAll(val) {
            val ? this.checkedAllIndeterminate = false : '';
            this.$refs.multipleTable.toggleAllSelection();
        },
        del(idx) {
            let that = this,
                params = {},
                msg = '';

            if (typeof idx == 'undefined') { // 批量删除
                params.del = this.idArr;
                msg = '你确定要删除选中项吗？';
            } else {// 单个删除
                params.del = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function (params) {
                httpPost('m=user&c=school_academy&a=del', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        message.success(res.data.msg, function () {
                            that.$refs.multipleTable.clearSelection();
                            that.getList();
                        });
                    }
                })
            }, msg)
        },
        picChange(file) {
			// 预览文件处理
			this.curr_school.photo_n = URL.createObjectURL(file.raw);
			// 复刻文件信息
			this.file = file.raw;
		},
		changeCity(val, level) {
		    if (level == 1) {
		        this.curr_school.provinceid = val;
		        this.curr_school.cityid = '';
		        this.curr_school.three_cityid = '';
		    } else if (level == 2) {
		        this.curr_school.cityid = val;
		        this.curr_school.three_cityid = '';
		    }
		    this.getCity(level);
		},
		getCity(level) {
		    let that = this;

		    httpPost('m=common&c=cache&a=getCity', {
		        provinceid: that.curr_school.provinceid,
		        cityid: that.curr_school.cityid,
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
    },
};
</script>
<style>
.drawerModLect {
    display: flex;
    align-items: center;
}
.el-image__error{
	background: none;
	color: #fff;
}
</style>