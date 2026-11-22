<template>
    <div class="moduleElHight" v-loading="loading"  style="overflow-y: auto;">
        <div class="shbox">
            <div class="shinfo">
                <div class="shshow_tit">
                    <i class="el-icon-office-building"></i> 基本资料
                    <span class="shshow_cz">
                        <el-button type="text"  @click="openBoxShow('Info');">
                            <i class="el-icon-edit"></i>编辑资料
                        </el-button>
                    </span>
                </div>
                <div class="userinfo_box">
                    <div class="userinfo_l"><img :src="Info.photo" width="70" height="70"></div>
                    <div class="userinfo_r">
                        <div class="userinfo_name">{{ Info.name }}
							<span v-if="Info.email_status=='1'" class="  yxrz" > 邮箱已认证</span>
							<span v-if="Info.wxid"  class="  wxrz">  微信公众号已绑定</span>
						</div>
                        <div class="userinfo">
                            {{ Info.sex_n }}
                            <span v-if="Info.age">，{{ Info.age }}岁</span>
							<span v-if="Info.edu_n">，{{ Info.edu_n }}学历</span>
							<span v-if="Info.exp_n">，{{ Info.exp_n }}经验</span>
                        </div>

						<div class="userinfo">{{Info.description}}</div>
						<div class="userinfo">职位订阅：
							<el-link type="primary" :underline="false"   @click="openBoxShow('subscribe');">订阅</el-link>
							<span v-if="issubscribe==1 && subscribe.jobname_n!=''" class="ydy"><el-divider direction="vertical"></el-divider>  已订阅类别：{{subscribe.jobname_n}}</span>
						</div>
						<div class="userinfo" v-if="Expect.add_ip">
							<span style="padding-top: 5px; display: inline-block;">  IP：{{Expect.add_ip}}</span><el-divider direction="vertical"></el-divider>IP归属地：{{Expect.ip_address}}
						</div>
                    </div>
                </div>

                <el-tabs v-model="tab" type="card" @tab-click="tabClick">
                    <el-tab-pane label="简历详情" name="1">
						<div class="user_resume_list">
                            <div class="shshow_tit"><i class="el-icon-notebook-2"></i> 求职意向 <el-button type="text"  @click="openBoxShow('Expect');">
							         <i class="el-icon-edit"></i> 修改
							     </el-button></div>

                            <div class="shshow_p">
                                <div class="cominfo">期望职位： {{ Expect.name }} </div>
                                <div class="cominfo">从事职位： {{ Expect.job_classname }}</div>
                                <div class="cominfo">期望地点： {{ Expect.city_classname }}</div>
                                <div class="cominfo">期望薪资： {{ Expect.salary }}</div>
                                <div class="cominfo">从事行业： {{ Expect.hy_n }}</div>
                                <div class="cominfo">到岗时间： {{ Expect.report_n }}</div>
                                <div class="cominfo">工作性质： {{ Expect.type_n }}</div>
                                <div class="cominfo">求职状态： {{ Expect.jobstatus_n }}</div>
                            </div>

                        </div>

                        <div class="user_resume_list">
                            <div class="shshow_tit"><i class="el-icon-suitcase-1"></i> 工作经历</div>

                            <div class="user_resume_show"  v-for="(item, wk) in Info.user_work" :key="wk">
                                <div class="user_resume_addname ">{{ item.name }}
                                    <el-button type="text" @click="openBoxShowInfo('work', item);">
                                        <i class="el-icon-edit"></i> 修改
                                    </el-button>
                                    <el-button type="text" @click="delResume('work', item.id, wk);">
                                        <i class="el-icon-delete"></i> 删除
                                    </el-button>
                                </div>
                                <div class="user_resume_addjy">
                                    <div class=" ">{{ item.title }}</div>
                                    <div class="user_resume_time">{{ item.sdate_n }}-{{ item.edate_n }}</div>
                                </div>
                                <div class="user_resume_ms">{{ item.content }}</div>
                            </div>

                            <div class="user_resume_add">
                                <div class="">展示工作经验、工作能力否符合岗位要求的重要依据</div>
                                <div class="user_resume_addbth">
                                    <el-button type="text" @click="openBoxShow('work');">
                                        <i class="el-icon-circle-plus-outline"></i> 添加
                                    </el-button>
                                </div>
                            </div>
                        </div>

                        <div class="user_resume_list">
                            <div class="shshow_tit"><i class="el-icon-school"></i> 教育经历</div>

                            <div class="user_resume_show" v-for="(item, ek) in Info.user_edu" :key="ek">
                                <div class="user_resume_addname ">{{ item.name }}
                                    <el-button type="text" @click="openBoxShowInfo('edu', item);">
                                        <i class="el-icon-edit"></i> 修改
                                    </el-button>
                                    <el-button type="text" @click="delResume('edu', item.id, ek);">
                                        <i class="el-icon-delete"></i> 删除
                                    </el-button>
                                </div>
                                <div class="user_resume_addjy">
                                    <div class=" ">{{ item.specialty }}<span class="userline" v-if="item.specialty && item.education_n">|</span>{{ item.education_n }}</div>
                                    <div class="user_resume_time">{{ item.sdate_n }}-{{ item.edate_n }}</div>
                                </div>
                            </div>

                            <div class="user_resume_add">
                                <div class="">补足HR对学历背景的了解</div>
                                <div class="user_resume_addbth">
                                    <el-button type="text"  @click="openBoxShow('edu');">
                                        <i class="el-icon-circle-plus-outline"></i> 添加
                                    </el-button>
                                </div>
                            </div>
                        </div>

                        <div class="user_resume_list">
                            <div class="shshow_tit"><i class="el-icon-data-analysis"></i> 培训经历</div>

                            <div class="user_resume_show" v-for="(item, tk) in Info.user_tra" :key="tk">
                                <div class="user_resume_addname ">{{ item.name }}
                                    <el-button type="text" @click="openBoxShowInfo('train', item);">
                                        <i class="el-icon-edit"></i> 修改
                                    </el-button>
                                    <el-button type="text" @click="delResume('training', item.id, tk);">
                                        <i class="el-icon-delete"></i> 删除
                                    </el-button>
                                </div>
                                <div class="user_resume_addjy">
                                    <div class=" ">{{ item.title }} </div>
                                    <div class="user_resume_time">{{ item.sdate_n }}-{{ item.edate_n }}</div>
                                </div>
                                <div class="user_resume_ms">{{ item.content }}</div>
                            </div>

                            <div class="user_resume_add">
                                <div class="">展示培训经验否符合岗位要求的重要依据</div>
                                <div class="user_resume_addbth">
                                    <el-button type="text" @click="openBoxShow('train');">
                                        <i class="el-icon-circle-plus-outline"></i> 添加
                                    </el-button>
                                </div>
                            </div>
                        </div>

                        <div class="user_resume_list">
                            <div class="shshow_tit"><i class="el-icon-reading"></i> 职业技能</div>

                            <div class="user_resume_show" v-for="(item, sk) in Info.user_skill" :key="sk">
                                <div class="user_resume_addname ">{{ item.name }}
                                    <el-button type="text" @click="openBoxShowInfo('skill', item);">
                                        <i class="el-icon-edit"></i> 修改
                                    </el-button>
                                    <el-button type="text" @click="delResume('skill', item.id, tk);">
                                        <i class="el-icon-delete"></i> 删除
                                    </el-button>
                                </div>
                                <div class="user_resume_addjy">
                                    <div class=" ">{{ item.ing_n }} </div>
                                    <div class="user_resume_time">{{ item.longtime }} 年</div>
                                </div>
                                <div class="user_resume_ms">
                                    <img :src="item.pic" width="95" height="70" :preview-src-list="item.pic">
                                </div>
                            </div>

                            <div class="user_resume_add">
                                <div class="">技能专长建议填写职业技能为简历加分</div>
                                <div class="user_resume_addbth">
                                    <el-button type="text" @click="openBoxShow('skill');">
                                        <i class="el-icon-circle-plus-outline"></i> 添加
                                    </el-button>
                                </div>
                            </div>
                        </div>

                        <div class="user_resume_list">
                            <div class="shshow_tit"><i class="el-icon-wallet"></i> 项目经历</div>

                            <div class="user_resume_show" v-for="(item, xk) in Info.user_xm" :key="xk">
                                <div class="user_resume_addname ">{{ item.name }}
                                    <el-button type="text" @click="openBoxShowInfo('project', item);">
                                        <i class="el-icon-edit"></i> 修改
                                    </el-button>
                                    <el-button type="text" @click="delResume('project', item.id, xk);">
                                        <i class="el-icon-delete"></i> 删除
                                    </el-button>
                                </div>
                                <div class="user_resume_addjy">
                                    <div class=" ">{{ item.title }}</div>
                                    <div class="user_resume_time">{{ item.sdate_n }}-{{ item.edate_n }}</div>
                                </div>
                                <div class="user_resume_ms">{{ item.content }}</div>
                            </div>

                            <div class="user_resume_add">
                                <div class="">展示工作经验、能力，这也是HR判断是否符合岗位要求的重要依据。</div>
                                <div class="user_resume_addbth">
                                    <el-button type="text" @click="openBoxShow('project');">
                                        <i class="el-icon-circle-plus-outline"></i> 添加
                                    </el-button>
                                </div>
                            </div>
                        </div>

                        <div class="user_resume_list" style="padding-bottom:80px; ;">
                            <div class="shshow_tit"><i class="el-icon-mic"></i> 其他描述</div>

                            <div class="user_resume_show" v-for="(other, otherkey) in Info.user_other" :key="otherkey">
                                <div class="user_resume_addname ">{{ other.name }}
                                    <el-button type="text" @click="openBoxShowInfo('other', other);">
                                        <i class="el-icon-edit"></i> 修改
                                    </el-button>
                                    <el-button type="text" @click="delResume('other', other.id, otherkey);">
                                        <i class="el-icon-delete"></i> 删除
                                    </el-button>
                                </div>
                                <div class="user_resume_ms">{{ other.content }}</div>
                            </div>

                            <div class="user_resume_add">
                                <div class="">添加其他</div>
                                <div class="user_resume_addbth">
                                    <el-button type="text" @click="openBoxShow('other');">
                                        <i class="el-icon-circle-plus-outline"></i> 添加
                                    </el-button>
                                </div>
                            </div>
                        </div>
                        <div class="shcz" style="top:60px;right:30px;">
                            <template v-if="Info.r_status == '2'">
                                <div class="wxsettip_small ">简历审核</div>
                                <template>
                                    <el-radio-group v-model="Info.r_status">
                                        <el-radio label="1">正常</el-radio>
                                        <el-radio label="2">锁定</el-radio>
                                    </el-radio-group>
                                    <el-alert v-if="Info.lock_info" :closable="false" :title="'锁定原因：' + Info.lock_info"
                                        type="warning" show-icon>
                                    </el-alert>
                                </template>
                            </template>
                            <template v-if="Info.r_status!='2'">
                                <div class="wxsettip_small ">简历审核</div>
                                <template>
                                    <el-radio-group v-model="Info.state">
                                        <el-radio label="1">正常</el-radio>
                                        <el-radio label="3">未通过</el-radio>
                                    </el-radio-group>
                                </template>
                                <div class="wxsettip_small ">审核说明模板</div>
                                <el-select v-model="auditTpl" placeholder="请选择" @change="changeTpl">
                                    <el-option v-for="item in userAudit" :key="item.id" :label="item.name" :value="item.name">
                                    </el-option>
                                </el-select>
                                <div class="wxsettip_small ">审核说明</div>
                                <el-input type="textarea" :rows="2" v-model="Info.statusbody">
                                </el-input>
                                <div class=" shczbth">
                                    <el-button type="primary" @click="auditResume">提 交</el-button>
                                </div>
                            </template>
                        </div>
                    </el-tab-pane>
					<el-tab-pane label="跟进记录" name="2">

					    <div class="xingweiDatas">
					        <div class="xingweiDaComs">
								<div v-if="!followTips">
									<div class="drawerModLis">
										<div class="drawerModTite">
											<span>人才姓名</span>
										</div>
										<div class="drawerModInpt">
											<el-input v-model="followInfo.name" :disabled="true"></el-input>
										</div>
									</div>
									<div class="drawerModLis">
										<div class="drawerModTite">
											<span>简历标签</span>
										</div>
										<div class="drawerModInpt">
											<el-select v-model="followInfo.label_id" placeholder="请选择简历标签" clearable>
												<el-option v-for="item in userLabel" :key="item.id" :label="item.name" :value="item.id"></el-option>
											</el-select>
										</div>
									</div>
									<div class="drawerModLis">
										<div class="drawerModTite">
											<span>客服评价</span>
										</div>
										<div class="drawerModInpt">
											<el-input type="textarea" v-model="followInfo.content" :autosize="{ minRows: 3, maxRows: 6 }"></el-input>
										</div>
									</div>
									<div class="drawerModLis">
										<div class="drawerModTite">
											<span>跟进时间</span>
										</div>
										<div class="drawerModInpt">
											<el-date-picker v-model="f_time" type="datetime" value-format="yyyy-MM-dd HH:mm:ss" :picker-options="fpickerOptions" placeholder="选择跟进时间"></el-date-picker>
										</div>
									</div>
									<div class="setBasicButn" style="border: none;">
										<el-button type="primary" size="medium" @click="addFollow">保存</el-button>
									</div>
								</div>
								<el-empty description="真是辛苦您啦~" v-if="followTips">
									<el-button type="primary" @click="againFollow">再次填写</el-button>
								</el-empty>

					        </div>
					        <div class="xingweiDaTime">
					            <div class="xingweiDaTite">
					                <el-radio-group v-model="followTab" style="margin-bottom: 20px;" @input="followTabChange">
										<el-radio-button label="1">我的跟进记录</el-radio-button>
										<el-radio-button label="2">历史跟进记录</el-radio-button>
					                </el-radio-group>
					            </div>
					            <div class="xingweiBuzous">
									<div v-if="follow_total>0">
										<el-timeline :reverse="false">
											<el-timeline-item v-for="(item, fk) in FollowList"  :key="fk"
													:timestamp="item.time_n+' '+item.c_name">
												{{ item.content }}
											</el-timeline-item>
										</el-timeline>
										<template v-if="followLast">
											<template v-if="FollowList.length>0">没有更多了</template>
										</template>
										<template v-if="!followLast">
											<h3 @click="getloglist">加载更多...</h3>
										</template>
									</div>
									<el-empty v-else description="暂无跟进记录"></el-empty>
					            </div>
					        </div>
					    </div>
					</el-tab-pane>
					<el-tab-pane label="投递记录" name="3">
					    <div class="moduleElTable moduleElTablTwo" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
					        <el-table ref="tdTable" :data="apply_tableData" v-loading="apply_list_loading"  stripe style="width: 100%"
					            :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :empty-text="emptytext">

					            <el-table-column prop="time_n" label="投递时间">
					            </el-table-column>
								<el-table-column prop="job_name" label="投递岗位">
								</el-table-column>
								<el-table-column prop="com_name" label="企业名称">
								</el-table-column>

					        </el-table>
					    </div>
					    <div class="modulePaging">
					        <div class="modulePagNum">
					            <el-pagination background @size-change="apply_handleSizeChange" @current-change="apply_handleCurrentChange"
					                :current-page="apply_currentPage" :page-size="apply_limit" :page-sizes="apply_page_sizes" :total="apply_total"
					                layout="total, sizes, prev, pager, next, jumper">
					            </el-pagination>
					        </div>
					    </div>
					</el-tab-pane>
                </el-tabs>
            </div>
        </div>
		<!--职位订阅弹窗-->
		<el-dialog title="职位订阅" width="450px" :visible.sync="subscribeBoxShow" :modal-append-to-body="false" append-to-body>
			<div class="dialoItemCoy">
				<div class="dialoItemTte">
					<span>工作职能：</span>
				</div>

				<div class="drawerModInpt">
					<job_class ref="subJob" :multiple="true" :max="5" @confirm="confirmSubJob" :selected="sub_jobclass_selected"></job_class>
				</div>
			</div>
			<div class="dialoItemCoy">
				<div class="dialoItemTte">
					<span>发送周期：</span>
				</div>

				<div class="drawerModInpt">
					<el-radio-group v-model="subtime">
					    <el-radio label="1">每天</el-radio>
					    <el-radio label="3">三天</el-radio>
					    <el-radio label="7">七天</el-radio>
					</el-radio-group>
				</div>
			</div>
			<span class="dialog-footer">
		        <el-button @click="subscribeBoxShow = false">取 消</el-button>
		        <el-button type="primary" @click="saveSub">确 定</el-button>
		    </span>
		</el-dialog>
		<!--基本信息弹窗-->
		<el-drawer title="修改基本资料" :modal-append-to-body="false" :append-to-body="true" :visible.sync="InfoBoxShow" size="60%">
		    <div class="uploadTable" style="padding:0px 20px;">
		        <table class="tableVue">
		            <thead>
		                <tr align="left">
		                    <th width="120">名称</th>
		                    <th width=" ">状态</th>
		                </tr>
		            </thead>
		            <tbody>
		                <tr>
		                    <td>
		                        <div class="TableTite">姓名</div>
		                    </td>
		                    <td>
		                        <div class="TableInpt">
		                            <el-input v-model="infodata.name" placeholder="请输入姓名"> </el-input>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">性别</div>
		                    </td>
		                    <td>
		                        <div class="job_set_list">
		                            <el-radio-group v-model="infodata.sex">
		                                <el-radio v-for="item in userSex" :key="item.id" :label="item.id">{{item.name}}</el-radio>
		                            </el-radio-group>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">出生年月</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-date-picker v-model="infodata.birthday" type="month" placeholder="选择月" value-format="yyyy-MM" :picker-options="pickerOptions">
		                            </el-date-picker>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">教育程度</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-select v-model="infodata.edu" placeholder="请选择">
		                                <el-option v-for="item in userEdu" :key="item.id" :label="item.name" :value="item.id">
		                                </el-option>
		                            </el-select>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">工作经验</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-select v-model="infodata.exp" placeholder="请选择">
		                                <el-option v-for="item in userWord" :key="item.id" :label="item.name" :value="item.id">
		                                </el-option>
		                            </el-select>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">自我介绍</div>
		                    </td>
		                    <td>
		                        <div class="TableInpt">
		                            <el-input type="textarea" :rows="2" placeholder="请自我介绍下吧" v-model="infodata.description"></el-input>
		                        </div>
		                    </td>
		                </tr>
		            </tbody>
		        </table>
		    </div>
		    <div class="setBasicButn" style="border: none; height: 80px;">
		        <el-button type="primary" size="medium" @click="saveResumeInfo">提交</el-button>
		    </div>
		</el-drawer>
		<!---编辑求职意向-->
		<el-drawer title="编辑求职意向" :append-to-body="true" :visible.sync="ExpectBoxShow" size="60%">
		    <div class="uploadTable" style="padding:0px 20px;">
		        <table class="tableVue">
		            <thead>
		                <tr align="left">
		                    <th width="120">名称</th>
		                    <th width=" ">状态</th>
		                </tr>
		            </thead>
		            <tbody>
		                <tr>
		                    <td>
		                        <div class="TableTite">期望职位</div>
		                    </td>
		                    <td>
		                        <div class="TableInpt">
		                            <el-input v-model="expectinfo.name" placeholder="请输入期望职位">
		                            </el-input>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">从事职位</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <!--7.0 统一类别选择-->
		                            <job_class ref="expectJob" multiple :max="5" @confirm="confirmJob" :selected="jobSelected"></job_class>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">期望地点</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <!--7.0 统一城市选择-->
		                            <city_class multiple :max="5" @confirm="confirmCity" :selected="citySelected"></city_class>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">期望薪资</div>
		                    </td>
		                    <td>
								<div class="TableInpt" style="max-width: 700px;">
									<el-select v-model="expectinfo.minsalary" placeholder="请选择" @change="salaryChange" style="margin-right:8px;">
										<el-option v-for="maxsalary1Val in minsalaryList" :key="maxsalary1Val" :label="maxsalary1Val" :value="maxsalary1Val">
										</el-option>
									</el-select>
									<el-select v-model="expectinfo.maxsalary" placeholder="请选择">
										<el-option v-for="maxsalary2Val in maxsalaryList" :key="maxsalary2Val" :label="maxsalary2Val" :value="maxsalary2Val">
										</el-option>
									</el-select>
								</div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">从事行业</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-select v-model="expectinfo.hy" placeholder="请选择从事行业" clearable>
		                                <el-option v-for="item in industryArr" :key="item.id" :label="item.name" :value="item.id">
		                                </el-option>
		                            </el-select>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">到岗时间</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-select v-model="expectinfo.report" placeholder="请选择到岗时间" clearable>
		                                <el-option v-for="item in userReport" :key="item.id" :label="item.name" :value="item.id">
		                                </el-option>
		                            </el-select>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">工作性质</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-select v-model="expectinfo.type" placeholder="请选择工作性质" clearable>
		                                <el-option v-for="item in userType" :key="item.id" :label="item.name" :value="item.id">
		                                </el-option>
		                            </el-select>
		                        </div>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <div class="TableTite">求职状态</div>
		                    </td>
		                    <td>
		                        <div class="TableSelect">
		                            <el-select v-model="expectinfo.jobstatus" placeholder="请选择求职状态" clearable>
		                                <el-option v-for="item in userJobStatus" :key="item.id" :label="item.name" :value="item.id">
		                                </el-option>
		                            </el-select>
		                        </div>
		                    </td>
		                </tr>
		            </tbody>
		        </table>
		    </div>
		    <div class="setBasicButn" style="border: none; height: 80px;">
		        <el-button type="primary" size="medium" @click="saveResumeExpect">提交</el-button>
		    </div>
		</el-drawer>
		<!---编辑工作经历-->
		<div class="modluDrawer">
			<el-dialog title="工作经历" :visible.sync="workBoxShow" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="450px" append-to-body>
				<div>
					<div class="wxsettip_small ">公司名称</div>
					<div class=""><el-input v-model="workInfo.name" placeholder="请输入公司名称"></el-input> </div>
					<div class="wxsettip_small ">担任职位</div>
					<div class=""><el-input v-model="workInfo.title" placeholder="请输入担任职位"></el-input> </div>
					<div class="wxsettip_small ">工作时间</div>
					<div class="wxsettip_Sealect" style="display: flex; align-items: center;">
						<el-date-picker v-model="workInfo.sdate_n" type="month" placeholder="选择开始时间" value-format="yyyy-MM" :picker-options="pastOptions">
						</el-date-picker>
						<el-date-picker style="margin: 0 8px;" :disabled="todayCheck" v-model="workInfo.edate_n"
							type="month" placeholder="选择结束时间" value-format="yyyy-MM">
						</el-date-picker>
						<el-checkbox v-model="todayCheck" @change="todayChange($event, 'work')">至今</el-checkbox>
					</div>
					<div class="wxsettip_small ">工作内容</div>
					<el-input type="textarea" placeholder="请简短介绍公司与自己负责的任务，分条罗列在什么项目中，通过某些动作或技能达到可量化的结果。
