<template>
    <div style="overflow: hidden; position: relative; height: 100%;">
        <div class="tableDome_tip">
            <el-alert title="根据自己的需求和喜好自定义属于自己的风格模板。微信小程序、百度小程序不允许有诱导分享行为，故不支持红包职位、赏金职位功能。" type="success"
                      :closable="false">
            </el-alert>
            <a v-if="showcachebtn" href="javascript:;" @click="makeCache()" class="admin_new_cz_tj"
               style="width:100px;">生成缓存</a>
        </div>
        <div class="moduleElHight" style="display: flex;" v-if="islook">
            <div class="mouldappLeft mouldappLeCont">
                <el-tabs :tab-position="tabPosition" class="tabSign">
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>自定义主题</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>自定义主题</span>
                            </div>
                            <div class="mouldappLiebs">
                                <div class="colorTite">
                                    <span>主颜色：</span>
                                    <el-color-picker v-model="maincolor"></el-color-picker>
                                </div>
                                <div class="colorButn">
                                    <el-button class="color1" @click="setcolor('#0078ff', 1)" circle></el-button>
                                    <el-button class="color2" @click="setcolor('#fa6400', 1)" circle></el-button>
                                    <el-button class="color3" @click="setcolor('#10ae51', 1)" circle></el-button>
                                    <el-button class="color4" @click="setcolor('#ff5857', 1)" circle></el-button>
                                    <el-button class="color5" @click="setcolor('#ac6cff', 1)" circle></el-button>
                                </div>
                            </div>
                            <div class="mouldappLiebs">
                                <div class="colorTite">
                                    <span>辅助色：</span>
                                    <el-color-picker v-model="secondcolor"></el-color-picker>
                                </div>
                                <div class="colorButn">
                                    <el-button class="color1" @click="setcolor('#0078ff', 2)" circle></el-button>
                                    <el-button class="color2" @click="setcolor('#fa6400', 2)" circle></el-button>
                                    <el-button class="color3" @click="setcolor('#10ae51', 2)" circle></el-button>
                                    <el-button class="color4" @click="setcolor('#ff5857', 2)" circle></el-button>
                                    <el-button class="color5" @click="setcolor('#ac6cff', 2)" circle></el-button>
                                </div>
                            </div>
                            <div class="mouldappLiebs">
                                <div class="mouldapTijiao">
                                    <el-button type="primary" @click="setsave('theme')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>搜索</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>搜索</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="searchshow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="searchstyle" label="2" border size="small">
                                        <span>默认样式</span>
                                        <span>主体色底，内容白色</span>
                                    </el-radio>
                                    <el-radio v-model="searchstyle" label="1" border size="small">
                                        <span>白色样式</span>
                                        <span>白色底样式</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>搜索框默认文字</span></div>
                                <div class="listConts" style="max-width: 500px;">
                                    <el-input v-model="searchstr" placeholder="请输入内容"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('search')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>幻灯广告区</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>幻灯广告区</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="membershow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="slideadstyle" label="1" border size="small">
                                        <span>默认尺寸</span>
                                        <span>700*240PX</span>
                                    </el-radio>
                                    <el-radio v-model="slideadstyle" label="2" border size="small">
                                        <span>小尺寸</span>
                                        <span>700*200PX</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>广告类别</span></div>
                                <div class="listConts" style="max-width: 500px;">
                                    <el-select v-model="hdid" placeholder="请选择">
                                        <el-option v-for="item in adClassList" :key="'ad_' + item.id"
                                                   :label="item.class_name" :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('member')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>导航</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>导航</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="navshow"></el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="navstyle" label="1" border size="small">
                                        <span>单行滑动</span>
                                        <span>一行5个，超出侧滑展示</span>
                                    </el-radio>
                                    <el-radio v-model="navstyle" label="2" border size="small">
                                        <span>多行滑动</span>
                                        <span>一行5个，超出向下排列</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="pznavsLis" v-for="(item, index) in nav" :key="'pznav_' + index">
                                <div class="pznavsLisOne">
                                    <div class="pznavsLiInfo">
                                        <div class="pznavsLisTite">
                                            <span>图标：</span>
                                            <el-upload class="upload-demo" :action="uploadAction" :accept="pic_accept" :auto-upload="false"
                                                       :show-file-list="false" :on-change="iconChange">
                                                <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                           @click="setnavindex(index)">添加图片
                                                </el-button>
                                            </el-upload>
                                        </div>
                                        <div class="pznavsLisTite">
                                            <span>标题：</span>
                                            <el-input v-model="item.name" placeholder="请输入内容"></el-input>
                                        </div>
                                    </div>
                                    <div class="pznavsLiImage">
                                        <el-image v-if="item.pic" style="width: 70px; height: 70px"
                                                  :src="item.pic"></el-image>
                                    </div>
                                </div>
                                <div class="pznavsLisTwo">
                                    <span class="navTwoTite">显示：</span>
                                    <el-switch v-model="item.display_n" @change="handleNavSwitch(index)"></el-switch>
                                </div>
                                <div class="pznavsLisTwo">
                                    <span class="navTwoTite">排序：</span>
                                    <el-input v-model="item.navsort" placeholder="请输入排序"></el-input>
                                </div>
                                <div class="pznavsLisTwo">
                                    <span class="navTwoTite">类型：</span>
                                    <el-radio v-model="item.navtype" label="0">内部页面</el-radio>
                                    <el-radio v-model="item.navtype" label="1">打开小程序(APP和微信小程序内)</el-radio>
                                </div>
                                <div class="pznavsLisTwo" v-if="item.navtype == '1'">
                                    <span class="navTwoTite">Appid：</span>
                                    <el-input v-model="item.nav_appid" placeholder="请输入Appid"></el-input>
                                </div>
                                <div class="pznavsLisTwo" v-if="item.navtype == '1'">
                                    <span class="navTwoTite">原始ID：</span>
                                    <el-input v-model="item.nav_ysid" placeholder="请输入原始ID"></el-input>
                                </div>
                                <div class="pznavsLisTwo" v-if="item.navtype == '1'">
                                    <span class="navTwoTite">路径：</span>
                                    <el-input v-model="item.url" placeholder="请输入路径"></el-input>
                                </div>
                                <div class="pznavsLisTwo" v-if="item.navtype == '0'">
                                    <span class="navTwoTite">链接：</span>
                                    <el-select v-model="item.url" placeholder="请选择">
                                        <el-option v-for="item in navigation" :key="'link_' + item.id" :label="item.name"
                                                   :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="pznavsLisTwo" v-if="item.navtype == '0' && item.url == 6">
                                    <span class="navTwoTite">新闻类别：</span>
                                    <el-cascader :options="newsClassList" v-model="item.news_class" clearable
                                                 :props="{ checkStrictly: true, emitPath: false, value: 'id', label: 'name' }">
                                    </el-cascader>
                                </div>
                                <div class="pznavsLisTwo">
                                    <div class="pznavsLisTips">
                                        <span>建议图标尺寸：不小于64*64像素</span>
                                    </div>
                                </div>
                                <div class="pznavsLisTite" v-if="item.id > 150 || item.id == ''">
                                    <span></span>
                                    <el-button size="small" type="primary" plain icon="el-icon-delete-solid"
                                               @click="delnav(index)">删除
                                    </el-button>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('nav')" :disabled="saveLoading">保存</el-button>
                                    <el-button type="primary" @click="addnav">添加导航</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>职位专题</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>职位专题</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="jobspecshow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>数量</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="jobspecnum" placeholder="请输入数量"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('jobspec')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>公告</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>公告</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="notice">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="noticestyle" label="1" border size="small">
                                        <span>样式一</span>
                                    </el-radio>
                                    <div v-if="noticestyle == '1'">
                                        <div class="listTite"><span>标题图片</span></div>
                                        <div class="pznavsLiInfo">
                                            <div class="pznavsLisTite">
                                                <div class="pznavsLiImage" style="margin-right: 10px;">
                                                    <el-image v-if="noticestylepic1" style="width: 82px; height: 82px"
                                                              :src="noticestylepic1"></el-image>
                                                </div>
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                           :show-file-list="false" :on-change="ggPicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus">
                                                        添加图片
                                                    </el-button>
                                                </el-upload>
                                            </div>
                                        </div>

                                    </div>
                                    <el-radio v-model="noticestyle" label="2" border size="small">
                                        <span>样式二</span>
                                    </el-radio>
                                    <div v-if="noticestyle == '2'">
                                        <div class="listTite"><span>标题图片</span></div>
                                        <div class="pznavsLiInfo">
                                            <div class="pznavsLisTite">
                                                <div class="pznavsLiImage" style="margin-right: 10px;">
                                                    <el-image v-if="noticestylepic2" style="width: 82px; height: 82px"
                                                              :src="noticestylepic2"></el-image>
                                                </div>
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                           :show-file-list="false" :on-change="ggPicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus">
                                                        添加图片
                                                    </el-button>
                                                </el-upload>
                                            </div>
                                        </div>
                                    </div>
                                    <el-radio v-model="noticestyle" label="3" border size="small">
                                        <span>样式三</span>
                                    </el-radio>
                                    <div v-if="noticestyle == '3'">
                                        <div class="listTite"><span>标题图片</span></div>
                                        <div class="pznavsLiInfo">
                                            <div class="pznavsLisTite">
                                                <div class="pznavsLiImage" style="margin-right: 10px;">
                                                    <el-image v-if="noticestylepic3" style="width: 82px; height: 82px"
                                                              :src="noticestylepic3"></el-image>
                                                </div>
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                           :show-file-list="false" :on-change="ggPicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus">
                                                        添加图片
                                                    </el-button>
                                                </el-upload>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('notice')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane v-for="(item, index) in newsort" :key="index">
                        <div slot="label" class="divSlot">
                            <span>{{ item.label }}</span>
                            <i class="el-icon-rank"></i>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'ad'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="adshow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>广告类别一</span></div>
                                <div class="listConts" style="max-width: 500px;">
                                    <el-select v-model="adhdid" placeholder="请选择" clearable>
                                        <el-option v-for="item in adClassList" :key="item.id" :label="item.class_name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>广告类别二</span></div>
                                <div class="listConts" style="max-width: 500px;">
                                    <el-select v-model="adhdid2" placeholder="请选择" clearable>
                                        <el-option v-for="item in adClassList" :key="item.id" :label="item.class_name"
                                                   :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>广告类别三</span></div>
                                <div class="listConts" style="max-width: 500px;">
                                    <el-select v-model="adhdid3" placeholder="请选择" clearable>
                                        <el-option v-for="item in adClassList" :key="item.id" :label="item.class_name"
                                                   :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('ad')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'login'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="login">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>按扭一标题</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="login_title1" placeholder="请输入按扭一标题"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>按扭一副标题</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="login_subtitle1" placeholder="请输入按扭一副标题"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite">
                                    <span>按钮一图标</span>
                                </div>
                                <div class="drawerModInpt">
                                    <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false" :show-file-list="false"
                                               :on-change="btnIcon1Change">
                                        <el-button slot="trigger" size="small" type="primary">上传图片</el-button>
                                        <img class="el-upload-list__item-thumbnail" width="36" height="36"
                                             v-if="login_icon1" :src="login_icon1" />
                                    </el-upload>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>按扭二标题</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="login_title2" placeholder="请输入按扭二标题"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>按扭二副标题</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="login_subtitle2" placeholder="请输入按扭二副标题"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite">
                                    <span>按钮二图标</span>
                                </div>
                                <div class="drawerModInpt">
                                    <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false" :show-file-list="false"
                                               :on-change="btnIcon2Change">
                                        <el-button slot="trigger" size="small" type="primary">上传图片</el-button>
                                        <img class="el-upload-list__item-thumbnail" width="36" height="36"
                                             v-if="login_icon2" :src="login_icon2" />
                                    </el-upload>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('login')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'hotcom'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="hotcom">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="hotcomstyle" label="1" border size="small">
                                        <span>样式一</span>
                                    </el-radio>
                                    <el-radio v-model="hotcomstyle" label="2" border size="small">
                                        <span>样式二</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>更多</span></div>
                                <div class="listConts">
                                    <el-switch v-model="hotcommore">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>数量</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="hotcomnum" placeholder="请输入数量"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('hotcom')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'hotjob'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="hotjob">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>更多</span></div>
                                <div class="listConts">
                                    <el-switch v-model="hotjobmore">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>数量</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="hotjobnum" placeholder="请输入数量"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('hotjob')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'zph'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="zphshow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>更多</span></div>
                                <div class="listConts">
                                    <el-switch v-model="zphmore">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>数量</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="zphnum" placeholder="请输入数量"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('zph')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'article'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="articleshow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="articletype" label="1" border size="small">
                                        <span>样式一</span>
                                    </el-radio>
                                    <el-radio v-model="articletype" label="2" border size="small">
                                        <span>样式二</span>
                                    </el-radio>
                                    <el-radio v-model="articletype" label="3" border size="small">
                                        <span>样式三</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>更多</span></div>
                                <div class="listConts">
                                    <el-switch v-model="articlemore">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>数量</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="articlenum" placeholder="请输入数量"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('article')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                        <div class="mouldappLeCont" v-if="item.value == 'job'">
                            <div class="mouldappLeTite">
                                <span>{{ item.label }}</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="job">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="jobstyle" label="1" border size="small">
                                        <span>样式一</span>
                                    </el-radio>
                                    <el-radio v-model="jobstyle" label="2" border size="small">
                                        <span>样式二</span>
                                    </el-radio>
                                    <el-radio v-model="jobstyle" label="3" border size="small">
                                        <span>样式三</span>
                                    </el-radio>
                                    <el-radio v-model="jobstyle" label="4" border size="small">
                                        <span>样式四</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite">
                                    <div style="display: flex;justify-content: space-between;">
                                        <span>设置推荐栏目</span>
                                        <span>拖动可调整位置</span>
                                    </div>
                                    <div class="mouldappLirbia">
                                        <div v-for="(item, jobsortinedx) in jobsort" :key="jobsortinedx" @dragstart="dragstart(jobsortinedx)"
                                             @dragenter="dragenter($event, jobsortinedx)"
                                             @dragover="dragover($event, jobsortinedx)" draggable="true">
                                            <div class="mouldappDuoge" v-if="item == 'newjob'">
                                                <div class="mouldappDuoge">
                                                    <div class="mouldappXuzn">
                                                        <el-checkbox v-model="newjob" @change="calrealjobsort">最新</el-checkbox>
                                                    </div>
                                                    <div class="mouldappShur">
                                                        <div class="titName">职位展示条数</div>
                                                        <el-input v-model="newjobnum" placeholder="请输入内容"></el-input>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mouldappDuoge" v-if="item == 'urgjob'">
                                                <div class="mouldappDuoge">
                                                    <div class="mouldappXuzn">
                                                        <el-checkbox v-model="urgjob" @change="calrealjobsort">急聘</el-checkbox>
                                                    </div>
                                                    <div class="mouldappShur">
                                                        <div class="titName">职位展示条数</div>
                                                        <el-input v-model="urgjobnum" placeholder="请输入内容"></el-input>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mouldappDuoge" v-if="item == 'recjob'">
                                                <div class="mouldappDuoge">
                                                    <div class="mouldappXuzn">
                                                        <el-checkbox v-model="recjob" @change="calrealjobsort">推荐</el-checkbox>
                                                    </div>
                                                    <div class="mouldappShur">
                                                        <div class="titName">职位展示条数</div>
                                                        <el-input v-model="recjobnum" placeholder="请输入内容"></el-input>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mouldappDuoge" v-if="item == 'mapjob'">
                                                <div class="mouldappDuoge">
                                                    <div class="mouldappXuzn">
                                                        <el-checkbox v-model="mapjob" @change="calrealjobsort">附近</el-checkbox>
                                                    </div>
                                                    <div class="mouldappShur">
                                                        <div class="titName">职位展示条数</div>
                                                        <el-input v-model="mapjobnum" placeholder="请输入内容"></el-input>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('job')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>站长资质</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>站长资质</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="syszzmore">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('syszz')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>一键登陆</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>一键登陆</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="sysyjlogin">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('sysyjlogin')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>浮动客服</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>浮动客服</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件展示</span></div>
                                <div class="listConts">
                                    <el-switch v-model="cshow">
                                    </el-switch>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>客服链接</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="kfurl" placeholder="请输入客服链接"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>企业ID</span></div>
                                <div class="listConts" style="max-width: 300px;">
                                    <el-input v-model="kfcomid" placeholder="请输入企业ID"></el-input>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite">
                                    <span>客服图标</span>
                                </div>
                                <div class="drawerModInpt">
                                    <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false" :show-file-list="false"
                                               :on-change="kfIconChange">
                                        <el-button slot="trigger" size="small" type="primary">上传图片</el-button>
                                        <img class="el-upload-list__item-thumbnail" width="36" height="36" v-if="kficon"
                                             :src="kficon" />
                                    </el-upload>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('connect')">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane>
                        <div slot="label" class="divSlot"><span>底部导航</span></div>
                        <div class="mouldappLeCont">
                            <div class="mouldappLeTite">
                                <span>底部导航</span>
                            </div>
                            <div class="mouldappLists">
                                <div class="listTite"><span>组件样式</span></div>
                                <div class="listConts">
                                    <el-radio v-model="footstyle" label="1" border size="small">
                                        <span>样式一</span>
                                    </el-radio>
                                    <el-radio v-model="footstyle" label="2" border size="small">
                                        <span>样式二</span>
                                    </el-radio>
                                </div>
                            </div>
                            <div class="pznavsLis" v-for="(item, index) in footnav" :key="index">
                                <div class="pznavsLisOne">
                                    <div class="pznavsLiInfo">
                                        <div class="pznavsLisTite">
                                            <span>未选中：</span>
                                            <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                       :show-file-list="false" :on-change="footIconChange">
                                                <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                           @click="setfooticonindex(index)">添加图片
                                                </el-button>
                                            </el-upload>
                                        </div>
                                    </div>
                                    <div class="pznavsLiImage">
                                        <el-image v-if="item.pic" style="width: 70px; height: 70px"
                                                  :src="item.pic"></el-image>
                                    </div>
                                </div>
                                <div class="pznavsLisOne">
                                    <div class="pznavsLiInfo">
                                        <div class="pznavsLisTite">
                                            <span>选中：</span>
                                            <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                       :show-file-list="false" :on-change="footCurrIconChange">
                                                <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                           @click="setfooticonindex(index)">添加图片
                                                </el-button>
                                            </el-upload>
                                        </div>
                                    </div>
                                    <div class="pznavsLiImage">
                                        <el-image v-if="item.curpic" style="width: 70px; height: 70px"
                                                  :src="item.curpic"></el-image>
                                    </div>
                                </div>
                                <div class="pznavsLisTwo">
                                    <span class="navTwoTite">标题：</span>
                                    <el-input v-model="item.name" placeholder="请输入标题"></el-input>
                                </div>
                                <div class="pznavsLisTwo">
                                    <div class="pznavsLisTips">
                                        <span>建议图标尺寸：不小于64*64像素</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mouldappLists">
                                <div class="listCoButn">
                                    <el-button type="primary" @click="setsave('foot')" :disabled="saveLoading">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </div>
            <div class="mouldappRigh">
                <div class="mouldappIphone">
                    <div class="mouldappIphCont">
                        <div class="mouldappGundong">
                            <div class="mouldappGundBottom">
                                <div class="mouldappIphTop"
                                     :style="searchstyle == '2' ? { background: `${maincolor}` } : { background: `#fff` }">
                                    <div class="weizhi" :style="{color: searchstyle == '2' ? '#fff' : '#1890ff'}">
                                        <span>北京</span>
                                        <i class="el-icon-arrow-down"></i>
                                    </div>
                                    <div class="searchs" v-show="searchshow">
                                        <el-input v-model="searchstr" readonly size="mini" placeholder="请输入内容"
                                                  prefix-icon="el-icon-search"></el-input>
                                    </div>
                                </div>
                                <div class="mouldapBanne" v-if="membershow">
                                    <el-carousel :style="slideadstyle == '2' ? { height: `69px` } : { height: `85px` }"
                                                 style="overflow: hidden;">
                                        <el-carousel-item v-for="(item, index) in hd" :key="'hd_' + index">
                                            <el-image style=""
                                                      :style="slideadstyle == '2' ? { height: `69px`, width: `100%` } : { height: `85px`, width: `100%` }"
                                                      :src="item.pic"></el-image>
                                        </el-carousel-item>
                                    </el-carousel>
                                </div>
                                <div class="mouldapnavs" v-show="navshow">
                                    <div class="wap_mb_show_nav navstyle1" v-if="navstyle == '1'">
                                        <div id="navcar" style="background:#fff">
                                            <el-carousel>
                                                <el-carousel-item v-for="(narr, nindex) in navarr" :key="'nav_' + nindex">
                                                    <div class="mouldapNavs">
                                                        <div v-for="(pv,pkey) in narr" :key="pkey" :class="'linavshow' + pv.id" class="mouldapNavList">
                                                            <div :class="pv.display_n ? '' : 'nav_none'">
                                                                <img :src="pv.pic ? pv.pic : '../../images/wap_show_img1.png'" width="30" :class="'navpicshow' + pv.id">
                                                                <span>{{ pv.name }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </el-carousel-item>
                                            </el-carousel>
                                        </div>
                                    </div>
                                    <div class="wap_mb_show_nav navstyle2" v-if="navstyle == '2'">
                                        <ul class="wap_mb_show_nav_list">
                                            <div style="display: flex;flex-wrap: wrap;" class="wapmbSshowDuoh">
                                                <li v-for="(navval,nkey) in nav" :key="nkey" class="linavshow{yun:}$navval.id{/yun}" v-show="navval.display_n">
                                                    <img :src="navval.pic ? navval.pic : '../../images/wap_show_img1.png'" width="30" class="navpicshow{yun:}$navval.id{/yun}">
                                                    <div class="wap_mb_show_nav_list_p">{{ navval.name }}</div>
                                                </li>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mouldapjobspecial" v-if="jobspecshow">
                                    <img src="../../images/appdiy_jobspec1.png">
                                    <img src="../../images/appdiy_jobspec2.png">
                                    <img src="../../images/appdiy_jobspec3.png">
                                </div>
                                <div class="mouldapnotice" v-if="notice">
                                    <!--样式一-->
                                    <div :class="noticeclass">
                                        <div class="gg_left">
                                            <img v-if="noticestyle == '1'" :src="noticestylepic1" alt="" width="20">
                                            <img v-else-if="noticestyle == '2'" :src="noticestylepic2" alt="" width="59">
                                            <img v-else-if="noticestyle == '3'" :src="noticestylepic3" alt="" width="51"
                                                 height="16">
                                        </div>
                                        <div class="gg_right" v-if="noticestyle == '1'">
                                            <span>甘肃省实施《中华人民共和国森林法》办法</span>
                                        </div>
                                        <div class="gg_right" v-else-if="noticestyle == '2'">
                                            <span>甘肃省实施《中华人民共和国森林法》办法</span>
                                            <span>人才网春节放假通知</span>
                                            <span>职业梦想：人人都能自我实现</span>
                                        </div>
                                        <div class="gg_right" v-else-if="noticestyle == '3'">
                                            <span>甘肃省实施《中华人民共和国森林法》办法</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-for="(item,newkey) in newsort" :key="newkey">
                                <div class="app_adgg" v-if="adshow && item.value == 'ad' && adhd">
                                    <img :src="adhd">
                                </div>
                                <div class="app_adgg2" v-if="adshow && item.value == 'ad' && adhd2">
                                    <img :src="adhd2">
                                    <img :src="adhd2">
                                </div>
                                <div class="app_adgg3" v-if="adshow && item.value == 'ad' && adhd3">
                                    <img :src="adhd3">
                                    <img :src="adhd3">
                                    <img :src="adhd3">
                                </div>
                                <div class="appset_log" v-if="login && item.value == 'login'">
                                    <div class="appset_log_item">
                                        <div class="log_item_left">
                                            <img :src="login_icon1" alt="" width="40" height="40"
                                                style="border-radius: 50%;">
                                        </div>
                                        <div class="log_item_right">
                                            <div class="log_item_right_tit">{{ login_title1 }}</div>
                                            <div class="log_item_right_subtit">{{ login_subtitle1 }}</div>
                                        </div>
                                    </div>
                                    <div class="appset_log_item">
                                        <div class="log_item_left">
                                            <img :src="login_icon2" alt="" width="40" height="40"
                                                style="border-radius: 50%;">
                                        </div>
                                        <div class="log_item_right">
                                            <div class="log_item_right_tit">{{ login_title2 }}</div>
                                            <div class="log_item_right_subtit">{{ login_subtitle2 }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div style="float:left;width:100%;" v-if="hotcom && item.value == 'hotcom'">
                                    <!--样式一-->
                                    <div class="hotcomstyle" v-if="hotcomstyle == '1'" style="padding: 12px 0;">
                                        <div class="mq_style_itemone">
                                            <div class="mq_style_itemone_top">
                                                <div class="mq_style_itemone_name">
                                                    名企招聘
                                                </div>
                                                <div class="hotcommore" v-if="hotcommore">
                                                    更多 >
                                                </div>
                                            </div>
                                            <div class="mq_style_itemone_bottom">
                                                <div class="mq_style_itemone_bottom_item">
                                                    <div class="mq_itemone_item_left">
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemone_item_right">
                                                        <div class="mq_itemone_item_name">
                                                            双沟酿酒
                                                        </div>
                                                        <div>
                                                            2个岗位
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mq_style_itemone_bottom_item">
                                                    <div class="mq_itemone_item_left">
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemone_item_right">
                                                        <div class="mq_itemone_item_name">
                                                            雨虹防水
                                                        </div>
                                                        <div>
                                                            5个岗位
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mq_style_itemone_bottom_item">
                                                    <div class="mq_itemone_item_left">
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemone_item_right">
                                                        <div class="mq_itemone_item_name">
                                                            箐优网
                                                        </div>
                                                        <div>
                                                            4个岗位
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mq_style_itemone_bottom_item">
                                                    <div class="mq_itemone_item_left">
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemone_item_right">
                                                        <div class="mq_itemone_item_name">
                                                            酷尔传媒
                                                        </div>
                                                        <div>
                                                            8个岗位
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--样式二-->
                                    <div class="hotcomstyle" v-else-if="hotcomstyle == '2'">
                                        <div class="mq_style_itemtwo">
                                            <div class="mq_style_itemtwo_top">
                                                <div class="mq_style_itemtwo_name">
                                                    名企招聘
                                                </div>
                                                <div class="hotcommore" v-if="hotcommore">
                                                    更多 >
                                                </div>
                                            </div>
                                            <div class="mq_style_itemtwo_bottom">
                                                <div class="mq_style_itemtwo_item">
                                                    <div>
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemtwo_item_name">
                                                        雨虹防水
                                                    </div>
                                                    <div>
                                                        8个在招职位
                                                    </div>
                                                </div>
                                                <div class="mq_style_itemtwo_item">
                                                    <div>
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemtwo_item_name">
                                                        雨虹防水
                                                    </div>
                                                    <div>
                                                        8个在招职位
                                                    </div>
                                                </div>
                                                <div class="mq_style_itemtwo_item">
                                                    <div>
                                                        <img src="../../images/wap_diy_hotcom.png"
                                                            alt="" width="40" height="40">
                                                    </div>
                                                    <div class="mq_itemtwo_item_name">
                                                        雨虹防水
                                                    </div>
                                                    <div>
                                                        8个在招职位
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="appset_hotzw" v-if="hotjob && item.value == 'hotjob'">
                                    <div class="clear"></div>
                                    <div class="zph_style_top">
                                        <div>
                                            热门职位
                                        </div>
                                        <div class="hotjobmore" v-if="hotjobmore">
                                            更多>
                                        </div>
                                    </div>
                                    <div class="hotjob_bottom">
                                        <div class="hotjob_item">
                                            <span>服务业</span>
                                        </div>
                                        <div class="hotjob_item">
                                            <span>人事/行政</span>
                                        </div>
                                        <div class="hotjob_item">
                                            <span>送餐员</span>
                                        </div>
                                        <div class="hotjob_item">
                                            <span>销售顾问</span>
                                        </div>
                                        <div class="hotjob_item">
                                            <span>主播</span>
                                        </div>
                                        <div class="hotjob_item">
                                            <span>灯光师</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="appset_zph" v-if="zphshow && item.value == 'zph'">
                                    <div class="zph_style_top">
                                        <div>
                                            招聘会
                                        </div>
                                        <div class="zphmore" v-if="zphmore == 1">
                                            更多>
                                        </div>
                                    </div>
                                    <div class="zph_style_bottom">
                                        <div class="zph_item">
                                            <div class="zph_item_left">
                                                <img src="../../images/wap_show_img3.png"
                                                    width="100%" height="100%">
                                            </div>
                                            <div class="zph_item_right">
                                                <div>
                                                    <span>湖北2023年春节期间"春暖农民工"服务行动，来了！</span>
                                                </div>
                                                <div>
                                                    2023-1-3
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="articleshow && item.value == 'article'">
                                    <!--职场资讯 样式一-->
                                    <div class="appset_zx" v-if="articletype == '1'">
                                        <div class="zph_style_top">
                                            <div>
                                                职场资讯
                                            </div>
                                            <div class="articlemore" v-if="articlemore">
                                                更多>
                                            </div>
                                        </div>
                                        <div class="zph_style_bottom">
                                            <div class="zph_item">
                                                <div class="zph_item_left">
                                                    <img src="../../images/wap_diy_article.png"
                                                        alt="" width="100%" height="100%">
                                                </div>
                                                <div class="zph_item_right">
                                                    <div>
                                                        <span>湖北2023年春节期间"春暖农民工"服务行动，来了！</span>
                                                    </div>
                                                    <div>
                                                        2023-1-3
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--职场资讯 样式二-->
                                    <div class="appset_zx" v-else-if="articletype == '2'">
                                        <div class="zph_style_top">
                                            <div>
                                                职场资讯
                                            </div>
                                            <div class="articlemore" v-if="articlemore">
                                                更多>
                                            </div>
                                        </div>
                                        <div class="zph_style_bottom zph_style_bottomtwo">
                                            <div class="zph_item zph_itemtwo">
                                                <div class="zph_item_left zph_item_lefttwo">
                                                    <img src="../../images/wap_diy_article.png"
                                                        alt="" width="100%" height="100%">
                                                </div>
                                                <div class="zph_item_right">
                                                    <div>
                                                        <span>2023年春节期间"春暖农民工"服务行动，来了！</span>
                                                    </div>
                                                    <div>
                                                        2023-1-3
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="zph_item zph_itemtwo">
                                                <div class="zph_item_left zph_item_lefttwo">
                                                    <img src="../../images/wap_diy_article.png"
                                                        alt="" width="100%" height="100%">
                                                </div>
                                                <div class="zph_item_right">
                                                    <div>
                                                        <span>2023年春节期间"春暖农民工"服务行动，来了！</span>
                                                    </div>
                                                    <div>
                                                        2023-1-3
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--职场资讯 样式三-->
                                    <div class="appset_zx" v-else-if="articletype == '3'">
                                        <div class="zph_style_top">
                                            <div>
                                                职场资讯
                                            </div>
                                            <div class="articlemore" v-if="articlemore">
                                                更多>
                                            </div>
                                        </div>
                                        <div class="zph_style_bottom">
                                            <div class="zph_item zph_itemthree">
                                                <span>湖北2023年春节期间"春暖农民工"服务行动，来了！</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="job && item.value == 'job'" class="layui-tab-content" style="padding: 5px;">
                                    <div class="jobstyle jobstyle1" v-if="jobstyle == '1'">
                                        <div class="zw_style">
                                            <div class="style_top">
                                                <div v-for="(item, idx) in realjobsort" :key="idx" class="style_top_item">
                                                    <div v-if="item == 'newjob' && newjob" class="style_top_kudn">
                                                        最新
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                    <div v-if="item == 'urgjob' && urgjob" class="style_top_kudn">
                                                        急聘
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                    <div v-if="item == 'recjob' && recjob" class="style_top_kudn">
                                                        推荐
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                    <div v-if="item == 'mapjob' && mapjob" class="style_top_kudn">
                                                        附近
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="style_bottom">
                                                <div class="style_bottom_item">
                                                    <div class="style_bottom_item_top">
                                                        <div class="style_bottom_item_top_info">
                                                            <div class="info_name">
                                                                行政主管
                                                            </div>
                                                            <div class="info_price secondcolor_font"
                                                                :style="secondcolor ? { color: `${secondcolor}` } : ''">
                                                                4500-6000元
                                                            </div>
                                                        </div>
                                                        <div class="style_bottom_item_top_sec">
                                                            沭城 | 大专学历 | 2年经验
                                                        </div>
                                                        <div class="style_bottom_item_top_tagbox">
                                                            <div class="style_bottom_item_top_tag">
                                                                五险一金
                                                            </div>
                                                            <div class="style_bottom_item_top_tag">
                                                                公司旅行
                                                            </div>
                                                            <div class="style_bottom_item_top_tag">
                                                                加班补助
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="style_bottom_item_bottom">
                                                        <div>
                                                            <img src="../../images/wap_diy_hotcom.png"
                                                                alt="" width="34" height="34" style="border-radius: 6px;">
                                                            星诺教育
                                                        </div>
                                                        <div>
                                                            刚刚
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="jobstyle jobstyle2" v-if="jobstyle == '2'">
                                        <div class="zw_style">
                                            <div class="style_top style_toptwo">
                                                <div v-for="(item, idx) in realjobsort" :key="idx" class="style_top_item">
                                                    <div v-if="item == 'newjob' && newjob" class="style_top_kudn">
                                                        最新
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                    <div v-if="item == 'urgjob' && urgjob" class="style_top_kudn">
                                                        急聘
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                    <div v-if="item == 'recjob' && recjob" class="style_top_kudn">
                                                        推荐
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                    <div v-if="item == 'mapjob' && mapjob" class="style_top_kudn">
                                                        附近
                                                        <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                            :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="style_bottom">
                                                <div class="style_bottom_item">
                                                    <div class="style_bottom_item_top">
                                                        <div class="style_bottom_item_top_info">
                                                            <div class="info_name">
                                                                采购主管
                                                            </div>
                                                            <div class="info_price secondcolor_font"
                                                                :style="secondcolor ? { color: `${secondcolor}` } : ''">
                                                                4500-6000元
                                                            </div>
                                                        </div>
                                                        <div class="style_bottom_item_top_sec">
                                                            <div style="display: flex;align-items: center;">

                                                                    <div style="margin-left: 5px;">
                                                                        1年经验 . 不限学历
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    经开区
                                                                </div>
                                                            </div>
                                                            <div class="style_bottom_item_top_tagbox"
                                                                 style="display: flex;flex-wrap: nowrap; justify-content: space-between;">
                                                                <div style="display: flex;flex-wrap: wrap;">
                                                                    <div class="style_bottom_item_top_tag zw_styletwo_tag">
                                                                        五险一金
                                                                    </div>
                                                                    <div class="style_bottom_item_top_tag zw_styletwo_tag">
                                                                        公司旅行
                                                                    </div>
                                                                    <div class="style_bottom_item_top_tag zw_styletwo_tag">
                                                                        加班补助
                                                                    </div>
                                                                </div>
                                                                <div class="zw_styletwo_btn">
                                                                    申请
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="style_bottom_item_bottom">
                                                            <div style="display: flex; align-items: center;">
                                                                <img src="../../images/wap_diy_hotcom.png"
                                                                     alt="" width="34" height="34" style="border-radius: 50%;">
                                                                <div style="margin-left: 6px;">
                                                                    <div
                                                                            style="color: #666;font-weight: bolder; font-size: 15px;">
                                                                        途招聘
                                                                    </div>
                                                                    <div>
                                                                        精华电子(苏州)有限公司
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="jobstyle jobstyle3" v-if="jobstyle == '3'">
                                            <div class="zw_style">
                                                <div class="style_top">
                                                    <div v-for="(item, idx) in realjobsort" :key="idx" class="style_top_item">
                                                        <div v-if="item == 'newjob' && newjob" class="style_top_kudn">
                                                            最新
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                        <div v-if="item == 'urgjob' && urgjob" class="style_top_kudn">
                                                            急聘
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                        <div v-if="item == 'recjob' && recjob" class="style_top_kudn">
                                                            推荐
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                        <div v-if="item == 'mapjob' && mapjob" class="style_top_kudn">
                                                            附近
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="style_bottom">
                                                    <div class="style_bottom_item">
                                                        <div class="style_bottom_item_top">
                                                            <div class="style_bottom_item_top_info">
                                                                <div class="info_name">
                                                                    采购主管
                                                                </div>
                                                                <div class="info_price secondcolor_font"
                                                                     :style="secondcolor ? { color: `${secondcolor}` } : ''">
                                                                    3-5k
                                                                </div>
                                                            </div>
                                                            <div class="style_bottom_item_top_sec">
                                                                沭城 <span style="color: #eee;">|</span> 大专学历 <span
                                                                    style="color: #eee;">|</span>
                                                                2年经验
                                                            </div>
                                                            <div class="style_bottom_item_top_tagbox">
                                                                <div class="style_bottom_item_top_tag zw_stylethree_tag">
                                                                    五险一金
                                                                </div>
                                                                <div class="style_bottom_item_top_tag zw_stylethree_tag">
                                                                    公司旅行
                                                                </div>
                                                                <div class="style_bottom_item_top_tag zw_stylethree_tag">
                                                                    加班补助
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="style_bottom_item_bottom"
                                                             style="border-top: 1px solid #efefef;">
                                                            <div style="display: flex; align-items: center;">
                                                                <img src="../../images/wap_diy_hotcom.png"
                                                                     alt="" width="34" height="34" style="border-radius: 50%;">
                                                                <div style="margin-left: 6px;">
                                                                    <div
                                                                            style="color: #666;font-weight: bolder; font-size: 15px;">
                                                                        途招聘
                                                                    </div>
                                                                    <div>
                                                                        精华电子(苏州)有限公司
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="jobstyle jobstyle4" v-if="jobstyle == '4'">
                                            <div class="zw_style">
                                                <div class="style_top style_topfour">
                                                    <div v-for="(item, idx) in realjobsort" :key="idx" class="style_top_item">
                                                        <div v-if="item == 'newjob' && newjob" class="style_top_kudn">
                                                            最新
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                        <div v-if="item == 'urgjob' && urgjob" class="style_top_kudn">
                                                            急聘
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                        <div v-if="item == 'recjob' && recjob" class="style_top_kudn">
                                                            推荐
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                        <div v-if="item == 'mapjob' && mapjob" class="style_top_kudn">
                                                            附近
                                                            <i v-if="idx == 0" class="zw_xh_line maincolor_cur"
                                                               :style="maincolor ? { background: `${maincolor}` } : ''"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="style_bottom">
                                                    <div class="style_bottom_item">
                                                        <div class="style_bottom_item_top">
                                                            <div class="style_bottom_item_top_info">
                                                                <div class="info_name">
                                                                    行政主管
                                                                </div>
                                                                <div class="info_price secondcolor_font"
                                                                     :style="secondcolor ? { color: `${secondcolor}` } : ''">
                                                                    4500-6000元
                                                                </div>
                                                            </div>
                                                            <div class="style_bottom_item_top_tagbox">
                                                                <div style="display: flex;flex-wrap: wrap;">
                                                                    <div class="style_bottom_item_top_tag zw_stylethree_tag">
                                                                        经开区
                                                                    </div>
                                                                    <div class="style_bottom_item_top_tag zw_stylethree_tag">
                                                                        不限经验
                                                                    </div>
                                                                    <div class="style_bottom_item_top_tag zw_stylethree_tag">
                                                                        不限学历
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    刚刚
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="style_bottom_item_bottom">
                                                            <div style="display: flex; align-items: center;">
                                                                <img src="../../images/wap_diy_hotcom.png"
                                                                     alt="" width="34" height="34" style="border-radius: 50%;">
                                                                <div style="margin-left: 6px;">
                                                                    <div
                                                                            style="color: #666;font-weight: bolder; font-size: 15px;">
                                                                        途招聘
                                                                    </div>
                                                                    <div>
                                                                        精华电子(苏州)有限公司
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="syszzmore" class="yun_newedition_jobmore syszzmore"
                                     style="font-size: 12px; color: #999; text-align: center;line-height: 18px;">
                                    <p>版权所有：鑫潮人力资源服务 </p>
                                    <p>ICP备案号：苏ICP备12049413号-3 </p>
                                    <p>网安备案号：苏公网安备11020210125号 </p>
                                    <p>人力资源证编号：321333 </p>
                                </div>

                                <!--底部导航开始-->
                            </div>
                        </div>
                        <div v-if="cshow" class="yun_newedition_footer">
                            <div class="">
                                <img :src="kficon" style="width:50px;height:50px;">
                            </div>
                        </div>

                        <div class="footstyle">
                            <div class="appfooter"
                                 :style="footstyle == '2' ? 'background: #fff; border-radius: 26px 26px 0 0;' : ''">
                                <ul class="yun_footer_nav">
                                    <template v-for="(pv,footkey) in footnav">
                                        <li  :key="footkey" v-if="pv.url == 'index' || pv.url == 'job' || pv.url == 'midButton' || pv.url == 'resume' || pv.url == 'login'">
                                            <a>
                                                <img v-if="pv.pic" :src="pv.pic" width="30" :class="'footpicshow' + pv.id"
                                                     :style="pv.url == 'midButton' ? 'width: 40px;height: 40px;left: 14%;top: -30%;' : ''">
                                                <img v-else src="../../images/wap_show_img1.png"
                                                     width="30" :class="'footpicshow' + pv.id">
                                                {{ pv.name }}
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- 横幅登录 -->
                        <div class="henfuLogin" v-if="sysyjlogin">
                            <div class="henfuLogTite">
                                登录后，推荐精准好职位
                            </div>
                            <div class="henfuLogLink">
                                一键登录
                            </div>
                        </div>
                        <!--底部导航结束-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    module.exports = {
        data: function () {
            return {
                islook: false,
                tabPosition: 'left',
                maincolor: '#0078ff',
                secondcolor: '#ff5857',
                searchshow: false,
                searchstyle: '2',
                searchstr: '请输入职位关键字，如：会计...',
                membershow: false,
                slideadstyle: '1',
                hdid: '',
                adClassList: [],
                newsClassList: [],
                navshow: false,
                navstyle: '1',
                nav: [],
                navigation: [],
                jobspecshow: false,
                jobspecnum: '3',
                newsort: [],
                notice: false,
                noticestyle: '1',
                noticestylepic1: '',
                noticestylepic2: '',
                noticestylepic3: '',
                hd: [],
                curr_navindex: 0,
                iconslist: [],
                ggpic: [],
                newggpickeys: [],
                adshow: false,
                adhdid: '',
                adhd: '',
            adhdid2: '',
            adhd2: '',
            adhdid3: '',
            adhd3: '',
                login: false,
                login_title1: '',
                login_subtitle1: '',
                login_icon1: '',
                login_icon1_file: [],
                login_title2: '',
                login_subtitle2: '',
                login_icon2: '',
                login_icon2_file: [],
                hotcom: false,
                hotcomstyle: '1',
                hotcommore: false,
                hotcomnum: '',
                hotjob: false,
                hotjobmore: false,
                hotjobnum: '',
                zphshow: false,
                zphmore: false,
                zphnum: '',
                articleshow: false,
                articlemore: false,
                articletype: '',
                articlenum: '4',
                job: false,
                jobstyle: '',
                jobsort: [],
                realjobsort: [],
                newjob: false,
                newjobnum: '5',
                urgjob: false,
                urgjobnum: '5',
                recjob: false,
                recjobnum: '5',
                mapjob: false,
                mapjobnum: '5',
                syszzmore: false,
            sysyjlogin: false,
                cshow: false,
                kfurl: '',
                kfcomid: '',
                kficon: '',
                kficon_file: [],
                curr_footiconindex: 0,
                footiconslist: [],
                footcurriconslist: [],
                footnav: [],
                footstyle: '',

                // 源对象的下标
                dragIndex: '',
                // 目标对象的下标
                enterIndex: '',
                timeout: null,
                navarr: [],
                showcachebtn: false,
                pic_accept: localStorage.getItem("pic_accept"),
                saveLoading: false,
                uploadAction: baseUrl + 'm=common&c=common_upload'
            }
        },
        computed: {
            noticeclass() {
                if (this.noticestyle == '1') {
                    return 'appdiy_gg appdiy_ggone'
                } else if (this.noticestyle == '2') {
                    return 'appdiy_gg'
                } else if (this.noticestyle == '3') {
                    return 'appdiy_gg appdiy_ggthree'
                }
            },
        },
        mounted() {
            this.getinfo();
        },
        methods: {
            calrealjobsort(){
                var jsort = deepClone(this.jobsort)
                if (this.newjob == false) {
                    this.$delete(jsort, jsort.indexOf('newjob'))
                }
                if (this.urgjob == false) {
                    this.$delete(jsort, jsort.indexOf('urgjob'))
                }
                if (this.recjob == false) {
                    this.$delete(jsort, jsort.indexOf('recjob'))
                }
                if (this.mapjob == false) {
                    this.$delete(jsort, jsort.indexOf('mapjob'))
                }
                this.realjobsort = jsort
            },
            // 职位列表 设置推荐栏目 开始
            dragstart(index) {
                this.dragIndex = index
            },
            // dragenter 和 dragover 事件的默认行为是拒绝接受任何被拖放的元素。
            // 因此，我们要在这两个拖放事件中使用`preventDefault`来阻止浏览器的默认行为
            dragenter(e, index) {
                e.preventDefault();
                this.enterIndex = index
                if (this.timeout !== null) {
                    clearTimeout(this.timeout)
                }
                // 拖拽事件的防抖
                this.timeout = setTimeout(() => {
                    if (this.dragIndex !== index) {
                        const source = this.jobsort[this.dragIndex]
                        this.jobsort.splice(this.dragIndex, 1)
                        this.jobsort.splice(index, 0, source)
                        // 排序变化后目标对象的索引变成源对象的索引
                        this.dragIndex = index;
                        this.calrealjobsort()
                    }
                }, 100);
            },
            dragover(e, index) {
                e.preventDefault();
            },
            // 职位列表 设置推荐栏目 结束

            setfooticonindex(index) {
                this.curr_footiconindex = index
            },
            footIconChange(file) {
                // 预览文件处理
                this.footnav[this.curr_footiconindex].pic = URL.createObjectURL(file.raw);
                this.footnav[this.curr_footiconindex].new_pic = 1;
                // 复刻文件信息
                this.footiconslist.push(file.raw);
            },
            footCurrIconChange(file) {
                // 预览文件处理
                this.footnav[this.curr_footiconindex].curpic = URL.createObjectURL(file.raw);
                this.footnav[this.curr_footiconindex].new_currpic = 1;
                // 复刻文件信息
                this.footcurriconslist.push(file.raw);
            },
            // 导航新增图标start
            setnavindex(index) {
                this.curr_navindex = index
            },
            iconChange(file) {
                // 预览文件处理
                this.nav[this.curr_navindex].pic = URL.createObjectURL(file.raw);
                this.nav[this.curr_navindex].new_pic = 1;
                // 复刻文件信息
                this.iconslist.push(file.raw);
            },
            // 导航新增图标end
            // 上传公告标题图片
            ggPicChange(file) {
                // 预览文件处理
                var pickey = 0
                if (this.noticestyle == '1') {
                    pickey = 0
                    this.noticestylepic1 = URL.createObjectURL(file.raw)
                } else if (this.noticestyle == '2') {
                    pickey = 1
                    this.noticestylepic2 = URL.createObjectURL(file.raw)
                } else if (this.noticestyle == '3') {
                    pickey = 2
                    this.noticestylepic3 = URL.createObjectURL(file.raw)
                }
                // 复刻文件信息
                this.ggpic[pickey] = file.raw;
                this.newggpickeys.push(pickey)
            },
            // 上传按钮一图标
            btnIcon1Change(file) {
                // 预览文件处理
                this.login_icon1 = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.login_icon1_file = file.raw;
            },
            // 上传按钮二图标
            btnIcon2Change(file) {
                // 预览文件处理
                this.login_icon2 = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.login_icon2_file = file.raw;
            },
            // 上传客服图标
            kfIconChange(file) {
                // 预览文件处理
                this.kficon = URL.createObjectURL(file.raw);
                // 复刻文件信息
                this.kficon_file = file.raw;
            },
            setcolor(color, type) {
                if (type == 1) {
                    this.maincolor = color
                } else {
                    this.secondcolor = color
                }
            },
            delnav(index) {
                var that = this
                that.$delete(that.nav, index)
            },
            addnav() {
                this.nav.push({
                    name: '',
                    pic: '',
                    display_n: true,
                    navsort: '',
                    navtype: '0',
                    nav_appid: '',
                    nav_ysid: '',
                    url: '',
                    news_class: '',
                    id: ''
                });
            },
            handleNavSwitch: function (index) {
                var id = this.nav[index].id,
                    dis_n = this.nav[index].display_n;
                this.navarr.forEach(function (v, k) {
                    v.forEach(function (i, d) {
                        if (id== i.id){
                            i.display_n = dis_n;
                        }
                    });
                });
            },
            getinfo() {
                let that = this;
                httpPost('m=tool&c=diy&a=app', {}).then(function (response) {
                    if (response.data.error == 0) {
                        var res = response.data.data
                        if (res.tplapp.length == 0 || res.tplapp.isnew == '1') {
                            that.showcachebtn = true
                        } else {
                            that.showcachebtn = false
                        }
                        if (res.tplapp.maincolor) {
                            that.maincolor = res.tplapp.maincolor
                        }
                        if (res.tplapp.secondcolor) {
                            that.secondcolor = res.tplapp.secondcolor
                        }
                        if (res.tplapp.searchshow == '1') {
                            that.searchshow = true
                        }
                        if (res.tplapp.searchstyle == '1') {
                            that.searchstyle = '1'
                        }
                        if (res.tplapp.search) {
                            that.searchstr = res.tplapp.search
                        }
                        if (res.tplapp.membershow == '1') {
                            that.membershow = true
                        }
                        if (res.tplapp.slideadstyle == '2') {
                            that.slideadstyle = '2'
                        }
                        that.adClassList = res.adClassList
                        that.newsClassList = res.newsClassList
                        if (res.tplapp.navshow == '1') {
                            that.navshow = true
                        }
						if (res.tplapp.jobspecshow == '1') {
							that.jobspecshow = true
						}
                        if (res.tplapp.navstyle == '2') {
                            that.navstyle = '2'
                        }
                        that.nav = res.nav
                        that.navigation = res.navigation
                        that.newsort = res.newsort
                        if (res.tplapp.notice == '1') {
                            that.notice = true
                        }
                        if (res.tplapp.noticestyle) {
                            that.noticestyle = res.tplapp.noticestyle
                        }
                        if (res.tplapp.noticestyleimg) {
                            if (res.tplapp.noticestyleimg[0]) {
                                that.noticestylepic1 = res.tplapp.noticestyleimg[0]
                            }
                            if (res.tplapp.noticestyleimg[1]) {
                                that.noticestylepic2 = res.tplapp.noticestyleimg[1]
                            }
                            if (res.tplapp.noticestyleimg[2]) {
                                that.noticestylepic3 = res.tplapp.noticestyleimg[2]
                            }
                        }
                        that.hd = res.hd
                        if (res.tplapp.hdid) {
                            that.hdid = res.tplapp.hdid
                        }
                        if (res.tplapp.jobspecnum) {
                            that.jobspecnum = res.tplapp.jobspecnum
                        }
                        if (res.tplapp.adshow == '1') {
                            that.adshow = true
                        }
                        if (res.tplapp.adhdid) {
                            that.adhdid = res.tplapp.adhdid + ''
                        }
                        if (res.adhd) {// 通栏广告预览图片
                            that.adhd = res.adhd
                        }
                    if (res.tplapp.adhdid2) {
                        that.adhdid2 = res.tplapp.adhdid2 + ''
                    }
                    if (res.adhd2) {// 通栏广告预览图片
                        that.adhd2 = res.adhd2
                    }
                    if (res.tplapp.adhdid3) {
                        that.adhdid3 = res.tplapp.adhdid3 + ''
                    }
                    if (res.adhd3) {// 通栏广告预览图片
                        that.adhd3 = res.adhd3
                    }
                        if (res.tplapp.login == '1') {
                            that.login = true
                        }
                        if (res.tplapp.login_title1) {
                            that.login_title1 = res.tplapp.login_title1
                        }
                        if (res.tplapp.login_subtitle1) {
                            that.login_subtitle1 = res.tplapp.login_subtitle1
                        }
                        if (res.tplapp.login_icon1) {
                            that.login_icon1 = res.tplapp.login_icon1
                        }
                        if (res.tplapp.login_title2) {
                            that.login_title2 = res.tplapp.login_title2
                        }
                        if (res.tplapp.login_subtitle2) {
                            that.login_subtitle2 = res.tplapp.login_subtitle2
                        }
                        if (res.tplapp.login_icon2) {
                            that.login_icon2 = res.tplapp.login_icon2
                        }
                        if (res.tplapp.hotcom == '1') {
                            that.hotcom = true
                        }
                        if (res.tplapp.hotcomstyle) {
                            that.hotcomstyle = res.tplapp.hotcomstyle
                        }
                        if (res.tplapp.hotcommore == '1') {
                            that.hotcommore = true
                        }
                        if (res.tplapp.hotcomnum) {
                            that.hotcomnum = res.tplapp.hotcomnum
                        }
                        if (res.tplapp.hotjob == '1') {
                            that.hotjob = true
                        }
                        if (res.tplapp.hotjobmore == '1') {
                            that.hotjobmore = true
                        }
                        if (res.tplapp.hotjobnum) {
                            that.hotjobnum = res.tplapp.hotjobnum
                        }
                        if (res.tplapp.zphshow == '1') {
                            that.zphshow = true
                        }
                        if (res.tplapp.zphmore == '1') {
                            that.zphmore = true
                        }
                        if (res.tplapp.zphnum) {
                            that.zphnum = res.tplapp.zphnum
                        }
                        if (res.tplapp.articleshow == '1') {
                            that.articleshow = true
                        }
                        if (res.tplapp.articlemore == '1') {
                            that.articlemore = true
                        }
                        if (res.tplapp.articletype) {
                            that.articletype = res.tplapp.articletype
                        }
                        if (res.tplapp.articlenum) {
                            that.articlenum = res.tplapp.articlenum
                        }
                        if (res.tplapp.job == '1') {
                            that.job = true
                        }
                        if (res.tplapp.jobstyle) {
                            that.jobstyle = res.tplapp.jobstyle
                        }
                        if (res.tplapp.jobsort) {
                            that.jobsort = res.tplapp.jobsort
                        }
                        if (res.tplapp.newjob == '1') {
                            that.newjob = true
                        }
                        if (res.tplapp.newjobnum) {
                            that.newjobnum = res.tplapp.newjobnum
                        }
                        if (res.tplapp.urgjob == '1') {
                            that.urgjob = true
                        }
                        if (res.tplapp.urgjobnum) {
                            that.urgjobnum = res.tplapp.urgjobnum
                        }
                        if (res.tplapp.recjob == '1') {
                            that.recjob = true
                        }
                        if (res.tplapp.recjobnum) {
                            that.recjobnum = res.tplapp.recjobnum
                        }
                        if (res.tplapp.mapjob == '1') {
                            that.mapjob = true
                        }
                        if (res.tplapp.mapjobnum) {
                            that.mapjobnum = res.tplapp.mapjobnum
                        }
                        if (res.tplapp.syszzmore == '1') {
                            that.syszzmore = true
                        }
                    if (res.tplapp.sysyjlogin == '1') {
                        that.sysyjlogin = true
                    }
                        if (res.tplapp.cshow == '1') {
                            that.cshow = true
                        }
                        if (res.tplapp.kfurl) {
                            that.kfurl = res.tplapp.kfurl
                        }
                        if (res.tplapp.kfcomid) {
                            that.kfcomid = res.tplapp.kfcomid
                        }
                        if (res.tplapp.kficon) {
                            that.kficon = res.tplapp.kficon
                        }
                        if (res.tplapp.footstyle) {
                            that.footstyle = res.tplapp.footstyle
                        }
                        that.calrealjobsort()
                        that.navarr = res.navarr
                        that.footnav = res.footnav;
                        that.islook = true
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            sortChange(){
                var sort = ['theme', 'search', 'member', 'nav', 'jobspec', 'notice'];
                for (item in this.newsort) {
                    sort.push(this.newsort[item].value)
                }
                sort.push('syszz')
                sort.push('connect')
                sort.push('foot')
                httpPost('m=tool&c=diy&a=appsort', {sort: sort}).then(function () {})
            },
            makeCache() {
                let that = this;
                httpPost('m=tool&c=diy&a=makeCache', {}).then(function () {
                    message.success('生成成功', function () {
                        that.getinfo();
                    });
                }).catch(function (error) {
                    console.log(error);
                })
            },
            async setsave(type) {
                let that = this;
                var params = new FormData();
                params.append('type', type)
                if (type == 'theme') {// 自定义主题
                    params.append('maincolor', this.maincolor)
                    params.append('secondcolor', this.secondcolor)
                } else if (type == 'search') {// 搜索
                    params.append('searchshow', this.searchshow)
                    params.append('searchstyle', this.searchstyle)
                    params.append('search', this.searchstr)
                } else if (type == 'member') {// 幻灯广告区
                    params.append('membershow', this.membershow)
                    params.append('slideadstyle', this.slideadstyle)
                    params.append('hdid', this.hdid)
                } else if (type == 'nav') {// 导航
                    params.append('navshow', this.navshow)
                    params.append('navstyle', this.navstyle)
                    for (fl in this.iconslist) {
                        params.append('navpic[]', this.iconslist[fl])
                    }

                    params.append('nav', JSON.stringify(this.nav))
                } else if (type == 'jobspec') {// 职位专题
                    params.append('jobspecshow', this.jobspecshow)
                    params.append('jobspecnum', this.jobspecnum)
                } else if (type == 'notice') {// 公告
                    params.append('notice', this.notice)
                    params.append('noticestyle', this.noticestyle)
                    params.append('newggpickeys', JSON.stringify(this.newggpickeys))
                    for (fl in this.ggpic) {
                        params.append('gigpic[]', this.ggpic[fl])
                    }
                } else if (type == 'ad') {// 通栏公告
                params.append('adshow', this.adshow)
                params.append('adhdid', this.adhdid)
                params.append('adhdid2', this.adhdid2)
                params.append('adhdid3', this.adhdid3)
                } else if (type == 'login') {// 登陆注册
                    params.append('login', this.login)
                    params.append('login_title1', this.login_title1)
                    params.append('login_subtitle1', this.login_subtitle1)
                    params.append('login_icon1', this.login_icon1_file)
                    params.append('login_title2', this.login_title2)
                    params.append('login_subtitle2', this.login_subtitle2)
                    params.append('login_icon2', this.login_icon2_file)
                } else if (type == 'hotcom') {// 名企展示
                    params.append('hotcom', this.hotcom)
                    params.append('hotcomstyle', this.hotcomstyle)
                    params.append('hotcommore', this.hotcommore)
                    params.append('hotcomnum', this.hotcomnum)
                } else if (type == 'hotjob') {// 热门职位
                    params.append('hotjob', this.hotjob)
                    params.append('hotjobmore', this.hotjobmore)
                    params.append('hotjobnum', this.hotjobnum)
                } else if (type == 'zph') {// 招聘会
                    params.append('zphshow', this.zphshow)
                    params.append('zphmore', this.zphmore)
                    params.append('zphnum', this.zphnum)
                } else if (type == 'article') {// 资讯
                    params.append('articleshow', this.articleshow)
                    params.append('articlemore', this.articlemore)
                    params.append('articletype', this.articletype)
                    params.append('articlenum', this.articlenum)
                } else if (type == 'job') {// 职位列表
                    params.append('job', this.job)
                    params.append('jobstyle', this.jobstyle)
                    params.append('jobsort', JSON.stringify(this.jobsort))
                    params.append('newjob', this.newjob)
                    params.append('newjobnum', this.newjobnum)
                    params.append('urgjob', this.urgjob)
                    params.append('urgjobnum', this.urgjobnum)
                    params.append('recjob', this.recjob)
                    params.append('recjobnum', this.recjobnum)
                    params.append('mapjob', this.mapjob)
                    params.append('mapjobnum', this.mapjobnum)
                } else if (type == 'syszz') {// 站在资质
                    params.append('syszzmore', this.syszzmore)
            } else if (type == 'sysyjlogin') {// 一键登陆
                params.append('sysyjlogin', this.sysyjlogin)
                } else if (type == 'connect') {// 浮动客服
                    params.append('cshow', this.cshow)
                    params.append('kfurl', this.kfurl)
                    params.append('kfcomid', this.kfcomid)
                    params.append('kficon', this.kficon_file)
                } else if (type == 'foot') {// 底部导航
                    params.append('footstyle', this.footstyle)
                    params.append('footnav', JSON.stringify(this.footnav))
                    for (fl in this.footiconslist) {
                        params.append('footpic[]', this.footiconslist[fl])
                    }
                    for (fl in this.footcurriconslist) {
                        params.append('footchoosed[]', this.footcurriconslist[fl])
                    }
                }
                that.saveLoading = true;
                httpPost('m=tool&c=diy&a=save', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg);
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                }).finally(function () {
                    setTimeout(function () {
                        that.saveLoading = false;
                    }, 2000);
                });
            },
        },
    };
</script>
<style scoped>
    .mouldappLeCont{overflow-y:auto;position:relative;width:100%;height:calc(100% - 60px)}
    .mouldapBanne .el-carousel__item h3{color:#475669;font-size:14px;opacity:.75;margin:0;line-height:90px;text-align:center}
    .mouldapBanne .el-carousel__item:nth-child(2n){background-color:#99a9bf}
    .mouldapBanne .el-carousel__item:nth-child(2n+1){background-color:#d3dce6}
    .mouldapBanne .el-carousel__arrow{width:28px;height:28px}
    .mouldapnavs .el-carousel__arrow{width:28px;height:28px;display:none}
    .mouldapnavs .el-carousel__item{background:#fff}
    .mouldapnavs .el-carousel__indicators{background:rgba(0,0,0,.2);border-radius:8px;bottom:8px}
    .mouldapnavs .el-carousel__indicator--horizontal{padding:4px 5px}
    .mouldapnavs .el-carousel__button{width:8px;height:8px;border-radius:50%}
    .mouldappLeft .el-tabs__nav-scroll{overflow:hidden;position:relative;padding:0;width:100%}
    .mouldappLeft .el-tabs__header{overflow:hidden;position:relative;width:350px;height:100%;margin-right:0}
    .mouldappLeft .el-tabs__content{position:relative;width:auto;overflow:hidden;padding:0;height:100%}
    .mouldappLeft .el-tabs{width:100%;height:100%}
    .mouldappLeft .el-tabs__item{padding:0 20px;height:45px;line-height:45px}
    .mouldappLeft .is-active{background:#eee}
    .mouldappLeft .el-tab-pane{overflow-y:auto;position:relative;width:calc(100% - 24px);height:100%;padding:0 12px}
    .mouldappLeft .el-tab-pane::-webkit-scrollbar{display:none}
    .divSlot{overflow:hidden;position:relative;display:flex;align-items:center;justify-content:space-between}
    .el-radio{margin-right:0;display:flex;align-items:center;margin-bottom:12px;max-width:500px}
    .el-radio.is-bordered+.el-radio.is-bordered{margin-left:0}
    .el-radio__label{overflow:hidden;position:relative;width:100%;display:flex;align-items:center;justify-content:space-between}
    .el-radio--small.is-bordered{padding:8px 15px 8px 10px}
    .color1{background-color:#0078ff;border-color:#0078ff}
    .color2{background-color:#fa6400;border-color:#fa6400}
    .color3{background-color:#10ae51;border-color:#10ae51}
    .color4{background-color:#ff5857;border-color:#ff5857}
    .color5{background-color:#ac6cff;border-color:#ac6cff}
    .mouldapjobspecial{margin-top:10px}
    .mouldapjobspecial img{width:32%}
    .appdiy_gg{display:flex;margin:10px 0 5px 0;padding:12px;background:#fff}
    .appdiy_ggone{align-items:center}
    .appdiy_ggthree{align-items:center}
    .gg_right{padding-left:8px}
    .appdiy_gg span{font-size:14px;display:-webkit-box;-webkit-line-clamp:1;overflow:hidden;text-overflow:ellipsis;-webkit-box-orient:vertical;word-break:break-all}
    .app_adgg{margin:5px 0}
    .app_adgg img{width:100%;height:46px; border-radius: 5px;}
    .app_adgg2 img{width:48%;height:46px; border-radius: 5px;}
    .app_adgg3 img{width:32%;height:46px; border-radius: 5px;}
    .appset_log{margin:5px 0;display:flex;justify-content:space-between}
    .appset_log_item{padding:8px;height:48px;background:#fff;width:43%;border-radius:5px;display:flex}
    .log_item_right{margin-left:6px}
    .log_item_right_tit{font-size:14px;font-weight:bolder}
    .log_item_right_subtit{font-size:12px;overflow:hidden;color:#666}
    .hotcomstyle{margin:5px 0}
    .mq_style_itemone{background:#fff;border-radius:8px;overflow:hidden;position:relative}
    .mq_style_itemone_name{color:#000;font-size:14px;font-weight:bolder}
    .mq_style_itemone_top{display:flex;font-size:12px;align-items:center;justify-content:space-between}
    .mq_style_itemone_bottom{overflow:hidden;position:relative;width:100%;display:flex;align-items:center;flex-wrap:wrap;justify-content:space-between}
    .mq_style_itemone_bottom_item{background:#f8f8f8;width:40%;margin:4px 4px 0 0;padding:10px;display:flex;align-items:flex-start}
    .mq_itemone_item_right{font-size:12px;margin-left:4px}
    .mq_itemone_item_name{color:#666;font-size:14px;font-weight:bolder;margin-bottom:2px}
    .mq_style_itemtwo_top{padding:0 12px;display:flex;font-size:12px;align-items:center;justify-content:space-between}
    .mq_style_itemtwo_name{font-size:14px}
    .mq_style_itemtwo_bottom{display:flex;align-items:center;flex-wrap:wrap;justify-content:space-between;overflow:hidden;position:relative;padding:8px 8px 0 8px}
    .mq_style_itemtwo_item{text-align:center;width:29.5%;background:#fff;border-radius:4px;padding:10px 0;font-size:12px;color:#666;overflow:hidden;position:relative}
    .mq_style_itemtwo{overflow:hidden;position:relative;padding:10px 0}
    .appset_hotzw{background:#fff;padding:12px;margin:5px 0}
    .zph_style_top{display:flex;justify-content:space-between;overflow:hidden;font-size:13px;color:#333;padding-bottom:5px}
    .hotjob_bottom{overflow:hidden;position:relative;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between}
    .hotjob_item{overflow:hidden;text-align:center;width:31%;margin:3px 0;line-height:30px;background:#f3f3f3;border-radius:2px;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#333}
    .appset_zph{background:#fff;padding:12px}
    .zph_style_top{display:flex;justify-content:space-between}
    .zph_item{display:flex;margin-top:4px}
    .zph_item_left{width:64px}
    .zph_item_right{flex:1;margin-left:4px;overflow:hidden;font-size:12px}
    .zph_item_right span{display:-webkit-box;-webkit-line-clamp:2;overflow:hidden;text-overflow:ellipsis;-webkit-box-orient:vertical;word-break:break-all}
    .appset_zx{background:#fff;padding:12px;margin:5px 0}
    .zph_style_top{display:flex;justify-content:space-between}
    .zph_item{display:flex;margin-top:4px}
    .zph_item_left{width:64px}
    .zph_item_right{flex:1;margin-left:4px}
    .zph_item_right span{display:-webkit-box;-webkit-line-clamp:2;overflow:hidden;text-overflow:ellipsis;-webkit-box-orient:vertical;word-break:break-all}
    .zph_itemtwo{flex-direction:column;width:48%;flex-wrap:wrap}
    .zph_item_lefttwo{width:100%;height:46px}
    .zph_itemthree{display:-webkit-box;-webkit-line-clamp:1;overflow:hidden;text-overflow:ellipsis;-webkit-box-orient:vertical;word-break:break-all}
    .yun_newedition_jobmore{background:#fff;padding:10px 15px;margin-top:1px}
    .yun_newedition_footer{position:absolute;overflow:hidden;right:0;bottom:120px;z-index:22}
    .appfooter{width:100%;height:50px;background:#fff;position:relative}
    .yun_footer_nav{position:relative;width:100%;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-around}
    .yun_footer_nav li{width:19%;display:inline-block;text-align:center;font-size:12px;position:relative}
    .yun_footer_nav li a{padding-top:30px;padding-bottom:5px;display:inline-block;position:relative;font-size:12px;color:#666}
    .yun_footer_nav li img{width:20px;height:20px;display:inline-block;position:absolute;left:50%;top:7px;margin-left:-10px}
    .style_top{overflow:hidden;position:relative;width:100%;display:flex;align-items:center;justify-content:space-around}
    .style_top_item{position:relative;text-align:center;overflow:hidden;font-size:13px}
    .style_top_kudn{overflow:hidden;position:relative;width:100%}
    .zw_xh_line{margin-top:2px;width:18px;height:2px;display:block;background:#0078ff}
    .style_bottom{padding:12px;border-radius:6px;background:#fff;margin:12px 0 0 0}
    .style_bottom_item_top_info{display:flex;align-items:center;justify-content:space-between;position:relative;overflow:hidden}
    .info_name{color:#000;font-size:14px;font-weight:700}
    .info_price{color:#ff4c52;font-size:14px;font-weight:bolder}
    .style_bottom_item_top_sec{margin:12px 0;overflow:hidden;font-size:12px;color:#555;display:flex;flex-wrap:wrap;align-items:center}
    .style_bottom_item_top_tagbox{display:flex;flex-wrap:wrap;margin-bottom:12px}
    .style_bottom_item_top_tag{margin-top:2px;margin-right:4px;background:#e5f1ff;color:#2778f8;padding:4px;border-radius:4px;font-size:12px}
    .style_bottom_item_bottom{display:flex;padding-top:12px;align-items:center;justify-content:space-between}
    .zw_styletwo_tag{border:1px solid #e4e0e0;background:#fff;color:#666}
    .zw_styletwo_btn{width:45px;height:22px;text-align:center;background:#f60;color:#fff;padding:4px;border-radius:4px;font-size:12px}
    .zw_stylethree_tag{background:#f5f5f5;color:#b2b2b2}
    .wap_mb_show_nav_list_p{height:30px;line-height:30px;font-size:12px}
    .clear{clear:both}
    .admin_new_cz_tj{font-size:12px;line-height:30px;color:#2778f8;background-color:#f5f8ff;height:30px;padding:0 15px;display:inline-block;text-align:center;border-width:1px;border-style:solid;border-color:#2778f8;border-image:initial;border-radius:4px;margin-right:20px}
    .admin_new_cz_tj:hover{background:#2778f8;color:#fff;text-decoration:none}
    .footstyle{position:absolute;background:#f0f2f5;width:100%;left:0;bottom:0;z-index:20}
    .zph_style_bottom{overflow:hidden;position:relative;width:100%;display:flex;align-items:center;justify-content:space-between}
    .nav_none{display:none}
</style>