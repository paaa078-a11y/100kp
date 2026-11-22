<template>
    <div class="shzTableConte">
        <div class="shzTableFrom">
            <div class="moduleSeachInpt">
                <el-date-picker v-model="time" size="small" type="daterange" range-separator="至" start-placeholder="开始日期"
                    end-placeholder="结束日期" @change="shouzhiFun">
                </el-date-picker>
            </div>
            <div class="moduleSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="shouzhiFun">查询</el-button>
            </div>
        </div>
        <div class="shzTableNeir">
            <el-table :data="tableData" border :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
                style="width: 100%" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{ dataText }}</p>
                </template>
                <el-table-column prop="times" label="日期">
                </el-table-column>
                <el-table-column prop="maosr" label="毛收入">
                </el-table-column>
                <el-table-column prop="zctx" label="支出/提现">
                </el-table-column>
                <el-table-column prop="jinsr" label="净收入">
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        time: Array
    },
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            input: '',
            value: '',
            tableData: [],
            uri: "m=yunying&c=",
        }
    },
    created() {
        this.shouzhiFun();
    },
    mounted() {
    },
    methods: {
        async shouzhiFun() {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=shouzhi';

            let sendData = {
                'isList': 1,
                'time': _this.time
            }
            _this.loading = true;
            let response = await httpPost(url, sendData)
            let res = response.data;
            if (res.error == 0) {
                _this.tableData = res.data.list;
                _this.loading = false;
                if (_this.tableData.length === 0) {
                    _this.dataText = "暂无数据";
                }
            }
        },
    },
};
</script>
<style scoped>
.moduleSeachInpt .el-button {
    margin-left: 0;
}
</style>