示例：
1、主要负责新员工入职培训；
2、分析制定员工每月个人销售业绩；
3、帮助员工提高每日客单价，整体店面管理工作等；" v-model="workInfo.content" :autosize="{ minRows: 3, maxRows: 6 }">
					</el-input>
				</div>

				<span slot="footer" class="dialog-footer">
					<el-button @click="workBoxShow = false">取 消</el-button>
					<el-button type="primary" @click="saveResumeWork">确 定</el-button>
				</span>
			</el-dialog>
		</div>
		<!---编辑学历-->
		<div class="modluDrawer">
		    <el-dialog title="教育经历" :visible.sync="eduBoxShow" :with-header="true" :modal-append-to-body="false"
		        :show-close="true" width="450px" append-to-body>
		        <div>
		            <div class="wxsettip_small ">学校名称</div>
		            <div class=""><el-input v-model="eduInfo.name" placeholder="请输入学校名称"></el-input> </div>
		            <div class="wxsettip_small ">在校时间</div>
		            <div class="wxsettip_Sealect" style="display: flex; align-items: center;">
		                <el-date-picker style="margin-right: 8px;" v-model="eduInfo.sdate_n" type="month"  placeholder="开始时间" value-format="yyyy-MM" :picker-options="pastOptions"></el-date-picker>
		                <el-date-picker v-model="eduInfo.edate_n" type="month" placeholder="结束时间" value-format="yyyy-MM"></el-date-picker>
		            </div>
		            <div class="wxsettip_small ">最高学历</div>
		            <div class="wxsettip_Sealect">
		                <el-select v-model="eduInfo.education" placeholder="请选择">
		                    <el-option v-for="item in userEdu" :key="item.id" :label="item.name" :value="item.id"></el-option>
		                </el-select>
		            </div>
		            <div class="wxsettip_small ">所学专业</div>
		            <div class=""><el-input v-model="eduInfo.specialty" placeholder="请输入专业名称"></el-input> </div>
		        </div>
		        <span slot="footer" class="dialog-footer">
		            <el-button @click="eduBoxShow = false">取 消</el-button>
		            <el-button type="primary" @click="saveResumeEdu">确 定</el-button>
		        </span>
		    </el-dialog>
		</div>
		<!---编辑培训经历-->
		<div class="modluDrawer">
		    <el-dialog title="培训经历" :visible.sync="trainBoxShow" :with-header="true" :modal-append-to-body="false"
		        :show-close="true" width="450px" append-to-body>
		        <div>
		            <div class="wxsettip_small ">培训中心</div>
		            <div class=""><el-input v-model="trainInfo.name" placeholder="请输入培训中心"></el-input> </div>
		            <div class="wxsettip_small ">培训方向</div>
		            <div class=""><el-input v-model="trainInfo.title" placeholder="请输入培训方向"></el-input> </div>
		            <div class="wxsettip_small ">培训时间</div>
		            <div class="wxsettip_Sealect" style="display: flex; align-items: center;">
		                <el-date-picker style="margin-right: 8px;" v-model="trainInfo.sdate_n" type="month"  placeholder="开始时间" value-format="yyyy-MM"></el-date-picker>
		                <el-date-picker v-model="trainInfo.edate_n" type="month" placeholder="结束时间" value-format="yyyy-MM"></el-date-picker>
		            </div>
		            <div class="wxsettip_small ">培训描述</div>
		            <el-input type="textarea" placeholder="请简短介绍培训经历所获收获等；" v-model="trainInfo.content"
		                :autosize="{ minRows: 3, maxRows: 6 }"></el-input>
		        </div>
		        <span slot="footer" class="dialog-footer">
		            <el-button @click="trainBoxShow = false">取 消</el-button>
		            <el-button type="primary" @click="saveResumeTraining">确 定</el-button>
		        </span>
		    </el-dialog>
		</div>
		<!---编辑技能-->
		<div class="modluDrawer">
		    <el-dialog title="职业技能" :visible.sync="skillBoxShow" :with-header="true" :modal-append-to-body="false"
		        :show-close="true" width="450px" append-to-body>
		        <div>
		            <div class="wxsettip_small ">技能名称</div>
		            <div class=""><el-input v-model="skillInfo.name" placeholder="请输入技能名称"></el-input> </div>
		            <div class="wxsettip_small ">掌握时间</div>
		            <div class="wxsettip_Sealect">
		                <el-input v-model="skillInfo.longtime" placeholder="请输入掌握时间">
		                    <template slot="append">年</template>
		                </el-input>
		            </div>
		            <div class="wxsettip_small ">熟练程度</div>
		            <div class="wxsettip_Sealect">
		                <el-select v-model="skillInfo.ing" placeholder="请选择" clearable>
		                    <el-option  v-for="item in userIng" :key="item.id" :label="item.name" :value="item.id">
		                    </el-option>
		                </el-select>
		            </div>
		            <div class="wxsettip_small ">技能证书</div>
		            <div>
		                <el-upload class="avatar-uploader" list-type="picture" :accept="pic_accept" action="" :auto-upload="false"
		                    :on-change="handleChangeSkillPic" :show-file-list="false">
		                    <img v-if="skillInfo.pic" :src="skillInfo.pic" class="avatar">
		                    <i v-else class="el-icon-plus avatar-uploader-icon"></i>
		                </el-upload>
		            </div>
		        </div>
		        <span slot="footer" class="dialog-footer">
		            <el-button @click="skillBoxShow = false">取 消</el-button>
		            <el-button type="primary" @click="saveResumeSkill">确 定</el-button>
		        </span>
		    </el-dialog>
		</div>
		<!---编辑项目经历-->
		<div class="modluDrawer">
			<el-dialog title="项目经历" :visible.sync="projectBoxShow" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="450px" append-to-body>
				<div>
					<div class="wxsettip_small ">项目名称</div>
					<div class=""><el-input v-model="projectInfo.name" placeholder="请输入项目名称"></el-input> </div>
					<div class="wxsettip_small ">担任职务</div>
					<div class=""><el-input v-model="projectInfo.title" placeholder="请输入担任职务"></el-input> </div>
					<div class="wxsettip_small ">项目时间</div>
					<div class="wxsettip_Sealect" style="display: flex; align-items: center;">
						<el-date-picker style="margin-right: 8px;" v-model="projectInfo.sdate_n" type="month"  placeholder="开始时间" value-format="yyyy-MM" :picker-options="pastOptions"></el-date-picker>
						<el-date-picker v-model="projectInfo.edate_n" type="month" placeholder="结束时间" value-format="yyyy-MM"></el-date-picker>
					</div>
					<div class="wxsettip_small ">项目描述</div>
					<el-input type="textarea" placeholder="请简短介绍公司与自己负责的任务，分条罗列在什么项目中，通过某些动作或技能达到可量化的结果。
