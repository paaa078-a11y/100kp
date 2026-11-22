<!--转交客户-->
<template>
    <div class="drawerModlue" v-loading="loading">
        <div class="drawerModInfo">
			<div class="drawerModLis">
			    <div class="drawerModInpt">
					<el-select v-model="crm_uid"  placeholder="客户经理" @change="crmSelect">
					    <el-option v-for="item in crmUser" :key="item.uid" :label="item.name" :value="item.uid"></el-option>
					</el-select>
				</div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModInpt">
					<el-input type="textarea" v-model="remark" :rows="3" placeholder="请阐述转交说明"></el-input>
				</div>
			</div>
        </div>
		<div class="setBasicButn" style="border: none;">
			<el-button type="primary" size="medium" @click="saveinfo" :loading="saveloading">提交</el-button>
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
			crmUser:[],
			loading:false,
			crm_uid:'',
			crm_name:'',
			remark:'',
			saveloading:false,
		}
    },
	mounted() {
		this.getInfo()
	},
	methods: {
        
		async getInfo(){
			
			var param = {
				uid:this.cuid,
			};
			this.loading = true;
			httpPost('m=crm&c=crm_my_customer&a=getData',param).then((result)=>{
				this.loading = false;
			    var res = result.data;
			    if (res.error == 0) {
					this.crmUser = res.data.crmUser;
			    }
			}).catch(function(e){
			    console.log(e)
			})
		},
		crmSelect:function(){
			var crmUser = deepClone(this.crmUser);
			var crm_name = '';
			for(let i in crmUser){
				if(crmUser[i].uid == this.crm_uid){
					crm_name = crmUser[i].name;
					break;
				}
			}
			
			this.crm_name = crm_name;
		},
		async saveinfo() {
			if (this.uid == ''){
				message.warning('请选择需要转交的客户！');
				return false;
			}
			if (this.crm_uid == ''){
				message.warning('请选择转交客户经理');
				return false;
			}
			if (this.remark == '') {
				message.warning('请填写转交备注信息');
				return false;
			}
							
			var param = {
				uid: this.uid,
				crm_uid: this.crm_uid,
				crm_name: this.crm_name,
				remark: this.remark,
			};
			this.saveloading = true;
			httpPost('m=crm&c=crm_my_customer&a=deliver', param).then((result) => {
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