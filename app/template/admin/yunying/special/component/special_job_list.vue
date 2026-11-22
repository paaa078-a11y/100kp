<template>
    <div class="jobspecial_box">
        <template v-if="type == 1">
            <special_job_list_component :id="id" :type="type" :rating-data="ratingData" :edu-data="eduData" :exp-data="expData" :counter="counter" @child-event="handleChildEvent"></special_job_list_component>
        </template>
        <template v-else-if="type == 2">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="自动匹配职位" name="first">
                    <special_job_list_component :id="id" :type="type" :rating-data="ratingData" :edu-data="eduData" :exp-data="expData" :counter="counter" @child-event="handleChildEvent"></special_job_list_component>
                </el-tab-pane>
                <el-tab-pane label="手动添加职位" name="second">
                    <special_job_list_component :id="id" :type="type" :is_manual="1" :rating-data="ratingData" :edu-data="eduData" :exp-data="expData" :counter="counter" @child-event="handleChildEvent"></special_job_list_component>
                </el-tab-pane>
            </el-tabs>
        </template>
    </div>
</template>

<script>
    module.exports = {
        props: {
            //special_job.id
            id: {type: [String, Number], default: 0},
            type: {type: [String, Number], default: 0},
            ratingData: {type: [Array], default: []},//会员等级
            eduData: {type: [Array], default: []},//学历
            expData: {type: [Array], default: []},//工作经验
        },
        data: function () {
            return {
                pytoken: localStorage.getItem("pytoken"),
                activeName: 'first',
                counter: 0,//监听值变化，刷新列表
            }
        },
        created: function () {
        },
        methods: {
            handleClick(tab, event) {
                // console.log(tab, event);
            },
            handleChildEvent() {
                this.counter++;
                this.$emit("child-event");
            }
        },
        components: {
            'special_job_list_component': httpVueLoader('./special_job_list_component.vue'),
        },
    };
</script>
<style scoped>
    .moduleElTable {margin-top: 0;padding: 0;width: 100%;height: calc(100% - (60px + 50px + 12px));}
    .jobspecial_box {overflow: hidden;position: relative;width: 100%;height: 100%;}
    .jobspecial_box .el-tabs {overflow: hidden;position: relative;width: 100%;height: 100%;}
    .jobspecial_box .el-tabs__content {overflow: hidden;position: relative;width: 100%;height: calc(100% - 60px);}
    .jobspecial_box .el-tab-pane {overflow: hidden;position: relative;width: 100%;height: 100%;}
</style>