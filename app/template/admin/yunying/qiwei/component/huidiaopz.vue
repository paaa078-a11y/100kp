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
                        <div class="TableTite">回调URL</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="wx_contact_url" placeholder="请输入内容"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“客户与上下游”<i class="el-icon-right"></i>“客户联系”<i class="el-icon-right"></i>“客户”下，点击“API”，设置接收事件服务器。</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">验证Token</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="wx_contact_token" placeholder="请输入内容"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“客户与上下游”<i class="el-icon-right"></i>“客户联系”<i class="el-icon-right"></i>“客户”下，点击“API”，设置接收事件服务器。</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">验证EncodingAESKey</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-input v-model="wx_contact_EncodingAESKey" placeholder="请输入内容"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>在</span>
                            <a href="https://work.weixin.qq.com" target="_blank">企业微信管理后台</a>
                            <span><i class="el-icon-right"></i>“客户与上下游”<i class="el-icon-right"></i>“客户联系”<i class="el-icon-right"></i>“客户”下，点击“API”，设置接收事件服务器。</span>
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
                wx_contact_url: '',
                wx_contact_token: '',
                wx_contact_EncodingAESKey: '',
            }
        },
        methods: {
            configsave() {
                if (this.save) {
                    this.save({
                            wx_contact_url: this.wx_contact_url,
                            wx_contact_token: this.wx_contact_token,
                            wx_contact_EncodingAESKey: this.wx_contact_EncodingAESKey
                        }
                    )
                }
            }
        },
    };
</script>
<style scoped></style>