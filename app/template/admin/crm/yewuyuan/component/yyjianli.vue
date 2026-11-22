<template>
	<div class="moduleElHight">
		<div class="moduleElTabSeach">
			<div class="moduleElTabInpt">
				<div class="moduleInptList">
					<el-input size="small" @keyup.enter.native="search" placeholder="请输入内容" v-model="keyword"
						class="input-with-select" clearable>
						<el-select v-model="keytype" slot="prepend" placeholder="请选择">
							<el-option label="用户姓名" value="1"></el-option>
							<el-option label="期望职位" value="3"></el-option>
							<el-option label="用户名" value="4"></el-option>
							<el-option label="手机号" value="5"></el-option>
							<el-option label="教育经历" value="6"></el-option>
							<el-option label="工作经历" value="7"></el-option>
							<el-option label="项目经历" value="8"></el-option>
							<el-option label="培训经历" value="9"></el-option>
							<el-option label="职业技能" value="10"></el-option>
							<el-option label="来源IP" value="11"></el-option>
						</el-select>
					</el-input>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="status" placeholder="简历审核" clearable @change="search">
						<el-option label="已审核" value="1"></el-option>
						<el-option label="已锁定" value="2"></el-option>
						<el-option label="未通过" value="3"></el-option>
						<el-option label="未审核" value="4"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="uptime" placeholder="更新时间" clearable @change="search">
						<el-option label="今天" value="1"></el-option>
						<el-option label="最近三天" value="3"></el-option>
						<el-option label="最近七天" value="7"></el-option>
						<el-option label="最近半月" value="15"></el-option>
						<el-option label="最近一个月" value="30"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="source" placeholder="数据来源" clearable @change="search">
						<el-option v-for="(item, index) in source_arr" :key="index" :label="item" :value="index"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="adtime" placeholder="添加时间" clearable @change="search">
						<el-option label="今天" value="1"></el-option>
						<el-option label="最近三天" value="3"></el-option>
						<el-option label="最近七天" value="7"></el-option>
						<el-option label="最近半月" value="15"></el-option>
						<el-option label="最近一个月" value="30"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="type" placeholder="工作性质" clearable @change="search">
						<el-option v-for="item in type_arr" :key="item.val" :label="item.name"
							:value="item.val"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="edu" placeholder="最高学历" clearable @change="search">
						<el-option v-for="item in edu_arr" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="exp" placeholder="工作经验" clearable @change="search">
						<el-option v-for="item in exp_arr" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="report" placeholder="到岗时间" clearable @change="search">
						<el-option v-for="item in report_arr" :key="item.val" :label="item.name"
							:value="item.val"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="integrity" placeholder="简历完整度" clearable @change="search">
						<el-option v-for="(item, index) in integrity_arr" :key="index" :label="item"
							:value="index"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="salary" placeholder="期望薪资" clearable @change="salaryChange">
						<el-option label="2000-4000" value="2000_4000"></el-option>
						<el-option label="4000-6000" value="4000_6000"></el-option>
						<el-option label="6000-8000" value="6000_8000"></el-option>
						<el-option label="8000-10000" value="8000_10000"></el-option>
						<el-option label="10000以上" value="10000"></el-option>
						<el-option label="自定义薪资" value="-1"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }" v-if="salary == -1">
					<el-input size="small" v-model="minsalary" placeholder="最低薪资" style="width: 110px;padding-right: 3px;"
						clearable></el-input>
					<el-input size="small" v-model="maxsalary" placeholder="最高薪资" style="width: 110px;"
						clearable></el-input>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="age" placeholder="年龄范围" clearable @change="ageChange">
						<el-option label="16_20岁" value="16_20"></el-option>
						<el-option label="21_30岁" value="21_30"></el-option>
						<el-option label="31_40岁" value="31_40"></el-option>
						<el-option label="41_50岁" value="41_50"></el-option>
						<el-option label="50岁以上" value="50"></el-option>
						<el-option label="自定义年龄" value="-1"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }" v-if="age == -1">
					<el-input size="small" v-model="minage" placeholder="最小年龄" style="width: 110px; padding-right: 3px;"
						clearable></el-input>
					<el-input size="small" v-model="maxage" placeholder="最大年龄" style="width: 110px;" clearable></el-input>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="remark" placeholder="是否跟进" clearable @change="search">
						<el-option label="是" value="1"></el-option>
						<el-option label="否" value="2"></el-option>
					</el-select>
				</div>

				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="job_class" filterable remote placeholder="输入搜索职能"
						:remote-method="jobclass_search">
						<el-option v-for="item in jobclass_list" :key="item.value" :label="item.name" :value="item.value"
							:disabled="item.disabled">
							<span :style="job_class == item.value ? 'color:#409eff' : ''">
								<span style="float: left; font-size: 14px;font-weight:bold;">{{ item.name }}</span>
								<span style="float: right; color: #a5a5a5; font-size: x-small;"
									v-if="item.upname != ''">{{ item.upname }}</span>
							</span>
						</el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					<el-select size="small" v-model="city_class" filterable remote placeholder="输入搜索城市"
						:remote-method="cityclass_search">
						<el-option v-for="item in cityclass_list" :key="item.value" :label="item.name" :value="item.value"
							:disabled="item.disabled">
							<span :style="city_class == item.value ? 'color:#409eff' : ''">
								<span style="float: left; font-size: 14px;font-weight:bold;">{{ item.name }}</span>
								<span style="float: right; color: #a5a5a5; font-size: x-small;"
									v-if="item.upname != ''">{{ item.upname }}</span>
							</span>
						</el-option>
					</el-select>
				</div>
				<div class="moduleInptList" :class="{ 'searchbutnKai': seachbutn }"
					style="display: flex; align-items: center;">
					<el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
					<el-button type="primary" class="zhankai" @click="seachbutn = !seachbutn, tableHig = !tableHig"
						size="mini" plain>展开<i class="el-icon-arrow-down el-icon--right"></i></el-button>
					<el-button type="primary" class="shouqi" @click="seachbutn = !seachbutn, tableHig = !tableHig"
						size="mini" plain>合并<i class="el-icon-arrow-up el-icon--right"></i></el-button>
				</div>
			</div>
			<div class="moduleSeachButn">
				<el-button type="primary" icon="el-icon-document-add" size="mini" @click="openBox">求职助手</el-button>
			</div>
		</div>
		<div class="moduleElTable" :class="{ 'moduleKaidYyJianl': tableHig }"
			style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
			<el-table ref="multipleTable" :data="tableData" v-loading="list_loading" stripe style="width: 100%"
				@mousedown.native="mouseDownHandler"
                @mouseup.native="mouseUpHandler"
                @mousemove.native="mouseMoveHandler"
				:empty-text="emptytext" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
				<el-table-column label="姓名/电话/归属" min-width="150">
					<template slot-scope="props">
						<div class=" ">
							<div>{{ props.row.uname }}
								<el-tooltip class="item" effect="dark" :content="r_status_arr[props.row.r_status]"
									placement="right" v-if="props.row.r_status != '1'">
