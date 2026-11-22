<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="180">名称</th>
                    <th width="260">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">默认LOGO</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept"
                                       list-type="picture"
                                       action=""
                                       :auto-upload="false"
                                       :on-change="handleChangeLogo"
                                       :show-file-list="false">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="120" height="120"
                                     style="padding-left: 5px;"
                                     v-if="ruleForm.sy_px_icon_n" :src="ruleForm.sy_px_icon_n"/>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>企业没有上传logo时展示的图片</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">默认课程图片</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept"
                                       list-type="picture"
                                       action=""
                                       :auto-upload="false"
                                       :on-change="handleChangeSubjectIcon"
                                       :show-file-list="false">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="150" height="90"
                                     style="padding-left: 5px;"
                                     v-if="ruleForm.sy_pxsubject_icon_n" :src="ruleForm.sy_pxsubject_icon_n"/>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>未上传课程图片时展示的默认图片</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">默认讲师头像</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept"
                                       list-type="picture"
                                       action=""
                                       :auto-upload="false"
                                       :on-change="handleChangeTeacherIcon"
                                       :show-file-list="false">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="90" height="90"
                                     style="padding-left: 5px;"
                                     v-if="ruleForm.sy_pxteacher_icon_n" :src="ruleForm.sy_pxteacher_icon_n"/>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>未上传讲师头像时展示的默认图片</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">默认企业横幅</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept"
                                       list-type="picture"
                                       action=""
                                       :auto-upload="false"
                                       :on-change="handleChangeBanner"
                                       :show-file-list="false">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="360" height="48"
                                     style="padding-left: 5px;"
                                     v-if="ruleForm.sy_px_banner_n" :src="ruleForm.sy_px_banner_n"/>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>企业未上传横幅时展示的默认图片</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="submit" :disabled="saveLoading">提交</el-button>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                saveLoading: false,

                ruleForm: {}
            }
        },
        mounted() {

        },
        created() {
            this.getData();
        },
        methods: {
            init() {
                this.getData();
            },

            getData() {
                let that = this;
                httpPost('m=user&c=peixun_config&a=logo').then(function (response) {
                    let res = response.data,
                        data = res.data;

                    that.ruleForm = data.config;
                })
            },

            // 上传时触发
            handleChangeLogo(file, fileList) {
                this.$set(this.ruleForm, 'sy_px_icon', file.raw);
                this.$set(this.ruleForm, 'sy_px_icon_n', file.url);
            },
            handleChangeSubjectIcon(file, fileList) {
                this.$set(this.ruleForm, 'sy_pxsubject_icon', file.raw);
                this.$set(this.ruleForm, 'sy_pxsubject_icon_n', file.url);
            },
            handleChangeTeacherIcon(file, fileList) {
                this.$set(this.ruleForm, 'sy_pxteacher_icon', file.raw);
                this.$set(this.ruleForm, 'sy_pxteacher_icon_n', file.url);
            },
            handleChangeBanner(file, fileList) {
                this.$set(this.ruleForm, 'sy_px_banner', file.raw);
                this.$set(this.ruleForm, 'sy_px_banner_n', file.url);
            },

            submit() {
                let that = this,
                    ruleForm = that.ruleForm,
                    formData = new FormData(),
                    imagePath = ['sy_px_icon_n', 'sy_pxsubject_icon_n', 'sy_pxteacher_icon_n', 'sy_px_banner_n'];

                if (that.saveLoading) {
                    return false;
                }
                that.saveLoading = true;

                $.each(ruleForm, function (key, value) {
                    if (imagePath.indexOf(key) === -1) {
                        formData.append(key, value);
                    }
                });

                httpPost('m=user&c=peixun_config&a=logoSave', formData).then(function (response) {
                    let res = response.data;

                    that.saveLoading = false;
                    if (res.error > 0) {
                        message.error(res.msg);
                    } else {
                        message.success(res.msg, function () {
                            that.$set(that.ruleForm, 'sy_haibao_web_logo', '');
                        });
                    }
                })
            },
        },
    };
</script>
<style scoped></style>