示例：
1、主要负责新员工入职培训；
2、分析制定员工每月个人销售业绩；
3、帮助员工提高每日客单价，整体店面管理工作等；" v-model="projectInfo.content" :autosize="{ minRows: 3, maxRows: 6 }"></el-input>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="projectBoxShow = false">取 消</el-button>
					<el-button type="primary" @click="saveResumeProject">确 定</el-button>
				</span>
			</el-dialog>
		</div>
		<!---编辑其他-->
		<div class="modluDrawer">
		    <el-dialog title="其他描述" :visible.sync="otherBoxShow" :with-header="true" :modal-append-to-body="false"
		        :show-close="true" width="450px" append-to-body>
		        <div>
		            <div class="wxsettip_small ">标题</div>
		            <div class=""><el-input v-model="otherInfo.name" placeholder="请输入标题"></el-input> </div>
		            <div class="wxsettip_small ">描述</div>
		            <el-input type="textarea" v-model="otherInfo.content" placeholder="请简短描述内容"
		                :autosize="{ minRows: 3, maxRows: 6 }"></el-input>
		        </div>
		        <span slot="footer" class="dialog-footer">
		            <el-button @click="otherBoxShow = false">取 消</el-button>
		            <el-button type="primary" @click="saveResumeOther">确 定</el-button>
		        </span>
		    </el-dialog>
		</div>
    </div>

