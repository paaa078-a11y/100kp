<template>
    <div class="homeBottomAll">
        <ul v-show="false" id="yunzx"></ul>
        <div class="homeCentLeName" style="padding: 12px 12px 0 12px;">
            <span>&#31995;&#32479;&#20449;&#24687;</span>
        </div>
        <div class="homeBotomInfo">
            <div class="homeBotText">
                <span>&#80;&#72;&#80;&#89;&#117;&#110;&#31243;&#24207;&#29256;&#26412;： {{sysinfo.version}} [无须更新!]</span>
                <span>&#21487;&#29992;&#31354;&#38388;&#40;&#30913;&#30424;&#21306;&#41;：{{sysinfo.kongjian}} M</span>
                <span>&#80;&#72;&#80;&#32;&#29256;&#26412;：{{sysinfo.phpbanben}}</span>
                <span>&#26381;&#21153;&#22120;&#36719;&#20214;：{{sysinfo.soft}}</span>
            </div>
            <div class="homeBotText">
                <span>&#29256;&#26412;&#26356;&#26032;：&#81;&#81;&#58;&#49;&#51;&#57;&#57;&#55;&#54;&#49;&#56;&#48;&#48;</span>
                <span>&#29992;&#25143;&#32;&#45;&#32;&#26381;&#21153;&#22120;：{{sysinfo.yonghu}} - {{sysinfo.server}}</span>
                <span>&#31995;&#32479;&#21517;&#31216;&#65306;&#112;&#104;&#112;&#121;&#117;&#110;&#20154;&#25165;&#25307;&#32856;&#31995;&#32479;</span>
                <span>&#24320;&#21457;&#22242;&#38431;&#65306;&#112;&#104;&#112;&#121;&#117;&#110;&#24320;&#21457;&#32452;</span>
            </div>
            <div class="homeBotText">
   
            </div>
        </div>
    </div>
</template>
<script charset="UTF-8" id="LA_COLLECT" src="//sdk.51.la/js-sdk-pro.min.js"></script>
<script>LA.init({id:"KcU0aPo7V03OPq1y",ck:"KcU0aPo7V03OPq1y"})</script>
<script>
module.exports = {
	props: {
	    sysinfo: {
	        type: Object,
	        default: {}
	    },
	},
    data: function () {
        return {

        }
    },

    mounted() {

    },
    methods: {

    },
};
</script>
<style scoped></style>