<!--									<img src="../../images/bg_wechat_help.png">-->
                                    <i class="el-tooltip el-icon-lock item" style="color: orange;"></i>
								</el-tooltip>
							</div>
							<div>{{ props.row.moblie }}</div>
							<div class="gsd" v-if="props.row.moblie_address">{{ props.row.moblie_address }}</div>
							<span v-else-if="props.row.moblie"><el-link type="primary" :underline="false"
									@click="getmobileaddress(props.row)">查询归属地</el-link></span>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="基本信息" min-width="240">
					<template slot-scope="props">
						<div class=" ">
							<div class="moduleProList moduleProOne">
								<span>{{ props.row.name }}</span>
							</div>
							<div class="moduleProList">
								<span>{{ props.row.age_n }}岁</span>
								<span v-if="props.row.sex_n">{{ props.row.sex_n }}</span>
								<span v-if="props.row.edu_n">{{
									props.row.edu_n }}学历</span>&nbsp;{{ props.row.exp_n ? props.row.exp_n + '经验' : '' }}
							</div>
							<div class="gsd">
								<span v-if="props.row.city_n != ''">{{ props.row.city_n }}
									<el-tooltip class="item" effect="dark" :content="props.row.cityall" placement="right"
										v-if="props.row.citynum > 1">
										<img src="../../images/bg_wechat_help.png">
									</el-tooltip>
								</span>

							</div>
							<!--<div class="gsd"> <span>{{ props.row.salary }}</span>
                                <span>{{ props.row.report_n }}</span>
                                <span>{{ props.row.type_n }}</span>
								</div>-->
						</div>
					</template>
				</el-table-column>

                <el-table-column label="个人认证" min-width="110" show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div class="rz_box">
                            <el-tooltip v-if="scope.row.idcard_status == 1" class="item" effect="dark" content="身份已认证"
                                        placement="top-start">
                                <el-button type="text" @click="idcardRz(scope.row)">
                                    <i class="rzicon rzicon_zzyrz"></i>
                                </el-button>
                            </el-tooltip>
                            <el-tooltip v-else class="item" effect="dark" content="身份未认证" placement="top-start">
                                <el-button type="text" @click="idcardRz(scope.row)">
                                    <i class="rzicon rzicon_zzwrz"></i>
                                </el-button>
                            </el-tooltip>
                            <el-tooltip v-if="scope.row.moblie_status == 1" class="item" effect="dark" content="手机已认证"
                                        placement="top-start">
                                <el-button type="text" @click="moblieRz(scope.row)">
                                    <i class="rzicon rzicon_sjyrz"></i>
                                </el-button>
                            </el-tooltip>
                            <el-tooltip v-else class="item" effect="dark" content="手机未认证" placement="top-start">
                                <el-button type="text" @click="moblieRz(scope.row)">
                                    <i class="rzicon rzicon_sjwrz"></i>
                                </el-button>
                            </el-tooltip>
                        </div>
                        <div class="rz_box">
                            <el-tooltip v-if="scope.row.email_status_n == 1" class="item" effect="dark" content="邮箱已认证"
                                        placement="top-start">
                                <el-button type="text" @click="emailRz(scope.row)">
                                    <i class="rzicon rzicon_yxyrz"></i>
                                </el-button>
                            </el-tooltip>
                            <el-tooltip v-else class="item" effect="dark" content="邮箱未认证" placement="top-start">
                                <el-button type="text" @click="emailRz(scope.row)">
                                    <i class="rzicon rzicon_yxwrz"></i>
                                </el-button>
                            </el-tooltip>
                            <el-tooltip v-if="scope.row.wxid != ''"
                                        class="item" effect="dark" placement="top-start">
                                <div slot="content" v-html="'微信已认证<br/>'"></div>
                                <el-button type="text">
                                    <i class="rzicon rzicon_wxyrz"></i>
                                </el-button>
                            </el-tooltip>
                            <el-tooltip v-else class="item" effect="dark" placement="top-start">
                                <div slot="content" v-html="'微信未认证<br/>'"></div>
                                <el-button type="text" @click="showQrcode(scope.row.uid, scope.row.wxid)">
                                    <i class="rzicon rzicon_wxwrz"></i>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>





				<el-table-column label="完整度/时间节点" min-width="150">
					<template slot-scope="props">
						<el-tag type="danger" size="mini" effect="dark"
							v-if="props.row.integrity < 65">{{ props.row.integrity }}%</el-tag>
						<el-tag type="success" size="mini" effect="dark" v-else>{{ props.row.integrity }}%</el-tag>

						<div class="gsd" style="padding-top:8px;;">{{
							props.row.ctime ? props.row.ctime_n : props.row.lastupdate_t }}发布</div>
						<div class="gsd">{{ props.row.lastupdate_t }}更新</div>
					</template>
				</el-table-column>
				<el-table-column label="跟进信息" min-width="180">
					<template slot-scope="props">
						<div class=" " v-if="props.row.content">
							<el-popover placement="right" trigger="hover"
								:disabled="typeof props.row.content == 'undefined' || props.row.content == ''">
								<div style='overflow: hidden;'>
									<div
										style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>
										最近跟进记录</div>
									<div>
										<div
											style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
											<span style='font-weight: bold;'>标记简历：</span>{{ props.row.label_n }}</div>
										<div
											style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
											<span style='font-weight: bold;' v-html="'跟进内容：' + props.row.content"></span></div>
									</div>
								</div>
								<span slot="reference" style="color: #ff6600; width: max-content;">{{ props.row.content
								}}</span>
							</el-popover>
						</div>
						<div v-else>--</div>
					</template>
				</el-table-column>
                <el-table-column prop="bzxinxi" label="隐私状态" min-width="100">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <span v-if="props.row.status == '1'">公开</span>
                            <span v-else-if="props.row.status == '3'">投递可见</span>
                            <span v-else>隐藏</span>
                            <el-button type="text" @click="changeStatus(props.row)" style="padding: 0;">修改</el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="shzhuat" label="审核状态" width="130">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <div v-if="props.row.r_status == '2'">
                                <span class="admin_state3">已锁定</span>
                                <div style="display:inline-block" v-if="props.row.lock_info">
                                    <el-popover trigger="hover" placement="right">
                                        <p>{{ props.row.lock_info }}</p>
                                        <div slot="reference" class="name-wrapper">
                                            <i class="el-icon-question el-icon--right"></i>
                                        </div>
                                    </el-popover>
                                </div>
                            </div>
                            <span class="admin_state1" v-else-if="props.row.state == '1'">已审核</span>
                            <span class="admin_state2" v-else-if="props.row.state == '3'">未通过</span>
                            <span class="admin_state3" v-else-if="props.row.state == '0'">未审核</span>
                            <span class="admin_state4" v-else-if="props.row.state == '2'">被举报 </span>
                        </div>
                    </template>
                </el-table-column>

				<el-table-column label="企业微信" min-width="90">
					<template slot-scope="props">
						<div class=" ">
							<el-tooltip class="item" effect="dark" content="微群客户" placement="bottom-start">
								<span :class="props.row.isGroup == '1' ? 'crmChatGroupxz' : 'crmChatGroupwxz'" title="微群客户"></span>
							</el-tooltip>
							<el-tooltip class="item" effect="dark" content="员工客户" placement="bottom-start">
								<span :class="props.row.isCustomer == '1' ? 'crmChatCustomerxz' : 'crmChatCustomerwxz'" title="员工客户"></span>
							</el-tooltip>
						</div>
					</template>
				</el-table-column>

				<el-table-column label="投递/聊天量" min-width="100">
					<template slot-scope="props">
						<div class=" "> <el-link type="primary" :underline="false" @click="ujShow(props.row)">{{
							props.row.sq_num }}</el-link>/<el-link type="primary" :underline="false"
								@click="clShow(props.row)">{{ props.row.chat_num }}</el-link></div>
					</template>
				</el-table-column>
                <el-table-column label="来源/IP/归属" min-width="160">
					<template slot-scope="props">
						<div> {{ props.row.source_n }}</div>
						<div class="gsd" v-if="props.row.add_ip"> {{ props.row.add_ip }}</div>
						<div class="gsd" v-if="props.row.ip_address"> {{ props.row.ip_address }}</div>

					</template>
				</el-table-column>
				<el-table-column fixed="right" label="操作" width="140">
					<template slot-scope="props">
						<div class="moduleElTaCaoz">
							<el-button size="mini" @click="crmLabel(props.row)">跟进</el-button>
							<el-button size="mini" @click="enterRoom(props.row)">沟通</el-button>
							<el-button size="mini" @click="auditShow(props.row)">管理</el-button>
							<el-tooltip v-if="isOutcall && props.row.moblie" class="item" effect="dark" content="通过虚拟号联系客户"
								placement="bottom">
								<el-button size="mini" @click="contact_customer(props.row)">拨号</el-button>
							</el-tooltip>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
					:current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
					layout="total, sizes, prev, pager, next, jumper">
				</el-pagination>
			</div>
		</div>
		<div class="modluDrawer">
			<el-drawer :close-on-press-escape="false" :wrapper-closable="false" title="投递职位详情" :visible.sync="uj_show"
				:modal-append-to-body="false" append-to-body :show-close="true" :with-header="true" size="60%">
				<useridjob v-if="uj_show" :uid="uj_uid" :eid="uj_eid"></useridjob>
			</el-drawer>
		</div>
		<div class="modluDrawer">
			<el-drawer :close-on-press-escape="false" :wrapper-closable="false" title="聊天人数详情" :visible.sync="cl_show"
				:modal-append-to-body="false" append-to-body :show-close="true" :with-header="true" size="60%">
				<chatloglist v-if="cl_show" :uid="cl_uid"></chatloglist>
			</el-drawer>
		</div>
		<div class="modluDrawer">
			<el-drawer :close-on-press-escape="false" :wrapper-closable="false" title="客勤" :visible.sync="chatroomShow"
				:modal-append-to-body="false" append-to-body :show-close="true" :with-header="true" size="80%">
				<crmchatroom v-if="chatroomShow" :uid="chatuid"></crmchatroom>
			</el-drawer>
		</div>
		<div class="modluDrawer">
			<el-drawer title="简历管理" :visible.sync="audit_show" :modal-append-to-body="false" append-to-body
				:show-close="true" :with-header="true" size="80%">
				<useraudit v-if="audit_show" :eid="audit_id"></useraudit>
			</el-drawer>
		</div>
		<el-dialog title="修改状态" width="430px" :visible.sync="statusshow" :modal-append-to-body="false" append-to-body>
			<div class="modluxiugaiztai">
				<div class="dialog_item">
					<span>个人姓名：</span>
					<div class="drawerModInpt">
						{{ statusname }}
					</div>
				</div>
				<div class="dialog_item">
					<span>隐私状态：</span>
					<div class="drawerModInpt">
						<el-select v-model="status_v" placeholder="请选择">
							<el-option label="公开" value="1"></el-option>
							<el-option label="投递企业可见" value="3"></el-option>
							<el-option label="隐藏" value="2"></el-option>
						</el-select>
					</div>
				</div>
			</div>
			<span slot="footer" class="dialog-footer">
				<el-button @click="statusshow = false">取 消</el-button>
				<el-button type="primary" :loading="status_load" @click="setStatus">确 定</el-button>
			</span>
		</el-dialog>
		<el-dialog title="跟进简历" width="430px" :visible.sync="labelshow" :modal-append-to-body="false" append-to-body>
			<div class="modluxiugaiztai">
				<div class="dialog_item">
					<span>个人姓名：</span>
					<div class="drawerModInpt">
						{{ labelname }}
					</div>
				</div>
				<div class="dialog_item">
					<span>标记简历：</span>
					<div class="drawerModInpt">
						<el-select v-model="label" placeholder="请选择">
							<el-option v-for="item in label_arr" :key="item.val" :label="item.name"
								:value="item.val"></el-option>
						</el-select>
					</div>
				</div>
				<div class="dialog_item">
					<span>跟进内容：</span>
					<div class="drawerModInpt">
						<el-input type="textarea" v-model="label_content" style="flex: 1;"></el-input>
					</div>
				</div>
			</div>
			<span slot="footer" class="dialog-footer">
				<el-button @click="labelshow = false">取 消</el-button>
				<el-button type="primary" :loading="label_load" @click="setLabel">确 定</el-button>
			</span>
		</el-dialog>



        <!--身份认证弹窗-->
        <div class="modluDrawer">
            <el-dialog title="身份认证" :visible.sync="dialogIdcardRz" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="450px">
                <div>
                    <div class="wxsettip_small ">姓名</div>
                    <el-input :value="detail.uname" :disabled="true"></el-input>
                    <div class="wxsettip_small ">身份证号码</div>
                    <el-input :value="detail.idcard" :disabled="true"></el-input>
                    <div class="wxsettip_small ">身份证图片</div>
                    <div class="zzrz_img">
                        <div class="zzrz_imgpreview">
                            <el-image v-if="detail.idcard_pic" style="width: 80px; height: 80px" :src="detail.idcard_pic"
                                      :preview-src-list="detail.idcard_pic">
                            </el-image>
                            <span v-else>暂无身份证图片</span>
                        </div>
                    </div>
                    <div class="wxsettip_small ">审核操作</div>
                    <el-radio v-model="ruleFormIdcardRz.r_status" label="0">待认证</el-radio>
                    <el-radio v-model="ruleFormIdcardRz.r_status" label="1">已认证</el-radio>
                    <div class="wxsettip_small ">审核说明</div>
                    <el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormIdcardRz.statusbody"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogIdcardRz = false">取 消</el-button>
                    <el-button type="primary" @click="idcardRzSubmit">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--手机认证弹窗-->
        <div class="modluDrawer">
            <el-dialog title="手机认证" :visible.sync="dialogMoblieRz" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="450px">
                <div>
                    <div class="wxsettip_small ">手机号</div>
                    <el-input placeholder="" v-model="ruleFormMobileRz.moblie"></el-input>
                    <div class="wxsettip_small ">审核操作</div>
                    <el-radio v-model="ruleFormMobileRz.mstatus" label="1">已认证</el-radio>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogMoblieRz = false">取 消</el-button>
                    <el-button type="primary" @click="moblieRzSubmit">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--邮箱认证弹窗-->
        <div class="modluDrawer">
            <el-dialog title="邮箱认证" :visible.sync="dialogEmailRz" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="450px">
                <div>
                    <div class="wxsettip_small ">邮箱号</div>
                    <el-input placeholder="" v-model="ruleFormEmailRz.email"></el-input>
                    <div class="wxsettip_small ">审核操作</div>
                    <el-radio v-model="ruleFormEmailRz.estatus" label="1">已认证</el-radio>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogEmailRz = false">取 消</el-button>
                    <el-button type="primary" @click="emailRzSubmit">确 定</el-button>
                </span>
            </el-dialog>
        </div>
	</div>