</template>

<script>
module.exports = {
	props: {
		eid: {
			type: [Number,String],
			default: ''
		},
	},
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            emptytext: '暂无数据',
			loading:false,
            tab:'1',
			fpickerOptions: {
				disabledDate(time) {
					const date = new Date();
					var day7 = date.getTime() - 3600 * 1000 * 24 * 7;
					if (time.getTime() >= date.getTime() || time.getTime() <= day7) {
						return true;
					};
				}
			},
			pickerOptions: {
			    disabledDate(time) {
			        let year = parseInt((new Date()).getFullYear());
			        year = year-16;
			        return time.getTime() > new Date(year + '-12-31');
			    },
			},
			pastOptions:{
				disabledDate(time) {
				    var today = formatDatetime(new Date());
				    var date = new Date(today).getTime();

				    return time.getTime() > date;
				},
			},
			auditTpl:'',
			Info: {},
			Expect: [],
			userSex:[],
			userAudit: [],
			userEdu: [],
			userWord: [],
			userLabel: [],
			userReport: [],
			userType: [],
			userJobStatus: [],
			userIng: [],
			industryArr: [],

			InfoBoxShow: false,
			infodata:{},

			ExpectBoxShow: false,
			expectinfo:{},
			jobSelected: null,
			citySelected: null,
			salaryList: [],
			minsalaryList: [],
			maxsalaryList: [],

			workBoxShow: false,
			workInfo:[],
			todayCheck: false, // 至今选中

			eduBoxShow: false,
			eduInfo:[],

			trainBoxShow: false,
			trainInfo:[],

			projectBoxShow: false,
			projectInfo:[],

			skillBoxShow: false,
			skillInfo:[],

			otherBoxShow: false,
			otherInfo:[],

			//	跟进记录
			followTab: 1,
			followInfo:{},
			followTips:false,
			f_time:'',
			followLast:false,
			FollowList: [],
			follow_total: 0,
			follow_limit: '',
			follow_currentPage: 1,


			//	投递记录
			apply_tableData: [],
			apply_total: 0,
			apply_limit: 0,
            prevApplyPage: 0,
			apply_currentPage: 1,
			apply_page_sizes: [],
			apply_list_loading: false,

			subscribeBoxShow:false,
			subscribe:{},
			subscribeid:'',
			subtime:'',
			sub_jobclass_selected:{},
			sub_job_class:[],
			issubscribe:0,
        }
    },
	components: {
	    'job_class': httpVueLoader('../../../component/job_class.vue'),
	    'city_class': httpVueLoader('../../../component/city_class.vue'),
	},
	mounted() {
		this.getList();
    },
    methods: {
		tabClick:function(){
			if(this.tab=='3'){
				this.apply_currentPage = 1;
				this.getApplyList();
			}else if(this.tab=='2'){
				this.followInfo = deepClone(this.Info);
				if(this.followInfo.label_id=='0'){
					this.followInfo.label_id = '';
				}
				this.f_time = '';
				this.follow_currentPage=1;
				this.FollowList = [];
				this.getFollowList();
			}
		},
		againFollow:function(){

			this.followInfo = deepClone(this.Info);
			if(this.followInfo.label_id=='0'){
				this.followInfo.label_id = '';
			}
			this.f_time = '';

			this.followTips = false;
		},
        async getList() {
            let self = this;
            let params = {
				id:self.eid,
			}

            httpPost('m=crm&c=crm_chat&a=getResumeInfo', params).then((result) => {
                this.loading = false;
                var res = result.data;

                if (res.error == 0) {
                    self.Info = res.data.Info;
					self.Info.state = self.Info.state=='3'?'3':'1';
					
					self.Expect = res.data.Info.user_expect;
					self.userSex = res.data.userSex;
					self.userEdu = res.data.userEdu;
					self.userWord = res.data.userWord;
					self.userReport = res.data.userReport;
					self.userType = res.data.userType;
					self.userJobStatus = res.data.userJobStatus;
					self.userLabel = res.data.userLabel;
					self.userIng = res.data.userIng;
					self.userAudit = res.data.userAudit;
					self.industryArr = res.data.industryArr;
					self.subscribe = res.data.subscribe;
					self.salaryList = res.data.salary;
					self.issubscribe = res.data.issubscribe;
					if(typeof self.subscribe.jobnameArr!='undefined'){
						self.sub_jobclass_selected = self.subscribe.jobnameArr;
						self.sub_job_class = self.subscribe.jobclass;
					}
					if(typeof self.subscribe.time!='undefined'){
						self.subtime = self.subscribe.time;
					}
					if(typeof self.subscribe.id!='undefined'){
						self.subscribeid = self.subscribe.id;
					}
                }
            }).catch(function (e) {
                console.log(e)
            })
        },

		apply_handleCurrentChange(val) {
		    this.apply_currentPage = val;
		    this.getApplyList()
		},
		apply_handleSizeChange(val) {
		    this.apply_currentPage = 1
		    this.apply_limit = val
		    this.getApplyList()
		},
		async getApplyList() {
		    let that = this;
		    let params = {
				id:that.eid,
		        page: that.apply_currentPage,
		        limit: that.apply_limit,
		    }

		    this.apply_list_loading = true;
            that.emptytext = "数据加载中";
		    httpPost('m=crm&c=crm_chat&a=getApplyList', params).then((result) => {
		        this.apply_list_loading = false;
		        var res = result.data;

		        if (res.error == 0) {
		            that.apply_tableData = res.data.list
					that.apply_total = parseInt(res.data.total)
		            that.apply_page_sizes = res.data.page_sizes;
					that.apply_limit =  res.data.page_size;
                    if(that.prevApplyPage != that.apply_currentPage){
                        that.prevApplyPage = that.apply_currentPage;
                        that.$refs.tdTable.bodyWrapper.scrollTop = 0;
                    }
                    if (that.apply_tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
		        }
		    }).catch(function (e) {
		        console.log(e)
		    })
		},
		getloglist:function(){
			this.follow_currentPage++;
			this.getFollowList();
		},
		followTabChange:function(){
			this.follow_currentPage=1;
			this.FollowList = [];
			this.getFollowList();
		},
		async getFollowList(){
			let that = this;
			let params = {
				id:that.eid,
			    page: that.follow_currentPage,
			    followTab: that.followTab,
			}

			httpPost('m=crm&c=crm_chat&a=getFollowList', params).then((result) => {

			    var res = result.data;

			    if (res.error == 0) {
					if(res.data.last_page==0 || res.data.last_page<=that.follow_currentPage){
						that.followLast = true;
					}else{
						that.followLast = false;
					}

			        that.FollowList = that.FollowList.concat(res.data.list);
					that.follow_total =  res.data.total;
				}
			}).catch(function (e) {
			    console.log(e)
			})
		},
		openBoxShowInfo:function(e, v){
			var self = this;
			if (e == 'work'){
				self.workInfo = deepClone(v);
				if (v.edate_n == '至今') {
				    self.todayCheck = true;
				}
				self.workBoxShow = true;
			} else if (e == 'edu'){

				self.eduInfo = deepClone(v);
				self.eduBoxShow = true;
			} else if (e == 'train'){

				self.trainInfo = deepClone(v);
				self.trainBoxShow = true;

			} else if (e == 'project'){

				self.projectInfo = deepClone(v);
				self.projectBoxShow = true;
			} else if (e == 'skill'){

				self.skillInfo = deepClone(v);
				self.skillBoxShow = true;
			} else if (e == 'other'){

				self.otherInfo = deepClone(v);
				self.otherBoxShow = true;
			}
		},
		openBoxShow: function(e){
			var self = this;
			if (e == 'Info'){
				self.infodata = deepClone(self.Info);
				self.InfoBoxShow = true;
			} else if (e == 'Expect'){
				self.expectinfo = deepClone(self.Expect);
				self.jobSelected= self.expectinfo.jobnameArr;
				self.citySelected= self.expectinfo.citynameArr;

				self.expectinfo.minsalary = self.expectinfo.minsalary && self.expectinfo.minsalary > 0 ? parseInt(self.expectinfo.minsalary) : '';
				self.expectinfo.maxsalary = self.expectinfo.maxsalary && self.expectinfo.maxsalary > 0 ? parseInt(self.expectinfo.maxsalary) : '';

				let salaryList = deepClone(self.salaryList),
					maxsalaryList = [];
				salaryList.forEach(function(item, index) {
					if (index > 0) {
						if (self.expectinfo.maxsalary > 0) {
							if (parseInt(self.expectinfo.minsalary) < parseInt(item)) {
								maxsalaryList.push(item)
							}
						} else {
							maxsalaryList.push(item)
						}
					}
				})
				salaryList.splice(salaryList.length-1, 1);
				self.minsalaryList = salaryList;
				self.maxsalaryList = maxsalaryList;

				self.ExpectBoxShow = true;
			} else if (e == 'work'){
				this.todayCheck = false;
				self.workInfo = [];
				self.workInfo.eid = self.eid;
				self.workInfo.uid = self.Info.uid;
				self.workBoxShow = true;

			} else if (e == 'edu'){

				self.eduInfo = [];
				self.eduInfo.eid = self.eid;
				self.eduInfo.uid = self.Info.uid;
				self.eduBoxShow = true;

			} else if (e == 'train'){

				self.trainInfo = [];
				self.trainInfo.eid = self.eid;
				self.trainInfo.uid = self.Info.uid;
				self.trainBoxShow = true;

			} else if (e == 'project'){
				self.projectInfo = [];
				self.projectInfo.eid = self.eid;
				self.projectInfo.uid = self.Info.uid;
				self.projectBoxShow = true;

			} else if (e == 'skill'){

				self.skillInfo = {};
				self.skillInfo.eid = self.eid;
				self.skillInfo.uid = self.Info.uid;
				self.skillBoxShow = true;
			} else if (e == 'other'){


				self.otherInfo = {};
				self.otherInfo.eid = self.eid;
				self.otherInfo.uid = self.Info.uid;
				self.otherBoxShow = true;

			} else if (e == 'subscribe'){
				self.subscribeBoxShow = true;
			}
		},
		confirmSubJob:function(data){
			this.sub_job_class = data.jobId;

		},
		salaryChange(val) {
			let that = this,
				maxsalaryList = [],
				i = 0;
			this.salaryList.forEach(function(item, index) {
				if (parseInt(val) < parseInt(item)) {
					maxsalaryList.push(item)
					if (i === 0) {
						that.expectinfo.maxsalary = item;
					}
					i++;
				}
			})
			this.maxsalaryList = maxsalaryList;
		},
        saveSub:function(){

			var that = this;

			if(that.sub_job_class.length==0){
				message.warning('请选择工作职能'); return;
			}
			if(that.subtime==''){
				message.warning('请选择发送周期'); return;
			}

			var param = {};
			param.sid = that.subscribeid;
			param.jobclass_id = that.sub_job_class.join(',');
			param.time = that.subtime;
			param.uid = that.Info.uid;

			httpPost('m=crm&c=crm_chat&a=saveSub',param).then((result)=>{
			    that.subscribeBoxShow = false;
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						that.getList();
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})

		},
		auditResume:function(){

			var self = this;
			var param = {};

			if (self.Info.r_status == 2){
				var lock_status = self.Info.r_status;
				if (typeof lock_status !== 'undefined') {
					if (lock_status == '1') {
						params.lock_status = lock_status;
					} else {
						message.warning('锁定操作异常');
						return false;
					}
				}
			}
			var status = self.Info.state;
			if (typeof status === 'undefined') {
				message.warning('请选择审核状态');
				return false;
			}
			var statusbody = self.Info.statusbody;

			param.id = self.eid;
			param.uid = self.Info.uid;
			param.status = status;
			param.statusbody = statusbody;

			httpPost('m=crm&c=crm_chat&a=statusResume',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success('简历审核设置成功！',function(){
						self.Info.status = status;
						self.Info.statusbody = statusbody;
						self.Info.lock_status = 1;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})

		},
		saveResumeInfo:function(){

			var self = this;
			var info = self.infodata;

			if(isEmpty(info.name)){
				message.warning('个人姓名不能为空'); return;
			}
			if(isEmpty(info.sex)){
				message.warning('性别不能为空'); return;
			}
			if(isEmpty(info.birthday)){
				message.warning('出生年月不能为空'); return;
			}
			if(isEmpty(info.edu)){
				message.warning('请选择教育程度'); return;
			}
			if(isEmpty(info.exp)){
				message.warning('请选择工作经验'); return;
			}

			var param = {};
			param.id = self.eid;
			param.uid = self.Info.uid;
			param.name = info.name;
			param.sex = info.sex;
			param.birthday = info.birthday;
			param.edu = info.edu;
			param.exp = info.exp;
			param.description = info.description;
			param.type = 'Info';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.InfoBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		delResume: function (e, v, key) {
		    var self = this
		    self.$confirm('确定要删除该项内容？', '温馨提示', {
		        confirmButtonText: '确定',
		        cancelButtonText: '取消',
		        type: 'info'
		    }).then(() => {

				var param = {};
				param.eid = self.eid;
				param.uid = self.Info.uid;
				param.id = v;
				param.table = e;

		        httpPost('m=crm&c=crm_chat&a=delResumeFb',param).then(function (result) {
		            var res = result.data;
		            if (res.error == 0) {
		                message.success(res.msg,function(){
							if (e == 'work'){

								self.Info.user_work.splice(key, 1);
							} else if (e == 'edu'){

								self.Info.user_edu.splice(key, 1);
							} else if (e == 'training'){

								self.Info.user_tra.splice(key, 1);
							} else if (e == 'skill'){

								self.Info.user_skill.splice(key, 1);
							} else if (e == 'project'){

								self.Info.user_xm.splice(key, 1);
							} else if (e == 'other'){

								self.Info.user_other.splice(key, 1);
							}
						})
		            } else {
		                message.error(res.msg)
		                return false
		            }
		        }).catch(function (e) {

		        })
		    }).catch(() => {
		    });
		},
		saveResumeOther:function(){
			var self = this;
			var info = self.otherInfo;

			if(isEmpty(info.eid)){
				message.warning('请先完善求职意向！'); return;
			}
			if(isEmpty(info.name)){
				message.warning('请填写标题信息！'); return;
			}


			var param = {};
			param.uid = self.otherInfo.uid;
			param.eid = self.otherInfo.eid;
			param.id = self.otherInfo.id;
			param.name = info.name;
			param.content = info.content;
			param.type = 'other';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.otherBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		saveResumeProject:function(){
			var self = this;
			var info = self.projectInfo;

			if(isEmpty(info.eid)){
				message.warning('请先完善求职意向！'); return;
			}
			if(isEmpty(info.name)){
				message.warning('请填写项目名称！'); return;
			}
			if(isEmpty(info.sdate_n)){
				message.warning('项目时间不能为空！'); return;
			}
			if(!isEmpty(info.edate_n)){
				var sdate = info.sdate_n.replace(/-/g, "-");
				var edate = info.edate_n.replace(/-/g, "-");

				var sd = new Date(Date.parse(sdate));
				var ed = new Date(Date.parse(edate));

				if (sd >= ed) {
					message.warning('结束日期必须大于开始日期！');
					return false;
				}
			}


			var param = {};
			param.uid = self.projectInfo.uid;
			param.eid = self.projectInfo.eid;
			param.id = self.projectInfo.id;
			param.name = info.name;
			param.title = info.title;
			param.sdate = info.sdate_n;
			param.edate = info.edate_n;
			param.content = info.content;
			param.type = 'project';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.projectBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		saveResumeSkill:function(){
			var self = this;
			var info = self.skillInfo;

			if(isEmpty(info.eid)){
				message.warning('请先完善求职意向！'); return;
			}
			if(isEmpty(info.name)){
				message.warning('请填写技能名称！'); return;
			}
			if(isEmpty(info.ing)){
				message.warning('请选择技能熟练度！'); return;
			}

			var param = new FormData();
			param.append('uid', self.skillInfo.uid);
			param.append('eid', self.skillInfo.eid);
			param.append('id',self.skillInfo.id);
			param.append('name',info.name);
			param.append('longtime',info.longtime);
			param.append('ing',info.ing);
			if(typeof info.file!='undefined' && info.file){
				param.append('file',info.file);
			}
			param.append('type','skill');

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.skillBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		saveResumeTraining:function(){
			var self = this;
			var info = self.trainInfo;

			if(isEmpty(info.eid)){
				message.warning('请先完善求职意向！'); return;
			}
			if(isEmpty(info.name)){
				message.warning('请填写培训中心名称！'); return;
			}
			if(isEmpty(info.sdate_n)){
				message.warning('培训时间不能为空！'); return;
			}
			if(!isEmpty(info.edate_n)){
				var sdate = info.sdate_n.replace(/-/g, "-");
				var edate = info.edate_n.replace(/-/g, "-");

				var sd = new Date(Date.parse(sdate));
				var ed = new Date(Date.parse(edate));

				if (sd >= ed) {
					message.warning('结束日期必须大于开始日期！');
					return false;
				}
			}

			var param = {};
			param.uid = self.trainInfo.uid;
			param.eid = self.trainInfo.eid;
			param.id = self.trainInfo.id;
			param.name = info.name;
			param.title = info.title;
			param.sdate = info.sdate_n;
			param.edate = info.edate_n;
			param.content = info.content;
			param.type = 'train';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.trainBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		saveResumeEdu:function(){
			var self = this;
			var info = self.eduInfo;

			if(isEmpty(info.eid)){
				message.warning('请先完善求职意向！'); return;
			}
			if(isEmpty(info.name)){
				message.warning('请填写学校名称！'); return;
			}
			if(isEmpty(info.sdate_n)){
				message.warning('在校时间不能为空！'); return;
			}
			if(!isEmpty(info.edate_n)){
				var sdate = info.sdate_n.replace(/-/g, "-");
				var edate = info.edate_n.replace(/-/g, "-");

				var sd = new Date(Date.parse(sdate));
				var ed = new Date(Date.parse(edate));

				if (sd >= ed) {
					message.warning('结束日期必须大于开始日期！');
					return false;
				}
			}

			var param = {};
			param.uid = self.eduInfo.uid;
			param.eid = self.eduInfo.eid;
			param.id = self.eduInfo.id;
			param.name = info.name;
			param.specialty = info.specialty;
			param.sdate = info.sdate_n;
			param.edate = info.edate_n;
			param.education = info.education;
			param.type = 'edu';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.eduBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		saveResumeWork:function(){
			var self = this;
			var info = self.workInfo;

			if(isEmpty(info.eid)){
				message.warning('请先完善求职意向！'); return;
			}
			if(isEmpty(info.name)){
				message.warning('请填写单位名称！'); return;
			}
			if(isEmpty(info.sdate_n)){
				message.warning('工作时间不能为空！'); return;
			}
			if(!isEmpty(info.edate_n)){
				var sdate = info.sdate_n.replace(/-/g, "-");
				var edate = info.edate_n.replace(/-/g, "-");

				var sd = new Date(Date.parse(sdate));
				var ed = new Date(Date.parse(edate));

				if (sd >= ed) {
					message.warning('结束日期必须大于开始日期！');
					return false;
				}
			}

			var param = {};
			param.uid = self.workInfo.uid;
			param.eid = self.workInfo.eid;
			param.id = self.workInfo.id;
			param.name = info.name;
			param.title = info.title;
			param.sdate = info.sdate_n;
			param.edate = info.edate_n;
			param.content = info.content;
			param.type = 'work';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.workBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		saveResumeExpect:function(){

			var self = this;
			var info = self.expectinfo;

			if(isEmpty(info.name)){
				message.warning('请填写期望职位！'); return;
			}
			if(isEmpty(info.job_classid)){
				message.warning('请选择从事职位！'); return;
			}
			if(isEmpty(info.city_classid)){
				message.warning('请选择工作地点！'); return;
			}
			if(isEmpty(info.minsalary)){
				message.warning('请填写期望月薪！'); return;
			}
			if(!isEmpty(info.maxsalary) &&  parseInt(info.maxsalary) <= parseInt(info.minsalary)){
				message.warning('最高薪资必须大于最低薪资！'); return;
			}
			if(isEmpty(info.report)){
				message.warning('请选择到岗时间！'); return;
			}
			if(isEmpty(info.type)){
				message.warning('请选择工作性质！'); return;
			}
			if(isEmpty(info.jobstatus)){
				message.warning('请选择求职状态！'); return;
			}

			var param = {};
			param.id = self.eid;
			param.uid = self.Info.uid;
			param.name = info.name;
			param.job_classid = info.job_classid;
			param.city_classid = info.city_classid;
			param.minsalary = info.minsalary;
			param.maxsalary = info.maxsalary;
			param.hy = info.hy;
			param.report = info.report;
			param.typeid = info.type;
			param.jobstatus = info.jobstatus;
			param.type = 'Expect';

			httpPost('m=crm&c=crm_chat&a=saveResumeType',param).then((result)=>{
			    var res = result.data;
			    if (res.error == 0) {
			        message.success(res.msg,function(){
						self.Info = res.data.Info;
						self.Expect = res.data.Info.user_expect;
						self.ExpectBoxShow = false;
					}); return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		// 切换审核模板
		changeTpl(val) {
		    this.Info.statusbody = this.auditTpl;
		},
		// 上传时触发
		handleChangeSkillPic(file, fileList) {
		    this.$set(this.skillInfo, 'file', file.raw);
		    this.$set(this.skillInfo, 'pic', file.url);
		},
		// 至今选择
		todayChange(val, type) {
		    if (type == 'work') {
		        this.$set(this.workInfo, 'edate', '');
		    }
		},
		confirmJob(data) {
		    this.expectinfo.job_classid = data.jobId.join(',');
		},
		confirmCity(data) {
		    this.expectinfo.city_classid = data.cityId.join(',');
		},

		addFollow:function(){

			let self = this;
			var param = {};
			param.id = self.eid;
			param.uid = self.Info.uid;
			param.label = self.followInfo.label_id;
			param.content = self.followInfo.content;
			param.f_time = self.f_time;
			param.from = 1;

			httpPost('m=crm&c=crm_chat&a=label',param).then((result) => {

				var res = result.data;
				if (res.error == 0) {
					self.followTips = true;
					self.f_time = '';
					self.FollowList = [];
					self.getFollowList();
				}else{
					message.error(res.msg); return;
				}

			}).catch(function (e) {
				console.log(e)
			})
		}
    },
};
</script>
<style>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 57px;
    margin: 3px 0;
}
/* 上传样式开始 */
.avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.avatar-uploader .el-upload:hover {
    border-color: #409EFF;
}

.avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 100px;
    height: 100px;
    line-height: 100px;
    text-align: center;
}

.avatar {
    width: 100px;
    height: 100px;
    display: block;
}

.dialoItemCoy{
	overflow: hidden;
	position: relative;
	width: 100%;
}
.dialoItemTte{
	overflow: hidden;
	position: relative;
	width: 100%;
	font-size: 15px;
	color: #333;
	font-weight: bold;
	padding-bottom: 8px;
}

.dialoItemCoy .drawerModInpt{
	overflow: hidden;
	position: relative;
	width: 100%;
	padding: 0 0 18px 0;
}

</style>