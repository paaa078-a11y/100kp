<template>
    <div class="moduleElHight">
        <div class="xcxseoLinks">
            <a href="" class="acure">首页</a>
            <a href="">找工作</a>
            <a href="">找人才</a>
            <a href="">兼职</a>
            <a href="">公司</a>
            <a href="">新闻公告</a>
            <a href="">招聘会</a>
            <a href="">店铺</a>
            <a href="">普工</a>
            <a href="">地图</a>
            <el-link type="primary" href="" icon="el-icon-document-add">主要链接</el-link>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="名称" width="80">
                </el-table-column>
                <el-table-column prop="name" label="SEO标识符">
                </el-table-column>
                <el-table-column prop="gzcs" label="标题">
                </el-table-column>
                <el-table-column prop="cjtime" label="更新时间	">
                </el-table-column>
                <el-table-column label="操作" width="180">
                    <template slot-scope="">
                        <el-button size="mini" @click="drawer2 = true">详情</el-button>
                        <el-button size="mini" @click="drawer2 = true">修改</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div> <el-checkbox v-model="checked">全选</el-checkbox> <el-button>批量删除</el-button></div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="提示" :visible.sync="drawer" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="300px">
                <div>
                    <i class="el-icon-warning"></i> 此操作将永久删除该信息, 是否继续
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawer = false">取 消</el-button>
                    <el-button type="primary" @click="drawer = false">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-dialog title="企业微信通知设置" :visible.sync="drawer2" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="540px">
                <div>
                    <div class="wxsettip_small ">
                        企业ID
                        <el-tooltip class="item" effect="dark" content="在企业微信管理端->“我的企业”－“企业信息”下查看“企业ID”（需要有管理员权限）"
                            placement="right-start"><i class="el-icon-warning-outline"></i> <el-button>右上</el-button>
                        </el-tooltip>
                    </div>
                    <el-input v-model="input" placeholder="wwf7201607f63ee1d4"></el-input>
                    <div class="wxsettip_small ">
                        应用AgentId
                        <el-tooltip class="item" effect="dark" content="在企业微信管理端->“应用管理”->“应用”->“自建”，点进某个应用，即可看到agentid"
                            placement="right-start">
                            <i class="el-icon-warning-outline"></i>
                            <el-button>右上</el-button>
                        </el-tooltip>
                    </div>
                    <el-input v-model="input" placeholder="1000003"></el-input>
                    <div class="wxsettip_small ">
                        应用SCERET
                        <el-tooltip class="item" effect="dark" content="在企业微信管理端->“应用管理”->“应用”->“自建”，点进某个应用，即可看到。"
                            placement="right-start">
                            <i class="el-icon-warning-outline"></i>
                            <el-button>右上</el-button>
                        </el-tooltip>
                    </div>
                    <el-input v-model="input" placeholder="acBp9nze21qPPITtv8WlAbXxutukvFYOpm_PnES9nLg"></el-input>


                    <div class="wxsettip">要想收到企业微信通知，需要后台管理员绑定企业微信，并在对应企业微信应用中将该用户加入可见范围</div>
                    <div class="wxsettip">企业微信绑定需要在上述AgentId对应应用里设置授权域名 <el-link icon="el-icon-edit">绑定企业微信</el-link> </div>
                    <div class="wxsettip">可见范围设置：企业微信管理端->“应用管理”->点进上述应用AgentId对应应用，即可编辑可见范围 </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawer2 = false">取 消</el-button>
                    <el-button type="primary" @click="drawer2 = false">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    data: function () {
        return {
            loading: false,
            emptytext: '暂无数据',
            
            input: '',
            input3: '',
            select: '',
            value: true,
            value1: '',
            checked: '',
            drawer: false,
            drawer2: false,
            currentPage4: 4,
            items: [
                { type: '', label: '意向' },
                { type: 'success', label: '老油条' },
                { type: 'info', label: '员工' },
                { type: 'danger', label: '标签四' },
                { type: 'warning', label: '标签五' }
            ],
            tableData: [
                {
                    id: '01',
                    name: '饭店桌号贴',
                    gzcs: '123',
                    cjtime: '2023.05.31',
                    dqtime: '2023.05.31',
                }, {
                    id: '02',
                    name: '中央商场电梯门',
                    gzcs: '38',
                    cjtime: '2023.05.31',
                    dqtime: '2023.05.31',
                }, {
                    id: '03',
                    name: '万达电梯广告',
                    gzcs: '5',
                    cjtime: '2023.05.31',
                    dqtime: '2023.05.31',
                },
            ]
        }
    },

    mounted() {

    },
    methods: {
        handleSizeChange(val) {
            console.log(`每页 ${val} 条`);
        },
        handleCurrentChange(val) {
            console.log(`当前页: ${val}`);
        }
    },
};
</script>
<style scoped>
.moduleSeachmore {
    padding: 0px;
}

.moduleSeachs {
    padding: 0px 0px 12px 0px;
    width: 100%;
}

.moduleElTable {
    padding: 0;
    margin: 0;
    height: calc(100% - 136px);
    width: 100%;
}

.tableSeachInptsmalltwo {
    margin-bottom: 0px;
    margin-right: 12px;
}

.tableSeachInptsmalltwo .el-input__inner {
    height: 32px;
    line-height: 32px;
    width: 260px;
    padding: 0px 5px;
}

.el-dialog__body {
    padding: 0px 20px;
}
</style>