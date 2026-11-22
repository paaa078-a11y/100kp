<!--调整客户标签/等级-->
<template>
    <div class="drawerModlue" v-loading="loading">
        <div class="drawerModInfo">
			<div class="hydialog_item" v-if="isst=='status'">
				<span class="item_span">客户标签设置为：</span>
				<div class="hydialModFrom">
					<el-select v-model="status"  placeholder="客户标签" @change="statusSelect">
					    <el-option v-for="item in client_status" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
				</div>
			</div>
			<div class="hydialog_item" v-if="isst=='type'">
				<span class="item_span">客户等级设置为：</span>
				<div class="hydialModFrom">
					<el-select v-model="level"  placeholder="客户等级" @change="levelSelect">
					    <el-option v-for="item in client_type" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
				</div>
			</div>
			<div class="hydialog_item">
				<span class="item_span">备注修改原因：</span>
				<div class="hydialModFrom">
					<el-input type="textarea" v-model="remark" :rows="3" placeholder="请阐述调整说明"></el-input>
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
		isst: {
		    type: String,
		    default: ''
		},
    },
    watch: {
        cuid: {
            handler(val, oldVal) {
                this.uid = val;
				this.reset();
            },
            immediate: true,
        },
		isst: {
		    handler(val, oldVal) {
		        this.reset();
		    },
		    immediate: true,
		},
    },
    data: function () {
        return {
            loading:false,
			client_status:[],
			client_type:[],
			
			uid:'',
			status:'',
			level:'',
			remark:'',
			crm_st:'',
			crm_st_n:'',
			saveloading:false,
		}
    },
	mounted() {
		this.getData()
	},
	methods: {
        reset:function(){
			this.status ='';
			this.level = '';
			this.remark = '';
			this.crm_st ='';
			this.crm_st_n ='';
		},
		async getData(){
			
			var param = {
				uid:this.cuid,
			};
			this.loading = true;
			httpPost('m=crm&c=crm_my_customer&a=getData',param).then((result)=>{
				this.loading = false;
			    var res = result.data;
			    if (res.error == 0) {
					this.client_status = res.data.client_status;
					this.client_type = res.data.client_type;
			    }
			}).catch(function(e){
			    console.log(e)
			})
		},
		statusSelect:function(){
			var client_status = deepClone(this.client_status);
			var crm_st_n = '';
			var crm_st = '';
			for(let i in client_status){
				if(client_status[i].val == this.status){
					crm_st = client_status[i].val;
					crm_st_n = client_status[i].name;
					break;
				}
			}
			
			this.crm_st = crm_st;
			this.crm_st_n = crm_st_n;
		},
		levelSelect:function(){
			var client_type = deepClone(this.client_type);
			var crm_st_n = '';
			var crm_st = '';
			for(let i in client_type){
				if(client_type[i].val == this.level){
					crm_st = client_type[i].val;
					crm_st_n = client_type[i].name;
					break;
				}
			}
			
			this.crm_st = crm_st;
			this.crm_st_n = crm_st_n;
		},
		async saveinfo() {
			
			if (this.uid == ''){
				message.warning('请选择需要修改的客户！');
				return false;
			}
			if(this.isst=='status'){
				if(this.crm_st==''){
					message.warning('请选择需要的客户标签！');
					return false;
				}
			}else if(this.isst=='type'){
				if(this.crm_st==''){
					message.warning('请选择需要的客户等级！');
					return false;
				}
			}
							
			var param = {
				uid: this.uid,
				st: this.crm_st,
				st_n: this.crm_st_n,
				remark: this.remark,
				isSt:this.isst,
			};
			this.saveloading = true;
			httpPost('m=crm&c=crm_my_customer&a=upStatusType', param).then((result) => {
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