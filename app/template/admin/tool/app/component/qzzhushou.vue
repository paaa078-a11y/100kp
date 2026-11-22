<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="180">名称</th>
                        <th width="560">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="TableTite">求职助手头像</div>
                        </td>
                        <td>
                            <div class="TableUpload" style="margin-top: 6px;">
                                <el-upload class="upload-demo" action="https://jsonplaceholder.typicode.com/posts/"
                                    :on-preview="handlePreview" :on-remove="handleRemove" :file-list="fileList"
                                    list-type="picture" :accept="pic_accept">
                                    <el-button size="small" type="primary">点击上传</el-button>
                                </el-upload>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>求职助手头像</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">用户头像</div>
                        </td>
                        <td>
                            <div class="TableUpload" style="margin-top: 6px;">
                                <el-upload class="upload-demo" action="https://jsonplaceholder.typicode.com/posts/"
                                    :on-preview="handlePreview" :on-remove="handleRemove" :file-list="fileList"
                                    list-type="picture" :accept="pic_accept">
                                    <el-button size="small" type="primary">点击上传</el-button>
                                </el-upload>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>用户头像</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">求职助手名称</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="input" placeholder="请输入内容"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>求职助手名称</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">打招呼文案</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="input" placeholder="请输入内容"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>打招呼文案</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">求职助手分享封面</div>
                        </td>
                        <td>
                            <div class="TableUpload" style="margin-top: 6px;">
                                <el-upload class="upload-demo" action="https://jsonplaceholder.typicode.com/posts/"
                                    :on-preview="handlePreview" :on-remove="handleRemove" :file-list="fileList"
                                    list-type="picture" :accept="pic_accept">
                                    <el-button size="small" type="primary">点击上传</el-button>
                                </el-upload>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>求职助手分享封面</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">求职助手分享标题</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="input" placeholder="请输入内容"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>求职助手分享标题</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">感兴趣的工作类型</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="input" placeholder="请输入内容"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>感兴趣的工作类型</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium">提交</el-button>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        list: Object
    },
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            value1: true,
            value: '',
            textarea: '',
            radio: '1',
            input: '22',
            fileList: [
                {
                    name: 'food.jpeg',
                    url: 'https://fuss10.elemecdn.com/3/63/4e7f3a15429bfda99bce42a18cdd1jpeg.jpeg?imageMogr2/thumbnail/360x360/format/webp/quality/100'
                },
            ]
        }
    },

    mounted() {

    },
    methods: {
        handleRemove(file, fileList) {
            console.log(file, fileList);
        },
        handlePreview(file) {
            console.log(file);
        },
    },
};
</script>
<style scoped></style>