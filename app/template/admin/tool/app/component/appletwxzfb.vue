<template>
    <div class="moduleElHight">
	 <div class="tableDome_tip">
	     <el-alert title="可以使用QQ/163等邮箱，465端口需要开启SSL服务，注意邮箱密码不是账户密码一般是独立授权密码" type="success"
	         :closable="false">
	     </el-alert>
	 </div>
     <div class="moduleTable">
         <table class="tableVue">
             <thead>
                 <tr align="left">
                     <th width="200">名称</th>
                     <th width="500">状态</th>
                     <th>说明</th>
                 </tr>
             </thead>
             <tbody>
				 <tr>
				     <td>
				         <div class="TableTite">邮件发送方式</div>
				     </td>
				     <td>
				       <div class="TableButn">
				           <el-radio v-model="radio" label="1">SMTP服务器发送邮件</el-radio>
				           <el-radio v-model="radio" label="2">阿里云DirectMail</el-radio> 
				       </div>
				     </td>
				     <td>
				      <div class="TableShuom">
				          <span> </span>
				      </div>
				     </td>
				 </tr>
                <tr>
                    <td>
                        <div class="TableTite">SMTP服务器</div>
                    </td>
                    <td>
                      <div class="TableInpt">
                          <el-input placeholder="请输入内容" v-model="input3">
                        
                          </el-input>
                      </div>
                    </td>
                    <td>
                      <div class="TableShuom">
                          <span>如：smtp.qq.com</span>
                      </div>
                    </td>
                </tr>
                				
                <tr>
                    <td>
                        <div class="TableTite">SMTP服务器的用户邮箱</div>
                    </td>
                    <td>
                      <div class="TableInpt">
                          <el-input placeholder="请输入内容" v-model="input3">
                        
                          </el-input>
                      </div>
                    </td>
                    <td>
                       <div class="TableShuom">
                           <span>如：phpyun@qq.com</span>
                       </div>
                    </td>
                </tr>
				
				<tr>
				    <td>
				        <div class="TableTite">邮箱密码</div>
				    </td>
				    <td>
				      <div class="TableInpt">
				          <el-input placeholder="请输入内容" v-model="input3">
				        
				          </el-input>
				      </div>
				    </td>
				    <td>
				       <div class="TableShuom">
				           <span>此处密码一般为邮箱独立的授权密码而并非原始邮箱密码，具体可查看各邮箱设置详细信息</span>
				       </div>
				    </td>
				</tr>
				<tr>
				    <td>
				        <div class="TableTite">发件人昵称</div>
				    </td>
				    <td>
				      <div class="TableInpt">
				          <el-input placeholder="请输入内容" v-model="input3">
				        
				          </el-input>
				      </div>
				    </td>
				    <td>
				       <div class="TableShuom">
				           <span> </span>
				       </div>
				    </td>
				</tr>
				
				
				
				
				
				
				
				 <tr>
				     <td>
				         <div class="TableTite">启用邮箱</div>
				     </td>
				     <td>
				       <div class="TableButn">
				           <el-radio v-model="radio" label="1">开始</el-radio>
				           <el-radio v-model="radio" label="2">关闭</el-radio> 
				       </div>
				     </td>
				     <td>
				       <div class="TableShuom">
				           <span>QQ互联平台移动应用和网站应用是否已打通，未打通的请不要打开</span>
				       </div>
				     </td>
				 </tr>
				 <tr>
				     <td>
				         <div class="TableTite">SMTP服务器</div>
				     </td>
				     <td>
				       <div class="TableInpt">
				           <el-input placeholder="请输入内容" v-model="input3">
				         
				           </el-input>
				       </div>
				     </td>
				     <td>
				        <div class="TableShuom">
				            <span>如：smtp.qq.com</span>
				        </div>
				     </td>
				 </tr>
				 <tr>
				     <td>
				         <div class="TableTite">邮箱密码</div>
				     </td>
				     <td>
				       <div class="TableInpt">
				           <el-input placeholder="请输入内容" v-model="input3">
				         
				           </el-input>
				       </div>
				     </td>
				     <td>
				        <div class="TableShuom">
				            <span>注：此处密码一般为邮箱独立的授权密码而并非原始邮箱密码，具体可查看各邮箱设置详细信息</span>
				        </div>
				     </td>
				 </tr>
				 
				<tr>
				    <td>
				        <div class="TableTite">SMTP服务器端口</div>
				    </td>
				    <td>
				      <div class="TableInpt">
				          <el-input placeholder="请输入内容" v-model="input3">
				        
				          </el-input>
				      </div>
				    </td>
				    <td>
				       <div class="TableShuom">
				           <span>注：普通邮箱端口为25 企业邮箱端口为465，如使用465端口 请确保服务器开启SSL服务</span>
				       </div>
				    </td>
				</tr>
				 <tr>
				     <td>
				         <div class="TableTite">发件人昵称</div>
				     </td>
				     <td>
				       <div class="TableInpt">
				           <el-input placeholder="请输入内容" v-model="input3">
				         
				           </el-input>
				       </div>
				     </td>
				     <td>
				        <div class="TableShuom">
				            <span>注：普通邮箱端口为25 企业邮箱端口为465，如使用465端口 请确保服务器开启SSL服务</span>
				        </div>
				     </td>
				 </tr>
				<tr>
				    <td>
				        <div class="TableTite">启用邮箱</div>
				    </td>
				    <td>
				      <div class="TableInpt">
				          <el-input placeholder="请输入内容" v-model="input3">
				        
				          </el-input>
				      </div>
				    </td>
				    <td>
				       <div class="TableShuom">
				           <span>注：普通邮箱端口为25 企业邮箱端口为465，如使用465端口 请确保服务器开启SSL服务</span>
				       </div>
				    </td>
				</tr> 
				 
				 
				 
				
 </tbody>
   </table>    </div>   
  <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium">提交</el-button>
        </div>
        
    </div>
</template>
    
<script>
module.exports = {
    data: function () {
        return {
            input3: '',
            select: '',
            value: true,
            value1: '',
            checked: '',
            drawer: false,
            drawer2: false,
            currentPage4: 4,
             
        }
    },

    mounted() {

    },
    methods: {
        
    },
};
</script>
<style scoped>
 
</style>