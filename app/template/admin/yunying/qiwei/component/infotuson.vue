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
                        <div class="TableTite">应用AgentId</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="wx_qy_agentid" placeholder="请输入内容"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“应用管理”<i class="el-icon-right"></i>“企业应用”<i class="el-icon-right"></i>“自建”，点进“消息推送”应用，即可看到agentId。</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">应用Secret</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="wx_qy_secret" placeholder="请输入内容"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“应用管理”<i class="el-icon-right"></i>“企业应用”<i class="el-icon-right"></i>“自建”，点进“消息推送”应用，即获取查看Secret。</span>
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
                wx_qy_agentid: '',
                wx_qy_secret: '',
            }
        },
        methods: {
            configsave() {
                if (this.save) {
                    this.save({
                            wx_qy_agentid: this.wx_qy_agentid,
                            wx_qy_secret: this.wx_qy_secret
                        }
                    )
                }
            }
        },
    };
</script>
<style scoped></style>