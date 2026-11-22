<!--客户信息-->
<template>
    <div class="drawerModlue" v-loading="loading">
        <div class="drawerModInfo">
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>客户名称：</span>
			    </div>
			    <div class="drawerModInpt">
					{{info.name}}
				</div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>会员套餐：</span>
			    </div>
			    <div class="drawerModInpt">
					{{info.ratingname}} <span>{{info.ratingtime}} </span>
				</div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>所在城市：</span>
			    </div>
			    <div class="drawerModInpt">
					{{info.cityname}}
				</div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>联系方式：</span>
			    </div>
			    <div class="drawerModInpt">
					{{info.linkman}} <span>{{info.moblie}}</span>
				</div>
			</div>
        </div>
    </div>
</template>

<script>

module.exports = {
    props: {
        uid: {
            type: [String, Number],
            default: ''
        },
    },
    watch: {
        uid: {
            handler(val, oldVal) {
                this.cuid = val;
                this.getInfo();
            },
            immediate: true,
        },
    },
    data: function () {
        return {
            cuid:'',
			info:{},
			loading:false,
		}
    },
	
    methods: {
        
		async getInfo(){
			
			var param = {
				uid:this.cuid,
			};
			this.loading = true;
			httpPost('m=crm&c=task&a=ComDetail',param).then((result)=>{
				this.loading = false;
			    var res = result.data;
			    if (res.error == 0) {
					this.info = res.data;
			    }
			}).catch(function(e){
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