</template>

<script>
module.exports = {
	props: {
		ly: { type: String, default: '' },
	},
	data: function () {
		return {


			mouseFlag: false,
            mouseOffset: 0,


			emptytext: '暂无数据',
			r_status_arr: ['个人身份未审核', '已审核', '用户已锁定', '个人身份审核未通过'],
			source_arr: [],
			type_arr: [],
			edu_arr: [],
			exp_arr: [],
			report_arr: [],
			integrity_arr: [],
			label_arr: [],
			jobclass_list: [],
			cityclass_list: [],
			seachbutn: true,
			tableHig: false,
			tableData: [],
			total: 0,
			limit: 0,
			currentPage: 1,
			prevPage: 0,
			page_sizes: [],

			list_loading: false,

			allchecked: false,
			choosedata: [],

			keyword: '',
			keytype: '1',
			status: '',
			uptime: '',
			source: '',
			adtime: '',
			type: '',
			edu: '',
			exp: '',
			report: '',
			salary: '',
			minsalary: '',
			maxsalary: '',
			age: '',
			minage: '',
			maxage: '',
			remark: '',
			integrity: '',
			city_class: '',
			job_class: '',
			sort_t: '',
			order: '',

			isOutcall: false,

			statusshow: false,
			status_uid: '',
			status_v: '',
			statusname: '',

			labelshow: false,
			labelid: '',
			labelname: '',
			label: '',
			label_content: '',
			labeluid: '',

			uj_show: false,
			uj_eid: '',
			uj_uid: '',

			cl_show: false,
			cl_uid: '',

			audit_show: false,
			audit_id: '',

			chatroomShow: false,
			chatuid: '',

			firstOpen: true, // 头部客勤首次弹窗
			status_load: false,
			label_load: false,

            // 身份证认证
            dialogIdcardRz: false,
            ruleFormIdcardRz: {},
            // 手机认证
            dialogMoblieRz: false,
            ruleFormMobileRz: {},
            // 邮件认证
            dialogEmailRz: false,
            ruleFormEmailRz: {},
            detail:{}

        }
	},
	components: {
		'useridjob': httpVueLoader('./useridjob.vue'),
		'chatloglist': httpVueLoader('./chatloglist.vue'),
		'useraudit': httpVueLoader('./useraudit.vue'),
		'crmchatroom': httpVueLoader('./crmchatroom.vue'),
	},
	mounted() {
		this.getStaticData();
		this.getList();
	},
	methods: {
		mouseDownHandler(e) {
            this.mouseOffset = e.clientX;
            this.mouseFlag = true;
        },
        mouseUpHandler(e) {
            this.mouseFlag = false;
        },
        mouseMoveHandler(e) {
            // 这里面需要注意，通过ref需要那个那个包含table元素的父元素
            let divData = this.$refs.multipleTable.bodyWrapper;
            if (this.mouseFlag) {
                // 设置水平方向的元素的位置
                divData.scrollLeft -= (- this.mouseOffset + (this.mouseOffset = e.clientX));
            }
        },
		getStaticData() {
			var that = this
			let params = {}
			httpPost('m=crm&c=crm_chat&a=getIndexStaticData', params, { hideloading: true }).then((result) => {
				var res = result.data;
				if (res.error == 0) {
					that.isOutcall = res.data.isOutcall
					that.sy_chat_open = res.data.sy_chat_open
					that.source_arr = res.data.source
					that.integrity_arr = res.data.integrity_arr
					that.type_arr = res.data.type_arr
					that.edu_arr = res.data.edu_arr
					that.exp_arr = res.data.exp_arr
					that.report_arr = res.data.report_arr
					that.label_arr = res.data.label_arr
					if (that.ly == 'index' && that.firstOpen) {
						that.firstOpen = false;
						that.openBox();
					}
				}
			});
		},
		async getList() {
			let that = this;
			let params = {
				page: that.currentPage,
				limit: that.limit,
				t: that.sort_t,
				order: that.order,
				keyword: that.keyword,
				keytype: that.keytype,
				status: that.status,
				uptime: that.uptime,
				source: that.source,
				adtime: that.adtime,
				type: that.type,
				edu: that.edu,
				exp: that.exp,
				report: that.report,
				remark: that.remark,
				integrity: that.integrity,
				city_class: that.city_class,
				job_class: that.job_class,
				sy_chat_open: '',
			};
			var salary = that.salary == -1 ? '' : that.salary;
			if (that.minsalary != '') {
				salary = that.minsalary;
				if (that.maxsalary != '') {
					salary += '_' + that.maxsalary;
				}
			}
			params.salary = salary;

			var age = that.age == -1 ? '' : that.age;
			if (that.minage != '') {
				age = that.minage;
				if (that.maxage != '') {
					age += '_' + that.maxage;
				}
			}

			params.age = age;

			this.list_loading = true;
			that.emptytext = "数据加载中";
			httpPost('m=crm&c=crm_chat&a=index', params).then((result) => {
				this.list_loading = false;
				var res = result.data;

				if (res.error == 0) {
					that.tableData = res.data.list
					that.total = parseInt(res.data.total)
					that.page_sizes = res.data.page_sizes
					that.limit = res.data.page_size
					if (that.prevPage != that.currentPage) {
						that.prevPage = that.currentPage;
						that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
						scrollToTop()
					}
					if (that.tableData.length === 0) {
						that.emptytext = "暂无数据";
					}

				}
			}).catch(function (e) {
				console.log(e)
			})
		},

		search: function () {
			this.currentPage = 1;
			this.getList();
		},

		handleCurrentChange(val) {
			this.currentPage = val;
			this.getList()
		},
		handleSizeChange(val) {
			this.currentPage = 1
			this.limit = val
			scrollToTop()
			this.getList()
		},
		contact_customer: function (row) {

			var phone = row.moblie;
			var uid = row.uid;

			var params = {
				mobile: phone,
				uid: uid,
			};

			message.confirm('确定联系客户吗？', function () {
				httpPost('m=crm&c=crm_chat&a=contactCustomer', params).then(function (result) {

					var res = result.data;
					if (res.error == '200') {
						message.success(res.msg);
					} else {
						message.error(res.msg); return;
					}
				}).catch(function (e) {
					console.log(e)
				})
			});
		},

		getmobileaddress: function (row) {
			var that = this;
			var tableData = deepClone(that.tableData);
			var params = {
				uid: row.uid,
				moblie: row.moblie
			};
			httpPost('m=index&c=getMobileAddress', params).then(function (result) {
				this.bzshow = false;
				var res = result.data;
				if (res.error == 0) {
					message.success('查询成功', function () {
						for (let i in tableData) {
							if (tableData[i].uid == row.uid) {
								tableData[i].moblie_address = res.msg;
								break;
							}
						}
						that.tableData = tableData;
					});

					return;
				} else {
					message.error(res.msg); return;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		jobclass_search: function (query) {
			this.jobclass_list = jobcityclass_slist({ keyword: query, type: 'jobclass', choosed: [this.job_class] });
		},

		cityclass_search: function (query) {
			this.cityclass_list = jobcityclass_slist({ keyword: query, type: 'cityclass', choosed: [this.city_class] });
		},
		salaryChange: function () {
			var that = this;
			if (that.salary != '') {
				that.minsalary = '';
				that.maxsalary = '';
			}
			if (that.salary != '-1') {
				that.search();
			}
		},
		ageChange: function () {
			var that = this;
			if (that.age != '') {
				that.minage = '';
				that.maxage = '';
			}
			if (that.age != '-1') {
				that.search();
			}
		},
		changeStatus: function (row) {
			this.statusname = row.uname;
			this.status_uid = row.uid;
			this.status_v = row.status;
			this.statusshow = true;
		},
		setStatus: function () {
			var that = this;

			var params = {
				uid: that.status_uid,
				status: that.status_v
			};
			that.status_load = true;
			httpPost('m=user&c=users_resume&a=cstatus', params).then((result) => {
				that.status_load = false;
				this.statusshow = false;
				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, function () { that.getList() }); return;
				} else {
					message.error(res.msg); return;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		crmLabel: function (row) {

			this.labelid = row.id;
			this.labelname = row.uname;
			this.label = row.label;
			this.label_content = row.content_n;
			this.labeluid = row.uid;
			this.labelshow = true;
		},
		setLabel: function () {
			var that = this;

			var params = {
				uid: that.labeluid,
				id: that.labelid,
				label: that.label,
				content: that.label_content,
			};
			that.label_load = true;
			httpPost('m=crm&c=crm_chat&a=label', params).then((result) => {
				that.label_load = false;
				this.labelshow = false;
				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, function () { that.getList() }); return;
				} else {
					message.error(res.msg); return;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		ujShow: function (row) {
			this.uj_uid = row.uid;
			this.uj_eid = row.id;
			this.uj_show = true;
		},
		clShow: function (row) {
			this.cl_uid = row.uid;
			this.cl_show = true;
		},
		auditShow: function (row) {
			this.audit_id = row.id;
			this.audit_show = true;
		},
		enterRoom: function (row) {
			if (this.sy_chat_open != '1') {
				message.error('请先到“工具-聊天-聊天设置”中打开聊天'); return;
			}
			startLoading();

			httpPost('m=crm&c=crm_chat&a=enterRoom', { uid: row.uid }).then((result) => {
				endLoading();
				var res = result.data;
				if (res.error == 0) {
					this.chatuid = row.uid;
					this.chatroomShow = true;
				} else {
					message.error('请选择要沟通的用户'); return;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		openBox() {
			if (this.sy_chat_open != '1') {
				message.error('请先到“工具-聊天-聊天设置”中打开聊天'); return;
			}
			this.chatuid = '';
			this.chatroomShow = true;
		},

        // 身份证认证
        idcardRz(row) {
            this.detail = row;
            this.ruleFormIdcardRz = {
                uid: row.uid,
                r_status: row.idcard_status,
                statusbody: ''
            }
            this.dialogIdcardRz = true;
        },
        idcardRzSubmit() {
            let that = this,
                ruleForm = this.ruleFormIdcardRz;

            if (typeof ruleForm.r_status == 'undefined' || ruleForm.r_status === '') {
                message.error('请选择审核状态');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=user&c=users_member&a=usercert', ruleForm).then(function (response) {
                let res = response.data;

                that.saveLoading = false;
                if (res.error > 0) {
                    message.error(res.msg);
                } else {
                    that.dialogIdcardRz = false;
                    that.getList();
                    that.$refs.multipleTable.clearSelection();
                    message.success(res.msg)
                }
            })
        },
        // 手机认证
        moblieRz(row) {
            this.detail = row;
            this.ruleFormMobileRz = {
                uid: row.uid,
                moblie: row.telphone,
                mstatus: row.moblie_status
            }
            this.dialogMoblieRz = true;
        },
        moblieRzSubmit() {
            let that = this,
                ruleForm = this.ruleFormMobileRz;

            if (!ruleForm.moblie) {
                message.error('请输入手机号');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=user&c=users_member&a=usercert', ruleForm).then(function (response) {
                let res = response.data;

                that.saveLoading = false;
                if (res.error > 0) {
                    message.error(res.msg);
                } else {
                    that.dialogMoblieRz = false;
                    that.getList();
                    that.$refs.multipleTable.clearSelection();
                    message.success(res.msg)
                }
            })
        },
        // 邮件认证
        emailRz(row) {
            this.detail = row;
            this.ruleFormEmailRz = {
                uid: row.uid,
                email: row.email,
                estatus: row.email_status_n,
            };
            this.dialogEmailRz = true;
        },
        emailRzSubmit() {
            let that = this,
                ruleForm = this.ruleFormEmailRz;

            if (!ruleForm.email) {
                message.error('请输入邮箱');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=user&c=users_member&a=usercert', ruleForm).then(function (response) {
                let res = response.data;

                that.saveLoading = false;
                if (res.error > 0) {
                    message.error(res.msg);
                } else {
                    that.dialogEmailRz = false;
                    that.getList();
                    that.$refs.multipleTable.clearSelection();
                    message.success(res.msg)
                }
            })
        },
        // 获取微信绑定二维码，显示微信绑定弹窗
        showQrcode: function (uid, wxid) {
            var that = this
            if (wxid != '') {
                message.error('企业已绑定微信');
                return false;
            }
            httpPost('m=crm&c=crm_chat&a=acwxbind', {acwxbind: 1, uid: uid}).then(function (result) {
                var res = result.data;
                if (res.error == 0) {
                    that.code_img = res.data.code_url;
                    that.wxrztc = true;
                    that.setval = setInterval(function () {
                        that.wxbindstatus(uid)
                    }, 2000);
                } else {
                    message.error(res.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        // 微信绑定绑定检测
        async wxbindstatus(comid) {
            var that = this;
            httpPost('m=user&c=crm_chat&a=getacbindstatus', {comid: comid}).then(function (result) {
                var res = result.data;
                if (res.error == 0) {
                    message.success('绑定成功', function () {
                        clearInterval(that.setval);
                    });
                }
            }).catch(function (e) {
                console.log(e)
            })
        },


    },
};
</script>
<style>
	.moduleElTaCaoz{justify-content:space-between}
	.moduleElTaCaoz .el-button{width:47%;margin:3px 0}
	.moduleElTabInpt{flex-wrap:wrap;width:calc(100% - 110px)}
	.moduleElTabSeach{height:initial}
	.moduleInptList{padding:3px 8px 3px 0}
	.job_city_sel{width:220px;margin-right:8px}
	.job_city_sel .el-input{width:228px}
	.moduleElYyJianl{height:calc(100% - 92px)!important}
	.moduleKaidYyJianl{height:calc(100% - 132px)!important}
	@media (max-width:1400px){.moduleKaidYyJianl{height:calc(100% - 168px)!important}
	}
	.crmChatGroupxz{width:22px;height:22px;display:inline-block;background:url(../../../images/is_group_h.png) no-repeat left center}
	.crmChatGroupwxz{width:22px;height:22px;display:inline-block;background:url(../../../images/is_group.png) no-repeat left center}
	.crmChatCustomerxz{width:22px;height:22px;display:inline-block;background:url(../../../images/is_customer_h.png) no-repeat left center}
	.crmChatCustomerwxz{width:22px;height:22px;display:inline-block;background:url(../../../images/is_customer.png) no-repeat left center}
</style>