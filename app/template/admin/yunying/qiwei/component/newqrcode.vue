<template>
    <div class="drawerModlue">
        <div class="moduleTable" style="max-height: calc(100% - 80px);">
            <div class="tableDome_tip tableDoAlert" style="margin-bottom: 12px;">
                <h3>配置客户群「加入群聊」方式 —— 客户群活码</h3>
                <div class="shiTopAllTips">
                    <span>1、企业可通过此接口为具有客户联系功能的成员生成专属的群二维码。</span>
                    <span>2、客户通过扫描二维码或点击小程序上的按钮，即可加入特定的客户群。</span>
                    <span>3、如果配置的是小程序按钮，并且开启使用，小程序职位详情页广告位将会替换成该插件。</span>
                    <span style="font-weight: bold;">注：通过该API添加的配置不会在管理端进行展示。</span>
                </div>
            </div>
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="380">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">活码名称</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-input v-model="info.name" placeholder="请输入活码名称"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>活码名称</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">入群场景</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-radio-group v-model="info.scene">
                                <el-radio label="2">二维码</el-radio>
                                <el-radio label="1">小程序</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>选择小程序场景时，可以通过微信小程序职位详情页面点击入群</span>
                        </div>
                    </td>
                </tr>
                <tr v-if="info.scene == '1'">
                    <td>
                        <div class="TableTite">开启应用</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-switch v-model="info.xcx_use"></el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>开启应用，其他客户群活码小程序插件会立即关闭</span>
                        </div>
                    </td>
                </tr>
                <tr v-if="info.scene == '1'">
                    <td>
                        <div class="TableTite">应用图标</div>
                    </td>
                    <td>
                        <div class="TableSelect" style="display: flex; align-items: center;">
                            <el-upload class="upload-demo" :accept="pic_accept" :action="''" :auto-upload="false" :show-file-list="false" :on-change="iconChange">
                                <el-button size="small" type="primary" plain icon="el-icon-plus">上传图标</el-button>
                            </el-upload>
                            <img style="width: 100px; height: 100px;padding-left: 5px;" v-if="info.xcx_icon" :src="info.xcx_icon" />
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span></span>
                        </div>
                    </td>
                </tr>
                <tr v-if="info.scene == '1'">
                    <td>
                        <div class="TableTite">应用标题</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-input v-model="info.xcx_title" placeholder="例如：微信求职群"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>最多15个字。</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">入群备注</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-input v-model="info.remark" placeholder="请输入入群备注"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>入群联系方式的备注信息，用于助记</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">对应群聊</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-select v-model="info.chat_id_list_arr" filterable multiple placeholder="请选择">
                                <el-option v-for="item in groupArr" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>入群联系方式的备注信息，用于助记</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">自建应用</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-radio-group v-model="info.auto_create_room">
                                <el-radio label="1">是</el-radio>
                                <el-radio label="2">否</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>当群满了后，是否自动新建群。</span>
                        </div>
                    </td>
                </tr>
                <tr v-if="info.auto_create_room == 1">
                    <td>
                        <div class="TableTite">群名称前缀</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-input v-model="info.room_base_name" placeholder="请填写入群名称前缀"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>自动建群的群名前缀</span>
                        </div>
                    </td>
                </tr>
                <tr v-if="info.auto_create_room==1">
                    <td>
                        <div class="TableTite">群起始序号</div>
                    </td>
                    <td>
                        <div class="TableSelect">
                            <el-input v-model="info.room_base_id" placeholder="请填写群起始序号"></el-input>
                            <el-alert type="info" :closable="false" style="margin-top: 8px;">
                                <span>群名称前缀 和 群起始序号 两个配合使用，用于指定自动新建群的群名<br></span>
                                <span>例如，假如 群名称前缀 = "销售客服群", 群起始序号 = 10<br></span>
                                <span>那么，自动创建的第一个群，群名为“销售客服群10”；自动创建的第二个群，群名为“销售客服群11”，依次类推</span>
                            </el-alert>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>自动建群的群起始序号</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="save" :disabled="saveLoading">提交</el-button>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            dtlinfo: {
                type: Object,
                default: null
            },
            grouparr: {
                type: Array,
                default: null
            },
        },
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                saveLoading: false,

                info: {},
                iconslist: [],
                groupArr: [],
                selected: []
            }
        },
        mounted() {

        },
        watch: {
            dtlinfo: {
                handler(newName) {
                    this.info = newName
                },
                deep: true,
                immediate: true
            },
            grouparr(data) {
                this.groupArr = data
            }
        },
        methods: {
            iconChange(file) {
                var tmp = deepClone(this.info)
                // 预览文件处理
                tmp.xcx_icon = URL.createObjectURL(file.raw);
                tmp.new_pic = 1;
                // 复刻文件信息
                this.iconslist[0] = file.raw;
                this.info = tmp
            },
            save() {
                var that = this
                if (!that.info.name) {
                    message.warning('请输入活码名称');
                    return false;
                }
                if (that.info.chat_id_list_arr.length == 0) {
                    message.warning('请选择对应群聊');
                    return false;
                }
                if (that.info.auto_create_room == '1' && !that.info.room_base_name) {
                    message.warning('请输入群名称前缀');
                    return false;
                }
                if (that.info.auto_create_room == '1' && !that.info.room_base_id) {
                    message.warning('请输入群起始序号');
                    return false;
                }

                if (that.saveLoading) {
                    return false;
                }
                that.saveLoading = true;

                var params = new FormData();
                for(let i in that.info){
                    params.append(i,that.info[i])
                }
                if (this.iconslist.length) {
                    params.append('xcxicon[]', this.iconslist[0])
                }
                httpPost('m=yunying&c=workwx_qrcode&a=save', params).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.$parent.$parent.draweradd = false
                        that.$parent.$parent.getList()
                        message.success(res.msg, function () {
                            that.saveLoading = false;
                        })
                        // that.$emit('update:draweradd', false)
                    } else {
                        that.saveLoading = false;
                        message.error(res.msg)
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            }
        },
    };
</script>
<style scoped>
    .drawerModInfo::-webkit-scrollbar {
        display: none;
    }

    .el-dialog-s {
        z-index: 11;
    }
</style>