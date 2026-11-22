<!--放弃客户-->
<template>
    <div class="drawerModlue">
        <div class="drawerModInfo">
			<el-alert title="重要提示：客户被放弃后将不能拥有客户操作权限" type="warning"></el-alert>
			<div class="drawerModLis">
			    <div class="drawerModInpt">
					<el-input type="textarea" v-model="remark" :rows="3" placeholder="请阐述放弃原因"></el-input>
				</div>
			</div>
        </div>
		<div class="setBasicButn" style="border: none;">
			<el-button type="primary" size="medium" @click="saveinfo" :loading="saveloading">确认放弃</el-button>
		</div>
    </div>
</template>

<script>

module.exports = {
    props: {
        cuid: {
            type: [String, Number],
            default: ''
        },
    },
    watch: {
        cuid: {
            handler(val, oldVal) {
                this.uid = val;
            },
            immediate: true,
        },
    },
    data: function () {
        return {
            uid:'',
			remark:'',
			saveloading:false,
		}
    },
	mounted() {
		
	},
	methods: {
        
		async saveinfo() {
			if (this.uid == ''){
				message.warning('请选择需要放弃的客户！');
				return false;
			}
			if (this.remark == '') {
				message.warning('请填写放弃客户的说明备注！');
				return false;
			}
							
			var param = {
				uid: this.uid,
				remark: this.remark,
			};
			this.saveloading = true;
			httpPost('m=crm&c=crm_my_customer&a=giveUp', param).then((result) => {
				this.saveloading = false;
				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, () => {
		
						this.$emit("close-update");
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		
    },
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
    display: none;
}
.drawerModlue{
    overflow: hidden;
    position: relative;
    width: 100%;
    height: 100%;
}
.drawerModInfo{
    overflow-y: auto;
    height: calc(100% - 80px);
}
</style>