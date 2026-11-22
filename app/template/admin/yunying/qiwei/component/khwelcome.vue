<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入欢迎语名称关键字" size="small" v-model="keyword" class="input-with-select" clearable></el-input>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
            <div class="moduleSeachButn">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="add({id: ''})">新增客户欢迎语</el-button>
            </div>
        </div>
        <div class="moduleElTable" style="height: calc(100% - 109px);">
            <el-table :data="tableData" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @selection-change="handleSelectionChange" ref="multipleTable" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="ID" width="60">
                </el-table-column>
                <el-table-column prop="name" label="欢迎语名称" min-width="240">
                </el-table-column>
                <el-table-column prop="msgtype_n" label="欢迎语类型" width="200">
                </el-table-column>
                <el-table-column prop="u_name" label="使用成员" width="200">
                </el-table-column>
                <el-table-column prop="create_time_n" label="添加时间" width="180">
                </el-table-column>
                <el-table-column prop="up_time_n" label="更新时间" width="180">
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="add(scope.row)">编辑</el-button>
                            <el-button size="mini" type="danger" @click="delrow(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulecz modulePagButn">
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button size="mini" @click="delAllBottom">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 新增、修改弹窗 -->
        <div class="modluDrawer">
            <el-drawer :title="info.id ? '客户欢迎语修改' : '客户欢迎语添加'" :visible.sync="draweradd"
                       :modal-append-to-body="false" :show-close="true" :with-header="true" size="45%">
                <div class="drawerModlue">
                    <div class="tableDome_tip tableDoAlert">
                        <div class="shiTopAllTips">
                            <h3>添加新客户欢迎语素材</h3>
                            <span>1、为了保证用户体验，欢迎语仅在添加客户后20秒内调用，且只可调用一次。<br></span>
                            <span>2、如果企业已经在管理端为相关成员配置了可用的欢迎语，则此接口设置欢迎语不会发送到客户。<br></span>
                            <span>3、欢迎语内容：文本消息和附件消息不能同时为空。<br></span>
                            <span>4、欢迎语内容添加小程序的情况，小程序封面有效期为三天，超过三天需要重新编辑上传。<br></span>
                        </div>
                    </div>
                    <div class="drawerModInfo drawerModInfoOne">
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>欢迎语名称</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input v-model="info.name" placeholder="请输入欢迎语名称"></el-input>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>选择员工</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-select :key="selkey" v-model="info.userid_arr" multiple filterable placeholder="请选择">
                                    <el-option v-for="item in userarr" :key="item.value" :label="item.label"
                                               :value="item.value"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>文本消息</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input ref="contentIpt" type="textarea" rows="6" show-word-limit maxlength="1200" v-model="info.content"
                                          placeholder="请输入文本消息"></el-input>
                            </div>
                            <div class="drawerModTips">
                                <span @click="copyName('{用户昵称}');" style="background-color: aliceblue;color:#2778F8;padding:2px 10px;border-radius:4px; margin-top: 5px; display: inline-block;">插入昵称</span>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>附件消息</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-radio-group v-model="info.msgtype">
                                    <el-radio label="1">图片</el-radio>
                                    <el-radio label="2">链接</el-radio>
                                    <el-radio label="3">小程序</el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '1'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-upload class="upload-demo" :action="''" :auto-upload="false"
                                           :show-file-list="false" :on-change="imgChange" :accept="pic_accept">
                                    <el-button size="small" type="primary" plain icon="el-icon-plus">上传图片</el-button>
                                </el-upload>
                                <img style="width: 100px; height: 100px;padding-left: 5px;" v-if="info.pic_url"
                                     :src="info.pic_url">
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '2'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="text" show-word-limit maxlength="600" v-model="info.link_url"
                                          placeholder="请输入图文消息链接"></el-input>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '2'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="text" show-word-limit maxlength="40" v-model="info.link_title"
                                          placeholder="请输入图文消息标题"></el-input>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '2'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="text" show-word-limit maxlength="160" v-model="info.link_desc"
                                          placeholder="请简单描述图文信息内容"></el-input>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '2'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-upload class="upload-demo" :action="''" :auto-upload="false"
                                           :show-file-list="false" :on-change="linkpicChange" :accept="pic_accept">
                                    <el-button size="small" type="primary" plain icon="el-icon-plus">上传图片</el-button>
                                </el-upload>
                                <img style="width: 140px; height: 140px;padding-left: 5px;" v-if="info.link_picurl"
                                     :src="info.link_picurl">
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="链接分享图，尺寸：300 * 300" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '3'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="关联到企业的小程序应用 , 默认使用招聘系统小程序 " type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '3'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="text" show-word-limit maxlength="40" v-model="info.mini_title"
                                          placeholder="请输入小程序标题"></el-input>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '3'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="text" show-word-limit maxlength="40" v-model="info.mini_appid"
                                          placeholder="请输入小程序APPID"></el-input>
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="小程序appid（可以在微信公众平台上查询）" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '3'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input type="text" show-word-limit maxlength="40" v-model="info.mini_page"
                                          placeholder="请输入小程序路径；例：/pages/index/index"></el-input>
                            </div>
                            <div class="drawerModTips">
                                <el-alert type="info" show-icon :closable="false">
                                    更多路径查询
                                    <el-link type="primary" href="https://www.yuque.com/cx2tdz/up423s/euc2qk"
                                             target="_blank">查询路径
                                    </el-link>
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis" v-if="info.msgtype == '3'">
                            <div class="drawerModTite">
                                <span></span>
                            </div>
                            <div class="drawerModInpt">
                                <el-upload class="upload-demo" :action="''" :auto-upload="false"
                                           :show-file-list="false" :on-change="minipicChange" :accept="pic_accept">
                                    <el-button size="small" type="primary" plain icon="el-icon-plus">上传图片</el-button>
                                </el-upload>
                                <img style="width: 208px; height: 167px;padding-left: 5px;" v-if="info.mini_picurl_n"
                                     :src="info.mini_picurl_n">
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="小程序封面，尺寸：520 * 416" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                    </div>
                    <div class="setBasicButn" style="border: none;">
                        <el-button v-if="info.send == '1'" type="primary" size="medium" @click="draweradd = false">返回</el-button>
                        <el-button v-if="info.send != '1'" type="primary" size="medium" @click="save"
                                   :disabled="saveLoading">提交</el-button>
                    </div>
                </div>
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
            saveLoading: false,

            checkedAll: false,
            selectedItem:[],
            tableData: [],
            currentPage: 1,
            perPage: 0,
            pageSizes: [],
            total: 0,
            keyword: '',
            draweradd: false,
            userarr: [],
            info: {},
            piclist: [],
            linkpiclist: [],
            minipiclist: [],
            selkey: '',
            def_xcxappid: '',
            def_xcxpath: '',
            islook: false,
			prevPage:0
        }
    },
    mounted() {

    },
    methods: {
        imgChange(file) {
            var tmp = deepClone(this.info)
            // 预览文件处理
            tmp.pic_url = URL.createObjectURL(file.raw);
            // 复刻文件信息
            this.piclist[0] = file.raw;
            this.info = tmp
        },
        linkpicChange(file) {
            var tmp = deepClone(this.info)
            // 预览文件处理
            tmp.link_picurl = URL.createObjectURL(file.raw);
            // 复刻文件信息
            this.linkpiclist[0] = file.raw;
            this.info = tmp
        },
        minipicChange(file) {
            var tmp = deepClone(this.info)
            // 预览文件处理
            tmp.mini_picurl_n = URL.createObjectURL(file.raw);
            // 复刻文件信息
            this.minipiclist[0] = file.raw;
            this.info = tmp
        },
        copyName(txt) {
            this.info.content += txt
            this.$refs.contentIpt.focus();
        },
        add(row) {
            this.draweradd = true
            if (row.id) {
                this.info = row
                if (!this.info.mini_appid) {
                    this.info.mini_appid = this.def_xcxappid
                }
                if (!this.info.mini_page) {
                    this.info.mini_page = this.def_xcxpath
                }
            } else {
                this.info = {id: '', content: '', userid_arr: [], mini_appid: this.def_xcxappid, mini_page: this.def_xcxpath}
            }
            this.getWorkUser()
            this.selkey = Math.random()
        },
        getWorkUser() {
            let that = this;
            httpPost('m=yunying&c=workwx_push&a=getWorkUser', {}, {hideloading: true}).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.userarr = res.data
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        handleSelectionChange(val) {
            this.selectedItem = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.selectedItem.push(item.id);
                });
            }
            if (_this.selectedItem.length == 0) {
                _this.checkedAll = false;
            } else {
                if (_this.selectedItem.length == _this.tableData.length) {
                    _this.checkedAll = true;
                } else {
                    _this.checkedAll = false;
                }
            }
        },
        selectAllBottom(value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        handleSizeChange(val) {
            this.perPage = val;
            this.getList()
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.getList()
        },
        search() {
            this.currentPage = 1;
            this.getList();
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                pageSize: that.perPage
            }
            if (that.keyword) {
                params.keyword = that.keyword
            }
            that.loading = true;
            httpPost('m=yunying&c=workwx_push&a=welcomeList', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.perPage = parseInt(res.data.perPage)
                    that.pageSizes = res.data.pageSizes
                    that.total = parseInt(res.data.total)
                    that.def_xcxappid = res.data.sy_xcxappid
                    that.def_xcxpath = res.data.sy_xcxpath
                    if(that.prevPage != that.page){
                        that.prevPage = that.page;
                        that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.tableData.length === 0) {
                        that.dataText = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        save() {
            var that = this
            if (!that.info.name) {
                message.warning('请输入欢迎语名称');
                return false;
            }
            if (that.info.userid_arr.length == 0) {
                message.warning('请选择员工');
                return false;
            }
            if (that.info.msgtype == '' && that.info.content == '') {
                message.warning('文本消息和附件消息不能同时为空');
                return false;
            }
            if (that.info.msgtype == '1' && !that.info.pic_url && that.piclist.length == 0) {
                message.warning('附件图片不能为空');
                return false;
            }
            if (that.info.msgtype == '2' && (that.info.link_url == '' || that.info.link_title == '')) {
                message.warning('附件链接URL、链接标题不能为空');
                return false;
            }
            if (that.info.msgtype == '3' && that.info.mini_title == '') {
                message.warning('请输入小程序标题');
                return false;
            }
            if (that.info.msgtype == '3' && !that.info.mini_picurl && that.minipiclist.length == 0) {
                message.warning('请上传小程序封面');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            let params = that.info;
            var formData = new FormData();
            Object.keys(params).forEach((key) => {
                if (Array.isArray(params[key])) {
                    params[key].forEach((v) => {
                        formData.append(key + '[]', v);
                    });
                } else {
                    formData.append(key, params[key]);
                }
            });
            if (that.piclist.length) {
                formData.append('pic[]', this.piclist[0])
            }
            if (that.linkpiclist.length) {
                formData.append('linkpic[]', this.linkpiclist[0])
            }
            if (that.minipiclist.length) {
                formData.append('minipic[]', this.minipiclist[0])
            }
            httpPost('m=yunying&c=workwx_push&a=saveWelcome', formData).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.draweradd = false
                    that.keyword = ''
                    that.getList()
                    message.success(res.msg, function(){
                        that.saveLoading = false;
                    });
                } else {
                    that.saveLoading = false;
                    message.error(res.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        delrow(id) {
            delConfirm(this, id, this.delete);
        },
        delAllBottom(){
            if (!this.selectedItem.length) {
                message.error('请选择要删除的数据');
                return false;
            }
            delConfirm(this, this.selectedItem, this.delete);
        },
        async delete(id) {
            let that = this;
            let params = {
                del: id
            };
            httpPost('m=yunying&c=workwx_push&a=delWelcome', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success('操作成功', function(){
                        that.getList();
                    });
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
    },
};
</script>
<style scoped></style>