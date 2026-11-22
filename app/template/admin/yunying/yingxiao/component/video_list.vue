<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" v-model="searchForm.keyword" placeholder="请输入公司名称" clearable></el-input>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
            <div class="moduleSeachButn">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="openAdd('')">添加视频</el-button>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="list" border style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                      @sort-change="sortChange" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="80" sortable="custom">
                </el-table-column>
                <el-table-column prop="com_name" label="企业名称" min-width="200">
                </el-table-column>
                <el-table-column prop="slogan" label="视频口号" min-width="240">
                </el-table-column>
                <el-table-column label="推荐" width="100">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.rec" active-color="#1890FF" inactive-color="#B8BDC9"
                                   active-value="1" inactive-value="0" @change="bindRec($event, scope.row)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="公开" width="100">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.display" active-color="#1890FF" inactive-color="#B8BDC9"
                                   active-value="1" inactive-value="0" @change="bindDisplay($event, scope.row)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="排序" width="110" property="sort" sortable="custom">
                    <template slot-scope="scope">
                        <div class="moduleProps moduleTrButn" v-if="scope.row[scope.column.property + 'isShow']">
                            <el-input :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index"
                                      v-model="scope.row.sort" type="number" @blur="editSort(scope)"></el-input>
                        </div>
                        <div class="moduleProps moduleTrButn" v-else>
                            <span>{{ scope.row.sort }}</span>
                            <el-button type="text" icon="el-icon-edit" @click="showSort(scope)"></el-button>
                        </div>
                    </template>

                </el-table-column>
                <el-table-column prop="pubdate" label="发布时间" width="160" sortable="custom">
                    <template slot-scope="scope">
                        <div>{{scope.row.pubdate_n}}</div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="preview(scope.row)">预览</el-button>
                            <el-button size="mini" @click="openLog(scope.row)">记录</el-button>
                            <el-button size="mini" @click="openAdd(scope.row)">修改</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulecz modulePagButn">
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>

        <div class="modluDrawer">
            <el-drawer :title="detail.id ? '企业视频调整' : '添加企业视频'" :visible.sync="drawerAdd" :modal-append-to-body="false" :show-close="true"
                :with-header="true" size="800px" :before-close="closeAdd">
                <div class="drawerModlue" v-if="drawerAdd">
                    <div class="tableDoAlert" v-if="detail.id">
                        <span>修改操作：上传新的视频文件，会将企业原有的视频文件删除；</span>
                        <a style="color:#f30;display: block;">如果不需要更换视频，请勿点击“视频文件 - 重新选择”</a>
                    </div>
                    <div class="tableDoAlert" v-else>
                        <span>企业筛选：操作人员可以通过企业名称关键词匹配，筛选需要添加视频的企业信息！</span>
                    </div>
                    <div class="drawerModInfo" style="height: calc(100% - 140px); overflow-y: auto;">
                        <template v-if="detail.id">
                            <div class="drawerModLis">
                                <div class="drawerModTite">
                                    <span>企业名称</span>
                                </div>
                                <div class="drawerModInpt">
                                    <el-input v-model="com_name" readonly placeholder=""></el-input>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="drawerModLis">
                                <div class="drawerModTite">
                                    <span>企业名称</span>
                                </div>
                                <div class="drawerModInpt" style="display: flex; align-items: center;">
                                    <el-input v-model="com_name" placeholder="请输入企业名称进行搜索"></el-input>
                                    <el-button style="margin-left: 8px;" size="mini" type="warning" @click="searchCom">搜索</el-button>
                                </div>
                            </div>
                            <div class="drawerModLis">
                                <div class="drawerModTite">
                                    <span>选择企业</span>
                                </div>
                                <div class="drawerModInpt">
                                    <el-select v-model="ruleForm.uid" placeholder="请选择">
                                        <el-option v-for="comItem in comList" :key="comItem.uid" :label="comItem.name"
                                                   :value="comItem.uid">
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="drawerModTips">
                                    <el-alert title="输入企业名称搜索后选择企业" type="info" show-icon :closable="false">
                                    </el-alert>
                                </div>
                            </div>
                        </template>

                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>视频封面</span>
                            </div>
                            <div class="drawerModInpt" style="display: flex; align-items: center;">
                                <el-image v-if="ruleForm.pic_n" style="width: 280px; height: 150px" :src="ruleForm.pic_n"></el-image>
                                <el-upload :accept="pic_accept"
                                        class="upload-demo"
                                        list-type="picture"
                                        action=""
                                        :auto-upload="false"
                                        :on-change="handleChangePic"
                                        :show-file-list="false">
                                    <el-button icon="el-icon-document-add" style="margin-left: 10px;" type="primary">{{ ruleForm.pic_n ? '重新上传' : '上传图片' }}</el-button>
                                </el-upload>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>视频文件</span>
                            </div>
                            <div class="drawerModInpt" style="display: flex; align-items: center;">
                                <div id="player">
                                    <div id="J_prismPlayer"></div>
                                    <div id="videoPreview"></div>
                                </div>
                                <el-upload
                                        class="upload-demo"
                                        action=""
                                        accept="video/*"
                                        :auto-upload="false"
                                        :on-change="handleChangeVideo"
                                        :show-file-list="false">
                                    <el-button style="margin-left: 10px;" icon="el-icon-document-add" type="primary">{{ ruleForm.videoid && ruleForm.videoid !== '0' ? '重新选择' : '选择文件' }}</el-button>
                                </el-upload>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>视频口号</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="ruleForm.slogan">
                                </el-input>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>视频推荐</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-switch v-model="ruleForm.rec" active-value="1" inactive-value="0">
                                </el-switch>
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="注：首页推荐" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>是否公开</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-switch v-model="ruleForm.display" active-value="1" inactive-value="0">
                                </el-switch>
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="注：选择是，客户才能够看到企业视频" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>视频排序</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input v-model="ruleForm.sort" placeholder="" @input="inputIntNumber($event, 'ruleForm', 'sort')"></el-input>
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="注：大前 小后" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                    </div>
                    <div class="setBasicButn" style="border: none;">
                        <el-button type="primary" size="medium" @click="save">提交</el-button>
                    </div>
                </div>
            </el-drawer>

            <el-drawer :title="detail.com_name + '-企业视频记录'" :visible.sync="drawerLog" append-to-body :show-close="true"
                       :with-header="true" size="65%">
                <log :id="detail.id"></log>
            </el-drawer>
        </div>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            loading: false,
            dataText: '数据加载中',
            // 搜索筛选项
            searchForm: {},

            // 列表
            page: 1,
            limit: 0,
            list: [],
            total: 0,
            pageSizes: [],

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],

            // 列表排序
            t: '',
            order: '',

            detail: {},

            // 助力人数查看
            dialogLog: false,
            loglist: [],

            // 企业视频添加/编辑
            com_name: '',
            file: null,
            drawerAdd: false,
            comList: [],
            ruleForm: {},

            // 参数配置
            sy_weburl: localStorage.getItem("sy_weburl"),
            config: {},

            saveLoading: false,
            submitLoading: null,

            oldData: null,

            // 企业视频记录
            drawerLog: false,
            prevPage:0
        }
    },
    components: {
        'log': httpVueLoader('./video_log.vue'),
    },
    mounted() {

    },
    created() {
        this.init();
    },
    methods: {
        init() {
            this.resetSearch();
            this.resetData();
            this.search();
            this.getConfig();
        },

        resetSearch() {
            this.searchForm = {};
            this.limit = 0;
        },

        resetData() {
            this.ruleForm = {
                uid: '',
                rec: '0',
                display: '2'
            };
            this.com_name = '';
            this.file = null;
            this.comList = [];
        },

        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
        },
        sortChange(event) {
            this.t = event.order ? event.prop : '';
            this.order = event.order ? event.order == 'descending' ? 'desc' : 'asc' : '';
            this.search();
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
                    t: that.t,
                    order: that.order,
                };
            that.loading = true;
            httpPost('m=yunying&c=yingxiao_video', {...params, ...searchForm}, {hideloading: true}).then(function (response) {
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
                if(that.prevPage != that.page){
                    that.prevPage = that.page;
                    that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
                that.loading = false;
                if (that.list.length === 0) {
                    that.dataText = "暂无数据";
                }
            })
        },

        // 批量操作
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
            if (this.multipleSelection.length == 0) {
                message.error('请选择要删除的数据');
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
                params.id = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function (params) {
                httpPost('m=yunying&c=yingxiao_video&a=del', params).then(function(res) {
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

        getConfig() {
            let that = this;

            httpPost('m=yunying&c=yingxiao_video&a=config',{}, {hideloading: true}).then(function(response) {
                let res = response.data,
                    data = res.data;

                that.config = data.config;
            })
        },

        inputIntNumber(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9]/g,'');
        },

        // 上传时触发
        handleChangePic(file, fileList) {
            this.$set(this.ruleForm, 'pic', file.raw);
            this.$set(this.ruleForm, 'pic_n', file.url);
        },

        // 上传时触发 - 视频
        handleChangeVideo(file, fileList) {
            let video_max_size = this.config.video_max_size,
                size = (file.size / 1024 / 1024).toFixed(2);

            if (parseFloat(video_max_size) > parseFloat(size)) {
                this.file = file.raw;

                //上传之前，可以预览视频
                var url = URL.createObjectURL(file.raw);
                console.log('url:' + url);
                var previewDiv = document.getElementById('videoPreview');
                previewDiv.innerHTML = '\
                <video width="355.55" height="200" controls id="video">\
                <source src="' + url + '" id="videosrc" type="' + file.raw.type + '"/>\
                浏览器不支持' + file.raw.type + '格式视频的预览\
                </video>\
                ';
                $('#J_prismPlayer').hide();
                var myVideo = document.getElementById('video');

                //  暂停
                myVideo.addEventListener('pause', function () {
                    console.log('pause');
                });
            } else {
                message.error('上传视频不能超过' + video_max_size + 'MB,请重新上转');
            }
        },

        async openAdd(data) {
            this.resetData();

            if (data != '') {
                this.detail = data;

                let response = await httpPost('m=yunying&c=yingxiao_video&a=getInfo', {id: data.id});

                let res = response.data;

                if (res.error > 0) {
                    message.error(res.msg);
                    return false;
                } else {
                    let info = res.data.info;

                    this.com_name = info.com_name;

                    this.ruleForm = {
                        id: info.id,
                        uid: info.uid,
                        pic_n: info.pic_n,
                        videoid: info.vid,
                        slogan: info.slogan,
                        rec: info.rec,
                        display: info.display,
                        sort: info.sort
                    };

                    this.drawerAdd = true;

                    if (info.playUrl && info.playUrl != '') {
                        setTimeout(function () {
                            var player = new Aliplayer({
                                id: "J_prismPlayer",                //  视频播放器ID
                                autoplay: false,
                                width: "355.55px",
                                height: "200px",
                                vid: info.vid,    //  视频ID
                                source: info.playUrl, //  视频播放地址 mp4
                                cover: info.video_pic,//  视频封面
                                skinLayout: [
                                    {
                                        "name": "bigPlayButton",
                                        "align": "cc"
                                    },
                                    {
                                        "name": "H5Loading",
                                        "align": "cc"
                                    },
                                    {
                                        "name": "errorDisplay",
                                        "align": "tlabs",
                                        "x": 0,
                                        "y": 0
                                    },
                                    {
                                        "name": "infoDisplay"
                                    },
                                    {
                                        "name": "tooltip",
                                        "align": "blabs",
                                        "x": 0,
                                        "y": 56
                                    },
                                    {
                                        "name": "thumbnail"
                                    },
                                    {
                                        "name": "controlBar",
                                        "align": "blabs",
                                        "x": 0,
                                        "y": 0,
                                        "children": [
                                            {
                                                "name": "progress",
                                                "align": "blabs",
                                                "x": 0,
                                                "y": 44
                                            },
                                            {
                                                "name": "playButton",
                                                "align": "tl",
                                                "x": 15,
                                                "y": 12
                                            },
                                            {
                                                "name": "timeDisplay",
                                                "align": "tl",
                                                "x": 10,
                                                "y": 7
                                            },
                                            {
                                                "name": "fullScreenButton",
                                                "align": "tr",
                                                "x": 10,
                                                "y": 12
                                            },
                                            {
                                                "name": "volume",
                                                "align": "tr",
                                                "x": 5,
                                                "y": 10
                                            }
                                        ]
                                    }
                                ]
                            });
                        }, 500)
                    }
                }
            } else {
                this.detail = {};
                this.drawerAdd = true;
            }
        },

        closeAdd(done) {
            $('#player').html('<div id="J_prismPlayer"></div><div id="videoPreview"></div>');

            done();
        },

        searchCom() {
            let that = this,
                com_name = that.com_name;

            httpPost('m=yunying&c=yingxiao_video&a=getComList', {name: com_name}).then(function(response) {
                let res = response.data,
                    data = res.data;

                that.ruleForm.uid = '';
                that.comList = data.list;
            })
        },

        save() {
            let that = this,
                detail = that.detail,
                file = that.file,
                ruleForm = that.ruleForm;

            if (that.saveLoading) {
                return false;
            }

            if (!ruleForm.uid) {
                message.error('请选择视频企业');
                return false;
            }

            if (that.file == null) {
                if (!detail.id) {
                    message.error('请上传企业视频');
                    return false;
                }

                that.showSubmitLoading();

                that.submit();
            } else {
                that.showSubmitLoading();

                httpPost('m=yunying&c=yingxiao_video&a=getVideoUploadToken', {
                    filename: file.name,
                    size: file.size
                }, {hideloading: true}).then(function(response) {
                    let res = response.data,
                        data = res.data;

                    if (data.UploadAuth || data.signature) {
                        that.upload(data)
                    } else {
                        that.closeSubmitLoading();
                        message.error('视频上传失败，请重试');
                    }
                })
            }
        },

        showSubmitLoading() {
            this.saveLoading = true;
            this.submitLoading = this.$loading({
                lock: true,
                text: '保存中',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.8)'
            });
        },

        closeSubmitLoading() {
            this.saveLoading = false;
            this.submitLoading.close();
            this.submitLoading = null;
        },

        upload(param) {
            var that = this,
                uploadAuth = param.UploadAuth,  //上传凭证
                uploadAddress = param.UploadAddress,
                fileId = param.fileId;
            var uploader = new AliyunUpload.Vod({
                userId: that.config.video_ali_userid,
                // 开始上传
                'onUploadstarted': function (uploadInfo) {
                    uploader.setUploadAuthAndAddress(uploadInfo, uploadAuth, uploadAddress, fileId);
                },
                // 文件上传进度
                'onUploadProgress': function (uploadInfo, totalSize, loadedPercent) {
                    var processNum = Math.ceil(loadedPercent * 100.00);
                    // layer.msg('视频上传中，上传进度：' + processNum + '%', {icon: 16, shade: 0.1, time: 5000});
                    // TODO 进度条显示
                    console.log("onUploadProgress:file:" + uploadInfo.file.name + ", fileSize:" + totalSize + ", percent:" + processNum + "%");
                },
                // 文件上传失败
                'onUploadFailed': function (uploadInfo, code, message) {
                    that.closeSubmitLoading();
                    message.error('视频上传失败' + ",code:" + code + ", message:" + message);
                    console.log("onUploadFailed: file:" + uploadInfo.file.name + ",code:" + code + ", message:" + message);
                },
                // 文件上传完成
                'onUploadSucceed': function (uploadInfo) {
                    console.log("onUploadSucceed: " + uploadInfo.file.name + ", endpoint:" + uploadInfo.endpoint + ", bucket:" + uploadInfo.bucket + ", object:" + uploadInfo.object);
                    that.file = null;
                    that.ruleForm.videoid = param.vid;
                    that.submit();
                    return true;
                },
                // 上传凭证超时
                'onUploadTokenExpired': function () {
                    console.log("onUploadTokenExpired");
                    console.log("resume upload with token.");
                },
                //取消上传
                'onUploadCanceled': function (uploadInfo) {
                    console.log("onUploadCanceled:file:" + uploadInfo.file.name);
                }
            });
            var userData = '{"Vod":{"UserData":{"IsShowWaterMark":"false","Priority":"7"}}}';
            //一次只上传一个视频文件
            uploader.addFile(that.file, null, null, null, userData);
            //开始上传
            uploader.startUpload();
        },

        submit() {
            let that = this,
                ruleForm = that.ruleForm,
                formData = new FormData();

            $.each(ruleForm, function(key, value){
                if (key != 'pic_n') {
                    formData.append(key, value);
                }
            });

            httpPost('m=yunying&c=yingxiao_video&a=save', formData, {hideloading: true}).then(function (response) {
                let res = response.data;

                if (res.error > 0) {
                    that.closeSubmitLoading();
                    message.error(res.msg);
                } else {
                    that.closeSubmitLoading();
                    that.drawerAdd = false;
                    message.success(res.msg, function () {
                        that.getList();
                    })
                }
            })
        },

        bindRec(val, data) {
            let that = this;

            httpPost('m=yunying&c=yingxiao_video&a=checkRec', {id: data.id, rec: val}).then(function (response) {
                let res = response.data;

                if (res.error > 0) {
                    message.error(res.msg);
                }
            })
        },

        bindDisplay(val, data) {
            let that = this;

            this.detail = data;

            httpPost('m=yunying&c=yingxiao_video&a=checkDisplay', {id: data.id, display: val}).then(function (response) {
                let res = response.data;

                if (res.error > 0) {
                    message.error(res.msg);
                }
            })
        },

        showSort(scope) {
            let index = scope.$index;
            let row = scope.row;
            let column = scope.column;
            this.oldData = JSON.parse(JSON.stringify(row));
            let copyRow = JSON.parse(JSON.stringify(row));
            copyRow[column.property + "isShow"] = true;
            this.$set(this.list, index, copyRow);
            this.$nextTick(() => {
                let ref = column.property + index;
                $("#" + ref).focus();
            });
        },

        editSort(scope) {
            if (this.oldData == null) {
                return false;
            }
            let index = scope.$index;
            let row = scope.row;
            let column = scope.column;
            let copyRow = JSON.parse(JSON.stringify(row));
            copyRow[column.property + "isShow"] = false;
            this.$set(this.list, index, copyRow);
            if (row[column.property] === this.oldData[column.property]) {
                return false;
            }
            let _this = this;
            let sendData = {id: row.id};
            sendData[column.property] = row[column.property];
            httpPost('m=yunying&c=yingxiao_video&a=upSort', sendData).then(function (response) {
                let res = response.data;
                if (res.error > 0) {
                    message.error(res.msg);
                }
                _this.oldData = null;
                _this.getList();
            }).catch(function (error) {
                console.log(error);
            });
        },

        preview(data) {
            window.open(this.sy_weburl + '/company/index.php?c=show&id=' + data.uid);
        },

        openLog(data) {
            this.detail = data;
            this.drawerLog = true;
        },
    },
};
</script>
<style scoped>
.moduleElHight .moduleElTable {
    height: calc(100% - 109px);
}
</style>