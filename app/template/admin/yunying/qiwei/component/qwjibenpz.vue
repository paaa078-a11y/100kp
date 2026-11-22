<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="180">名称</th>
                    <th width="460">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">企业微信ID</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="wx_qy_corpid" placeholder="请输入企业微信ID"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“我的企业”<i class="el-icon-right"></i>“企业信息”下查看“企业ID”（需要有管理员权限）。</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">通讯录Secret</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="sy_contact_secret" placeholder="请输入通讯录Secret"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“安全与管理”<i class="el-icon-right"></i>“管理工具”<i class="el-icon-right"></i>“通讯录管理”<i class="el-icon-right"></i>“通讯录同步”下查看（需要开启API接口同步）。</span>
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="configsave">提交</el-button>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: {
            save: {
                type: Function,
                default: null
            }
        },
        data: function () {
            return {
                wx_qy_corpid: '',
                sy_contact_secret: '',
            }
        },
        mounted() {

        },
        methods: {
            configsave() {
                if (this.save) {
                    this.save({
                            wx_qy_corpid: this.wx_qy_corpid,
                            sy_contact_secret: this.sy_contact_secret
                        }
                    )
                }
            }
        },
    };
</script>
<style scoped></style>