<template>
    <div class="moduleElHight" style="display: flex;" v-if="islook">
        <div class="mouldappLeft">
            <div class="wapHeaders">
                <div class="wapHeadName">
                    <span>设置手机自定义模板</span>
                </div>
                <div class="wapHeadKaig">
                    <el-switch @change="wapdiychange" v-model="tplmoblie.wapdiy" >
                    </el-switch>
                </div>
            </div>
            <div class="wapCollapse">
                <el-collapse v-model="activeNames" @change="handleChange">
                    <div v-for="(item, sindex) in sort" :key="sindex">
                        <el-collapse-item v-if="item == 'hearder'" title="头部" name="header">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>头部</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollColor">
                                    <span style="background: #63B359;" @click="setcolor(1)"></span>
                                    <span style="background: #2C9F67;" @click="setcolor(2)"></span>
                                    <span style="background: #0180CC;" @click="setcolor(3)"></span>
                                    <span style="background: #5885CF;" @click="setcolor(4)"></span>
                                    <span style="background: #9062C0;" @click="setcolor(5)"></span>
                                    <span style="background: #D09A45;" @click="setcolor(6)"></span>
                                    <span style="background: #E4B138;" @click="setcolor(7)"></span>
                                    <span style="background: #EE903C;" @click="setcolor(8)"></span>
                                    <span style="background: #DD6549;" @click="setcolor(9)"></span>
                                    <span style="background: #CC463D;" @click="setcolor(10)"></span>
                                </div>
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>分站：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.site" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.site" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>Logo：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.logo" label="1">LOGO</el-radio>
                                            <el-radio v-model="tplmoblie.logo" label="2">文字</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>栏目：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.heardercss" label="1">样式1</el-radio>
                                            <el-radio v-model="tplmoblie.heardercss" label="2">样式2</el-radio>
                                            <el-radio v-model="tplmoblie.heardercss" label="3">样式3</el-radio>
                                            <el-radio v-model="tplmoblie.heardercss" label="4">样式4</el-radio>
                                            <el-radio v-model="tplmoblie.heardercss" label="5">样式5</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('hearder')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'hd'" name="hd">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>幻灯片</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.hdshow" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.hdshow" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList" v-for="(item, index) in hd" :key="index">
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>图标：</span>
                                            </div>
                                            <div class="wapCollFroInpt">
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                    :show-file-list="false" :on-change="hdpicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                        @click="sethdpicindex(index)">添加图标
                                                    </el-button>
                                                </el-upload>
                                                <el-image style="width: 32px; height: 32px" :src="item.pic"></el-image>
                                                <el-button style="margin-left: auto;" type="text" icon="el-icon-delete"
                                                    @click="delhdpic(index)">删除
                                                </el-button>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="item.name" placeholder="请输入标题"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="item.url" placeholder="请输入链接"></el-input>
                                                <span class="spantip">建议图标尺寸：不小于640*280像素</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                @click="addhd">新增一个
                                            </el-button>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('hd')" :disabled="saveLoading">提交</el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'search'" name="search">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>搜索</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.searchshow" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.searchshow" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标题：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input v-model="tplmoblie.search" placeholder="请输入标题"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>类型：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-select v-model="tplmoblie.searchurl" placeholder="请选择">
                                                <el-option :value="''">请选择</el-option>
                                                <el-option v-for="(item, index) in searchurl" :key="index" :label="item.name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('search')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'nav'" name="nav">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>导航</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList" v-for="(item, index) in nav" :key="index">
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>图标：</span>
                                            </div>
                                            <div class="wapCollFroInpt">
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                    :show-file-list="false" :on-change="navpicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                        @click="setnavpicindex(index)">添加图标
                                                    </el-button>
                                                </el-upload>
                                                <el-image style="width: 32px; height: 32px" :src="item.pic"></el-image>
                                                <el-button style="margin-left: auto;" type="text" icon="el-icon-delete"
                                                    @click="delnav(index)">删除
                                                </el-button>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="item.name" placeholder="请输入标题"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-select v-model="item.url" placeholder="请选择">
                                                    <el-option :value="''">请选择</el-option>
                                                    <el-option v-for="(nitem, nindex) in navigation" :key="nindex"
                                                        :label="nitem.name" :value="nitem.id">
                                                    </el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>排序：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="item.sort" placeholder="请输入排序"></el-input>
                                                <span class="spantip">排序规则：小前大后</span>
                                                <span class="spantip">建议图标尺寸：不小于64*64像素</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                @click="addnav">新增一个
                                            </el-button>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('nav')" :disabled="saveLoading">提交</el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'notice'" name="notice">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>公告</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.notice" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.notice" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('notice')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'indexnav'" name="indexnav">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>图片导航</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.indexnav" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.indexnav" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroShum">左侧显示内容：</div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>图标：</span>
                                            </div>
                                            <div class="wapCollFroInpt">
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                    :show-file-list="false" :on-change="indexnavpicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                        @click="changeindexnavtype('1')">添加图标
                                                    </el-button>
                                                </el-upload>
                                                <el-image style="width: 32px; height: 32px" :src="indexnav1.pic"></el-image>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="indexnav1.name" placeholder="请输入标题"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标语：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="indexnav1.desc" placeholder="请输入标语"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-select v-model="indexnav1.url" placeholder="请选择">
                                                    <el-option v-for="item in navigation" :key="item.id" :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroShum">右上侧显示内容：</div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>图标：</span>
                                            </div>
                                            <div class="wapCollFroInpt">
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                    :show-file-list="false" :on-change="indexnavpicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                        @click="changeindexnavtype('2')">添加图标
                                                    </el-button>
                                                </el-upload>
                                                <el-image style="width: 32px; height: 32px" :src="indexnav2.pic"></el-image>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="indexnav2.name" placeholder="请输入内容"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标语：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="indexnav2.desc" placeholder="请输入内容"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-select v-model="indexnav2.url" placeholder="请选择">
                                                    <el-option v-for="item in navigation" :key="item.id" :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroShum">右下侧显示内容：</div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>图标：</span>
                                            </div>
                                            <div class="wapCollFroInpt">
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                    :show-file-list="false" :on-change="indexnavpicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                        @click="changeindexnavtype('3')">添加图标
                                                    </el-button>
                                                </el-upload>
                                                <el-image style="width: 32px; height: 32px" :src="indexnav3.pic"></el-image>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="indexnav3.name" placeholder="请输入标题"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标语：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="indexnav3.desc" placeholder="请输入标语"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-select v-model="indexnav3.url" placeholder="请选择">
                                                    <el-option v-for="item in navigation" :key="item.id" :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('indexnav')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'reglogin'" name="reglogin">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>登陆注册</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.reglogin" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.reglogin" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroShum">上侧描述：</div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>描述：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="tplmoblie.reglogindesc" placeholder="请输入描述"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFroShum">左侧按钮：</div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>名称：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="tplmoblie.login" placeholder="请输入名称"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>颜色：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="tplmoblie.logincolor" placeholder="请输入颜色"></el-input>
                                            </div>
                                            <div style="margin-top: 5px;">
                                                <el-color-picker v-model="tplmoblie.logincolor"></el-color-picker>
                                            </div>
                                        </div>
                                        <div class="wapCollFroShum">右侧按钮：</div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>名称：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="tplmoblie.reg" placeholder="请输入名称"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>颜色：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="tplmoblie.regcolor" placeholder="请输入颜色"></el-input>
                                            </div>
                                            <div style="margin-top: 5px;">
                                                <el-color-picker v-model="tplmoblie.regcolor"></el-color-picker>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('reglogin')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'ad'" name="ad">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>广告</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.ad" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.ad" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList" v-for="(item, index) in adlist" :key="index">
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>图标：</span>
                                            </div>
                                            <div class="wapCollFroInpt">
                                                <el-upload class="upload-demo" :action="''" :accept="pic_accept" :auto-upload="false"
                                                    :show-file-list="false" :on-change="adpicChange">
                                                    <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                        @click="setadpicindex(index)">添加图标
                                                    </el-button>
                                                </el-upload>
                                                <el-image style="width: 32px; height: 32px" :src="item.pic"></el-image>
                                                <el-button style="margin-left: auto;" type="text" icon="el-icon-delete"
                                                    @click="delad(index)">删除
                                                </el-button>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <el-input v-model="item.name" placeholder="请输入标题"></el-input>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <div class="wapCollFroInpt" style="max-width: 500px;">
                                                    <el-input v-model="item.url" placeholder="请输入链接"></el-input>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wapCollFromBane">
                                            <div class="wapCollFroTite">
                                                <span></span>
                                            </div>
                                            <div class="wapCollFroInpt" style="max-width: 500px;">
                                                <span class="spantip">建议图标尺寸：不小于300*180像素</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button size="small" type="primary" plain icon="el-icon-plus"
                                                @click="addad">新增一个
                                            </el-button>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('ad')" :disabled="saveLoading">提交</el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'rewardjob'" name="rewardjob">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>赏金职位</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.rewardjob" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.rewardjob" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>类型：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.rewardjobcss" label="1">列表</el-radio>
                                            <el-radio v-model="tplmoblie.rewardjobcss" label="2">缩略图</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标签：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-checkbox v-model="tplmoblie.rewardjobcom">企业</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.rewardjobsalary">薪资</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.rewardjobreward">赏金</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.rewardjobdate">时间</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.rewardjobmore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.rewardjobmore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.rewardjobnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('rewardjob')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'hotjob'" name="hotjob">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>热门职位</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.hotjob" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.hotjob" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.hotjobmore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.hotjobmore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.hotjobnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('hotjob')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'newjob'" name="newjob">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>最新职位</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.newjob" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.newjob" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标签：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-checkbox v-model="tplmoblie.newjobcom">企业</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.newjobsalary">薪资</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.newjobcity">地区</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.newjobdate">时间</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.newjobwelfare">福利待遇</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.newjobmore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.newjobmore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.newjobnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('newjob')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'hotcom'" name="hotcom">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>名企展示</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.hotcom" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.hotcom" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标签：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-checkbox v-model="tplmoblie.hotcomlogo">LOGO</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.hotcomhy">行业</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.hotcomcity">地区</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.hotcommore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.hotcommore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.hotcomnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('hotcom')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'recjob'" name="recjob">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>推荐职位</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.recjob" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.recjob" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标签：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-checkbox v-model="tplmoblie.recjobcom">企业</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.recjobsalary">薪资</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.recjobcity">地区</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.recjobdate">时间</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.recjobwelfare">福利待遇</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.recjobmore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.recjobmore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.recjobnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('recjob')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'urgentjob'" name="urgentjob">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>紧急职位</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.urgentjob" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.urgentjob" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标签：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-checkbox v-model="tplmoblie.urgentjobcom">企业</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.urgentjobsalary">薪资</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.urgentjobcity">地区</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.urgentjobdate">时间</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.urgentjobwelfare">福利待遇</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.urgentjobmore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.urgentjobmore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.urgentjobnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('urgentjob')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'resume'" name="resume">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>最新简历</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.resume" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.resume" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>方式：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.resumepic" label="1">文字</el-radio>
                                            <el-radio v-model="tplmoblie.resumepic" label="2">图文</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>标签：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-checkbox v-model="tplmoblie.resumeexp">经验</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.resumecity">地区</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.resumeedu">学历</el-checkbox>
                                            <el-checkbox v-model="tplmoblie.resumeexpect">意向职位</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.resumemore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.resumemore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.resumenum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('resume')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'article'" name="article">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>职场资讯</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.article" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.article" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>类型：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.articleclass" label="1">最新</el-radio>
                                            <el-radio v-model="tplmoblie.articleclass" label="2">推荐</el-radio>
                                            <el-radio v-model="tplmoblie.articleclass" label="3">热门</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>样式：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.articlecss" label="1">缩略图</el-radio>
                                            <el-radio v-model="tplmoblie.articlecss" label="2">图片列表</el-radio>
                                            <el-radio v-model="tplmoblie.articlecss" label="3">列表</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.articlemore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.articlemore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.articlenum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('article')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'zph'" name="zph">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>招聘会</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.zph" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.zph" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>更多：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.zphmore" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.zphmore" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input type="number" v-model="tplmoblie.zphnum"
                                                placeholder="请输入数量"></el-input>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('zph')" :disabled="saveLoading">提交</el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item v-if="item == 'jobclass'" name="jobclass">
                            <template slot="title">
                                <div class="wapColTite">
                                    <div class="wapColTiName">
                                        <span>职位类别</span>
                                    </div>
                                    <div class="wapColTiPaix">
                                        <el-button type="text" icon="el-icon-sort-up" @click.stop="changesort(sindex, 1)">
                                            上移
                                        </el-button>
                                        <el-button type="text" icon="el-icon-sort-down" @click.stop="changesort(sindex, 2)">
                                            下移
                                        </el-button>
                                    </div>
                                </div>
                            </template>
                            <!-- 折叠内容 -->
                            <div class="wapCollConts">
                                <div class="wapCollFrom">
                                    <div class="wapCollFroShum">一级类别：</div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.jobclassone" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.jobclassone" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input :disabled="jobclass1disabled" type="number"
                                                v-model="tplmoblie.jobclassonenum" placeholder="请输入数量"></el-input>
                                        </div>
                                        <div style="margin-left: 10px;">
                                            <el-checkbox v-model="tplmoblie.jobclassonenumall">全部</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFroShum" v-if="tplmoblie.jobclassone != '2'">二级类别：</div>
                                    <div class="wapCollFromList" v-if="tplmoblie.jobclassone != '2'">
                                        <div class="wapCollFroTite">
                                            <span>显示：</span>
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-radio v-model="tplmoblie.jobclasstwo" label="1">显示</el-radio>
                                            <el-radio v-model="tplmoblie.jobclasstwo" label="2">隐藏</el-radio>
                                        </div>
                                    </div>
                                    <div class="wapCollFromBane" v-if="tplmoblie.jobclassone != '2'">
                                        <div class="wapCollFroTite">
                                            <span>数量：</span>
                                        </div>
                                        <div class="wapCollFroInpt" style="max-width: 500px;">
                                            <el-input :disabled="jobclass2disabled" type="number"
                                                v-model="tplmoblie.jobclasstwonum" placeholder="请输入数量"></el-input>
                                        </div>
                                        <div style="margin-left: 10px;">
                                            <el-checkbox v-model="tplmoblie.jobclasstwonumall">全部</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="wapCollFromList">
                                        <div class="wapCollFroTite">
                                            <!-- <span>栏目：</span> -->
                                        </div>
                                        <div class="wapCollFroInpt">
                                            <el-button type="primary" size="mini" @click="setsave('jobclass')" :disabled="saveLoading">提交
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-collapse-item>
                    </div>
                </el-collapse>
            </div>
        </div>
        <div class="mouldappRigh">
            <div class="mouldappIphone">
                <div class="mouldappIphCont">
                    <div class="mouldappGundong">
                        <div v-for="(item,key) in sort" :key="key">
                            <div class="wap_header_box" v-if="item == 'hearder'">
                                <div :class="tplmoblie.color ? 'wap_header bg' + tplmoblie.color : 'wap_header'">
                                    <div class="logo" id="logo1" v-if="tplmoblie.logo == '1' && tplmoblie.logo != ''">
                                        <img :src="sy_wap_logo">
                                    </div>
                                    <div class="header_p_z" id="logo2" v-if="tplmoblie.logo == '2' || tplmoblie.logo == ''">
                                        PHPYUN人才网
                                    </div>
                                    <a href="" id="site" v-if="tplmoblie.site == '1'"><span class="search_con_l fl"><i
                                                class="city_icon iconfont"></i>全国</span></a>
                                </div>
                            </div>
                            <div class="mouldapBanne" v-if="item == 'hd' && tplmoblie.hdshow == '1'">
                                <el-carousel height="90px">
                                    <el-carousel-item v-for="(item, index) in hd" :key="index">
                                        <el-image :style="{ height: `85px`, width: `100%` }" :src="item.pic"></el-image>
                                    </el-carousel-item>
                                </el-carousel>
                            </div>
                            <div class="wap_mb_show_search " v-if="item == 'search' && tplmoblie.searchshow == '1'">
                                <div class="index_search_cont">
                                    <div class="index_formFiled">
                                        <input type="text" value="" name="keyword" class="index_input_search" id="pkeyword"
                                            :placeholder="tplmoblie.search">
                                        <input type="submit" value="搜职位" class="index_input_btn">
                                        <i class="index_input_btn_icon iconfont_index_search"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="wap_mb_show_nav" v-if="item == 'nav'">
                                <ul class="wap_mb_show_nav_list" id="paddnav">
                                    <li :id="'prn' + pv.id" v-for="(pv,pkey) in nav" :key="pkey">
                                        <img :src="pv.pic ? pv.pic : '../../images/wap_show_img1.png'"
                                            width="40" height="40" :id="'pimgn' + pv.id">
                                        <div class="wap_mb_show_nav_list_p" :id="'pnavname' + pv.id">{{ pv.name }}</div>
                                    </li>
                                </ul>
                            </div>
                            <div class="wap_mb_show_imgnav mbt10" v-if="item == 'indexnav' && tplmoblie.indexnav == '1'">
                                <div class="index_nav_yd">
                                    <div class="index_nav_yd_left">
                                        <div class="index_nav_yd_left_tit" id="pindexnavname1">{{ indexnav1.name }}</div>
                                        <img class="index_nav_yd_left_icon"
                                            :src="indexnav1.pic ? indexnav1.pic : '../../images/nav_icon_wz.png'"
                                            id="pindeximg1">
                                        <div class="index_nav_yd_left_job" id="pindexnavdesc1">{{ indexnav1.desc }}</div>
                                    </div>
                                    <div class="index_nav_yd_right">
                                        <div class="index_nav_yd_right_t">
                                            <div class="index_nav_yd_right_t_name" id="pindexnavname2">{{ indexnav2.name }}
                                            </div>
                                            <div class="index_nav_yd_right_t_p" id="pindexnavdesc2">{{ indexnav2.desc }}</div>
                                            <img class="index_nav_yd_right_icon"
                                                :src="indexnav2.pic ? indexnav2.pic : '../../images/nav_icon_dp.png'"
                                                id="pindeximg2">
                                        </div>
                                        <div class="index_nav_yd_right_b">
                                            <div class="index_nav_yd_right_bname" id="pindexnavname3">{{ indexnav3.name }}
                                            </div>
                                            <div class="index_nav_yd_right_bp" id="pindexnavdesc3">{{ indexnav3.desc }}</div>
                                            <img class="index_nav_yd_right_bicon"
                                                :src="indexnav3.pic ? indexnav3.pic : '../../images/nav_icon_jz.png'"
                                                id="pindeximg3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clear" v-if="item == 'notice'"></div>
                            <div class="yun_wap_notice sxl mbt10" v-if="item == 'notice' && tplmoblie.notice == '1'">
                                <span class="yun_wap_notice_tit"><i class="yun_wap_notice_tit_s"></i></span>
                                <ul class="yun_wap_notice_list sxlist">
                                    <li>PHPyun宿迁站，正式开通！</li>
                                </ul>
                            </div>
                            <div class="clear" v-if="item == 'reglogin'"></div>
                            <section id="reglogin" v-if="item == 'reglogin' && tplmoblie.reglogin == '1'">
                                <div class="index_warp_content mt10">
                                    <div class="index_login">
                                        <div class="index_login_p" id="preglogindesc">{{ tplmoblie.reglogindesc }}</div>
                                        <div class="index_logoin_sub">
                                            <a href="###" class="index_logoin_bth" id="plogin"
                                                :style="tplmoblie.logincolor ? { background: `${tplmoblie.logincolor}` } : ''">{{ tplmoblie.login }}</a>
                                            <a href="###" class="index_logoin_bth index_reg_bth" id="preg"
                                                :style="tplmoblie.regcolor ? { background: `${tplmoblie.regcolor}` } : ''">{{ tplmoblie.reg }}</a>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <!--广告预览-->
                            <div class="clear" v-if="item == 'ad'"></div>
                            <section id="ad" v-if="item == 'ad' && tplmoblie.ad == '1'">
                                <div class="yun_companyList">
                                    <ul class="clearfix" id="paddadlist">
                                        <li v-for="(pv,adkey) in adlist" :id="'prad' + pv.id" :key="adkey">
                                            <img :src="pv.pic" alt="pv.name" :id="'pimgad' + pv.id">
                                        </li>
                                    </ul>
                                </div>
                            </section>
                            <!--赏金职位-->
                            <div class="clear" v-if="item == 'rewardjob'"></div>
                            <div class="wap_mb_show_sj" id="rewardjob"
                                v-if="item == 'rewardjob' && tplmoblie.rewardjob == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_titsj"></i>赏金职位</span>
                                            <span class="rewardjobmore wap_titmore"
                                                v-if="tplmoblie.rewardjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">赏金职位</span>
                                            <span class="rewardjobmore wap_titmore"
                                                v-if="tplmoblie.rewardjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_titsj"></i>赏金职位</span>
                                            <span class="rewardjobmore wap_titmore"
                                                v-if="tplmoblie.rewardjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class=" wap_tit4_icon_sj"></i>
                                                <font color="#666">赏金职位</font>
                                            </span>
                                            <span class="rewardjobmore wap_titmore"
                                                v-if="tplmoblie.rewardjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">赏金职位</span>
                                            <span class="rewardjobmore wap_titmore"
                                                v-if="tplmoblie.rewardjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_diy_sjcont" id="rewardjobcss2" v-if="tplmoblie.rewardjobcss == '2'">
                                    <div class="wap_diy_sjlist">
                                        <a href="###" class="wap_diy_sjlist_a">
                                            <div class="wap_diy_sj_n">
                                                <span class="wap_diy_sj_n_s"><i class="wap_diy_sj_n_dw">￥</i>30.00</span>
                                                <span class="wap_diy_sj_n_time rewardjobdate">2017-09-09</span>
                                            </div>
                                            <div class="wap_diy_sjbox">
                                                <div class="wap_diy_sj_jobname">部门经理</div>
                                                <div class="wap_diy_sj_xz rewardjobsalary">3000~5000元</div>
                                                <div class="wap_diy_sj_comname rewardjobcom">蓝思科技信息技术有限公司沭阳分公司</div>
                                                <div class="wap_diy_sj_tj rewardjobreward">
                                                    <span class="wap_diy_sj_tj_s"><i
                                                            class="wap_diy_sj_tj_s_n">￥3</i>投递</span>
                                                    <span class="wap_diy_sj_tj_s"><i
                                                            class="wap_diy_sj_tj_s_n">￥17</i>面试</span>
                                                    <span class="wap_diy_sj_tj_s wap_diy_sj_tj_send"><i
                                                            class="wap_diy_sj_tj_s_n">￥10</i>入职</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="wap_diy_sjlist">
                                        <a href="###" class="wap_diy_sjlist_a">
                                            <div class="wap_diy_sj_n">
                                                <span class="wap_diy_sj_n_s"><i class="wap_diy_sj_n_dw">￥</i>30.00</span>
                                                <span class="wap_diy_sj_n_time rewardjobdate">2017-09-09</span>
                                            </div>
                                            <div class="wap_diy_sjbox">
                                                <div class="wap_diy_sj_jobname">部门经理</div>
                                                <div class="wap_diy_sj_xz rewardjobsalary">3000~5000元</div>
                                                <div class="wap_diy_sj_comname rewardjobcom">蓝思科技信息技术有限公司沭阳分公司</div>
                                                <div class="wap_diy_sj_tj rewardjobreward">
                                                    <span class="wap_diy_sj_tj_s"><i
                                                            class="wap_diy_sj_tj_s_n">￥3</i>投递</span>
                                                    <span class="wap_diy_sj_tj_s"><i
                                                            class="wap_diy_sj_tj_s_n">￥17</i>面试</span>
                                                    <span class="wap_diy_sj_tj_s wap_diy_sj_tj_send"><i
                                                            class="wap_diy_sj_tj_s_n">￥10</i>入职</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="clear" v-if="item == 'rewardjob'"></div>
                                <div class="index_sj_job_list_box" v-if="tplmoblie.rewardjobcss == '1'" id="rewardjobcss1"
                                    style="min-height:70px;">
                                    <div class="index_sj_job_list_box_pd">
                                        <div class="index_rewardjobs_money rewardjobreward">
                                            <span class="index_rewardjobs_money_n">￥200</span>
                                            <div class="index_rewardjobs_list_fs"><span
                                                    class="index_rewardjobs_list_fs_name">投递:￥50</span><span
                                                    class="index_rewardjobs_list_fs_name">面试:￥50</span><span
                                                    class="index_rewardjobs_list_fs_name">入职:￥100</span></div>
                                            <span class="index_rewardjobs_list_ls">领赏</span>
                                        </div>
                                        <div class="index_rewardjobs_name">人事专员</div>
                                        <div class="index_sj_comname rewardjobcom" v-if="tplmoblie.rewardjobcom == '1'">
                                            苏州缘爱商务咨询
                                        </div>
                                        <div class="index_rewardjobs_info rewardjobsalary"
                                            v-if="tplmoblie.rewardjobcom == '1'">
                                            ￥2500-5000
                                            <span class="index_rewardjob_line">|</span>上海 <span
                                                class="index_rewardjob_line">|</span>不限经验
                                            <span class="index_rewardjob_line">|</span>大专学历
                                        </div>
                                        <div class="index_rewardjobs_name_time rewardjobdate"
                                            v-if="tplmoblie.rewardjobdate == '1'"> 一周内更新
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--热门职位-->
                            <div class="wap_mb_show_sj" id="hotjob" v-if="item == 'hotjob' && tplmoblie.hotjob == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_tithot"></i>热门职位</span>
                                            <span class="hotjobmore wap_titmore"
                                                v-if="tplmoblie.hotjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">热门职位</span>
                                            <span class="hotjobmore wap_titmore"
                                                v-if="tplmoblie.hotjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_tithot"></i>热门职位</span>
                                            <span class="hotjobmore wap_titmore"
                                                v-if="tplmoblie.hotjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_hot"></i>
                                                <font color="#d81e06">热门职位</font>
                                            </span>
                                            <span class="hotjobmore wap_titmore"
                                                v-if="tplmoblie.hotjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">热门职位</span>
                                            <span class="hotjobmore wap_titmore"
                                                v-if="tplmoblie.hotjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="index_wap_hotjob">
                                    <div class="clear"></div>
                                    <ul class="index_hotlist">
                                        <li><a href=""><span>
                                                    <font color="#808080">经理</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">会计</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">客服</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">程序员</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">编辑</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">技工</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">销售</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">JAVA</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">美工</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">理财</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">司机</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">施工员</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">设计师</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">CAD</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">店长</font>
                                                </span></a></li>
                                        <li><a href=""><span>
                                                    <font color="#808080">顾问</font>
                                                </span></a></li>
                                    </ul>
                                    <div class="clear"></div>
                                </div>
                            </div>
                            <!--最新职位-->
                            <div class="wap_mb_show_sj" id="newjob" v-if="item == 'newjob' && tplmoblie.newjob == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_titzw"></i>最新职位 </span>
                                            <span class="newjobmore wap_titmore"
                                                v-if="tplmoblie.newjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">最新职位 </span>
                                            <span class="newjobmore wap_titmore"
                                                v-if="tplmoblie.newjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_titzw"></i>最新职位 </span>
                                            <span class="newjobmore wap_titmore"
                                                v-if="tplmoblie.newjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_zw"></i>
                                                <font color="#2383f0">最新职位</font>
                                            </span>
                                            <span class="newjobmore wap_titmore"
                                                v-if="tplmoblie.newjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">最新职位 </span>
                                            <span class="newjobmore wap_titmore"
                                                v-if="tplmoblie.newjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="index_wap_joblist">
                                    <h3> 收银员 </h3>
                                    <div class="index_wap_joblist_comname">
                                        <span class="index_wap_joblist_comname_p" id="newjobcity"
                                            v-if="tplmoblie.newjobcity == '1'"> 山西吕梁</span>
                                        <span class="index_wap_joblist_comname_p" id="newjobcom"
                                            v-if="tplmoblie.newjobcom == '1'">广西聚宏房地产开发有限公司</span>
                                    </div>
                                    <div class="index_wap_joblist_comcity" id="newjobwelfare"
                                        v-if="tplmoblie.newjobwelfare == '1'">
                                        <span class="index_wap_joblist_fl">五险一金</span>
                                        <span class="index_wap_joblist_fl">三险一金</span>
                                        <span class="index_wap_joblist_fl">包吃住</span>
                                    </div>
                                    <div class="index_wap_joblist_xz" id="newjobsalary"
                                        v-if="tplmoblie.newjobsalary == '1'"> 面议
                                    </div>
                                    <div class="index_wap_joblist_time" id="newjobdate" v-if="tplmoblie.newjobdate == '1'">
                                        2017-05-10
                                    </div>
                                </div>
                            </div>
                            <!--名企展示-->
                            <div class="wap_mb_show_sj" id="hotcom" v-if="item == 'hotcom' && tplmoblie.hotcom == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_titmq"></i>名企展示 </span>
                                            <span class="hotcommore wap_titmore"
                                                v-if="tplmoblie.hotcommore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">名企展示</span>
                                            <span class="hotcommore wap_titmore"
                                                v-if="tplmoblie.hotcommore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_titmq"></i>名企展示</span>
                                            <span class="hotcommore wap_titmore"
                                                v-if="tplmoblie.hotcommore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_mq"></i>
                                                <font color="#ff9c00">名企展示</font>
                                            </span>
                                            <span class="hotcommore wap_titmore"
                                                v-if="tplmoblie.hotcommore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">名企展示</span>
                                            <span class="hotcommore wap_titmore"
                                                v-if="tplmoblie.hotcommore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="indexcom_list_box">
                                    <div class="indexcom_list_t_box">
                                        <div class="indexcom_list_logo_box" id="hotcomlogo"
                                            v-if="tplmoblie.hotcomlogo == '1'">
                                            <img :src="'../../images/wap_diy_hotcom.png'">
                                        </div>
                                        <div class="indexcom_list_box_c">
                                            <h3>陶然阁装饰有限公司 </h3>
                                        </div>
                                        <div class="indexcom_list_box_js" v-if="tplmoblie.hotcomhy == '1'">
                                            <span class="indexcom_list_box_js_s indexcom_list_box_js_s_hy" id="hotcomhy"><i
                                                    class="indexcom_list_box_js_icon"></i>广告/市场/媒体/艺术</span>
                                            <span class="indexcom_list_box_js_s" id="hotcomcity"><i
                                                    class="indexcom_list_box_js_icon indexcom_list_box_js_icon_map"></i>江苏-宿迁</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--推荐职位-->
                            <div class="wap_mb_show_sj" id="recjob" v-if="item == 'recjob' && tplmoblie.recjob == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_tittj"></i>推荐职位 </span>
                                            <span class="recjobmore wap_titmore"
                                                v-if="tplmoblie.recjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">推荐职位</span>
                                            <span class="recjobmore wap_titmore"
                                                v-if="tplmoblie.recjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_tittj"></i>推荐职位</span>
                                            <span class="recjobmore wap_titmore"
                                                v-if="tplmoblie.recjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_tj"></i>
                                                <font color="#42a30d">推荐职位</font>
                                            </span>
                                            <span class="recjobmore wap_titmore"
                                                v-if="tplmoblie.recjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">推荐职位</span>
                                            <span class="recjobmore wap_titmore"
                                                v-if="tplmoblie.recjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="index_wap_joblist">
                                    <h3> 收银员 </h3>
                                    <div class="index_wap_joblist_comname" v-if="tplmoblie.recjobcity == '1'">
                                        <span class="index_wap_joblist_comname_p" id="recjobcity"> 山西吕梁</span>
                                        <span class="index_wap_joblist_comname_p" id="recjobcom">广西聚宏房地产开发有限公司1</span>
                                    </div>
                                    <div class="index_wap_joblist_comcity" id="recjobwelfare"
                                        v-if="tplmoblie.recjobwelfare == '1'">
                                        <span class="index_wap_joblist_fl">五险一金</span>
                                        <span class="index_wap_joblist_fl">三险一金</span>
                                        <span class="index_wap_joblist_fl">包吃住</span>
                                    </div>
                                    <div class="index_wap_joblist_xz" id="recjobsalary"
                                        v-if="tplmoblie.recjobsalary == '1'"> 面议
                                    </div>
                                    <div class="index_wap_joblist_time" id="recjobdate" v-if="tplmoblie.recjobdate == '1'">
                                        2017-05-10
                                    </div>
                                </div>
                            </div>
                            <!--紧急职位-->
                            <div class="wap_mb_show_sj" id="urgentjob"
                                v-if="item == 'urgentjob' && tplmoblie.urgentjob == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_titjp"></i>紧急职位</span>
                                            <span class="urgentjobmore wap_titmore"
                                                v-if="tplmoblie.urgentjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">紧急职位</span>
                                            <span class="urgentjobmore wap_titmore"
                                                v-if="tplmoblie.urgentjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_titjp"></i>紧急职位</span>
                                            <span class="urgentjobmore wap_titmore"
                                                v-if="tplmoblie.urgentjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_jp"></i>
                                                <font color="#e61717">紧急职位</font>
                                            </span>
                                            <span class="urgentjobmore wap_titmore"
                                                v-if="tplmoblie.urgentjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">紧急职位</span>
                                            <span class="urgentjobmore wap_titmore"
                                                v-if="tplmoblie.urgentjobmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="index_wap_joblist">
                                    <h3> 收银员 </h3>
                                    <div class="index_wap_joblist_comname">
                                        <span class="index_wap_joblist_comname_p" id="urgentjobcity"
                                            v-if="tplmoblie.urgentjobcity == '1'"> 山西吕梁</span>
                                        <span class="index_wap_joblist_comname_p" id="urgentjobcom"
                                            v-if="tplmoblie.urgentjobcom == '1'">广西聚宏房地产开发有限公司1</span>
                                    </div>
                                    <div class="index_wap_joblist_comcity" id="urgentjobwelfare"
                                        v-if="tplmoblie.urgentjobwelfare == '1'">
                                        <span class="index_wap_joblist_fl">五险一金</span>
                                        <span class="index_wap_joblist_fl">三险一金</span>
                                        <span class="index_wap_joblist_fl">包吃住</span>
                                    </div>
                                    <div class="index_wap_joblist_xz" id="urgentjobsalary"
                                        v-if="tplmoblie.urgentjobsalary == '1'"> 面议
                                    </div>
                                    <div class="index_wap_joblist_time" id="urgentjobdate"
                                        v-if="tplmoblie.urgentjobdate == '1'"> 2017-05-10
                                    </div>
                                </div>
                            </div>
                            <!--最新简历-->
                            <div class="wap_mb_show_sj" id="resume" v-if="item == 'resume' && tplmoblie.resume == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_tituser"></i>最新简历</span>
                                            <span class="resumemore wap_titmore"
                                                v-if="tplmoblie.resumemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">最新简历</span>
                                            <span class="resumemore wap_titmore"
                                                v-if="tplmoblie.resumemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_tituser"></i>最新简历</span>
                                            <span class="resumemore wap_titmore"
                                                v-if="tplmoblie.resumemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_user"></i>
                                                <font color="#0090ff">最新简历</font>
                                            </span>
                                            <span class="resumemore wap_titmore"
                                                v-if="tplmoblie.resumemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">最新简历</span>
                                            <span class="resumemore wap_titmore"
                                                v-if="tplmoblie.resumemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="user_list_cont">
                                    <div class="user_list" id="resumepic1" v-if="tplmoblie.resumepic == '1'">
                                        <h3><span class="c288">陈小云</span> <span class="resume_t_date"> 2017-09-19 </span>
                                        </h3>
                                        <aside class="user_list_p">
                                            <span class="user_city_n" id="resumecity"
                                                v-if="tplmoblie.resumecity == '1'">天津-天津</span>
                                            <i class="user_list_p_line"></i>
                                            <span class="user_list_jy_p" id="resumeexp"
                                                v-if="tplmoblie.resumeexp == '1'">3年以上经验</span>
                                            <i class="user_list_p_line"></i>
                                            <span class="user_list_jy_p" id="resumeedu" v-if="tplmoblie.resumeedu == '1'">
                                                本科学历 </span>
                                        </aside>
                                        <aside class="user_list_pyxjob" id="resumeexpect"
                                            v-if="tplmoblie.resumeexpect == '1'"><em class="user_p_ov"><span
                                                    class="user_list_j">意向职位：</span><span
                                                    class="user_list_yxjob">贸易</span></em>
                                        </aside>
                                    </div>
                                    <div class="user_img_box" id="resumepic2" v-if="tplmoblie.resumepic != 1">
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                        <div class="user_img_list">
                                            <div class="user_img"><img
                                                    :src="'../../images/wap_diy_resume.png'">
                                            </div>
                                            <div class="user_imgname">胡晓辉</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--职场资讯-->
                            <div class="wap_mb_show_sj" id="article" v-if="item == 'article' && tplmoblie.article == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_titnews"></i>职场资讯</span>
                                            <span class="articlemore wap_titmore"
                                                v-if="tplmoblie.articlemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">职场资讯</span>
                                            <span class="articlemore wap_titmore"
                                                v-if="tplmoblie.articlemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_titnews"></i>职场资讯</span>
                                            <span class="articlemore wap_titmore"
                                                v-if="tplmoblie.articlemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_news"></i>
                                                <font color="#d5870d">职场资讯</font>
                                            </span>
                                            <span class="articlemore wap_titmore"
                                                v-if="tplmoblie.articlemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">职场资讯</span>
                                            <span class="articlemore wap_titmore"
                                                v-if="tplmoblie.articlemore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <ul class="news_in_imglist" id="articlecss1" v-if="tplmoblie.articlecss == '1'">
                                    <li>
                                        <img src="../../images/wap_diy_article.png" width="120"
                                            height="80">
                                        <div class="news_in_imglist_p"> 小米的互联网手机神话几乎接近破灭乎接近破灭</div>
                                    </li>
                                    <li>
                                        <img src="../../images/wap_diy_article.png" width="120"
                                            height="80">
                                        <div class="news_in_imglist_p"> 小米的互联网手机神话几乎接近破灭乎接近破灭</div>
                                    </li>
                                    <li>
                                        <img src="../../images/wap_diy_article.png" width="120"
                                            height="80">
                                        <div class="news_in_imglist_p"> 小米的互联网手机神话几乎接近破灭乎接近破灭</div>
                                    </li>
                                    <li>
                                        <img src="../../images/wap_diy_article.png" width="120"
                                            height="80">
                                        <div class="news_in_imglist_p"> 小米的互联网手机神话几乎接近破灭乎接近破灭</div>
                                    </li>
                                </ul>
                                <ul class="news_in_plist" id="articlecss3" v-if="tplmoblie.articlecss == '3'">
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                    <li><span class="news_in_plist_p">小米的互联网手机神话几乎接近破灭</span><span>2017-12-22</span></li>
                                </ul>
                                <div class="news_in_list" id="articlecss2" v-if="tplmoblie.articlecss == '2'">
                                    <div class="news_in_list_box">
                                        <div class="news_in_list_box_left">
                                            <h2>小米的互联网手机神话几乎接近破灭乎接近破灭</h2>
                                            <div class="news_in_list_w65">
                                                <div class="news_in_list_p">
                                                    我因脾气不好和主管吵了一架，经理让我回家休息一段时间，说要我上班时会给我打电话。我离开了半个月
                                                </div>
                                                <div class="news_in_list_date">
                                                    <span class="news_in_eye_n"><i class="news_in_eye"></i>112</span>
                                                    <span class="news_in_eye_n"><i
                                                            class="news_in_date"></i>2016-07-16</span>
                                                </div>
                                            </div>
                                            <div class="news_in_cont_img"><img
                                                    src="../../images/wap_diy_article.png"
                                                    width="120" height="80"></div>
                                        </div>
                                    </div>
                                    <div class="news_in_list_box">
                                        <div class="news_in_list_box_left">
                                            <h2>小米的互联网手机神话几乎接近破灭乎接近破灭</h2>
                                            <div class="news_in_list_w65">
                                                <div class="news_in_list_p">
                                                    我因脾气不好和主管吵了一架，经理让我回家休息一段时间，说要我上班时会给我打电话。我离开了半个月
                                                </div>
                                                <div class="news_in_list_date">
                                                    <span class="news_in_eye_n"><i class="news_in_eye"></i>112</span>
                                                    <span class="news_in_eye_n"><i
                                                            class="news_in_date"></i>2016-07-16</span>
                                                </div>
                                            </div>
                                            <div class="news_in_cont_img"><img
                                                    src="../../images/wap_diy_article.png"
                                                    width="120" height="80"></div>
                                        </div>
                                    </div>
                                    <div class="news_in_list_box">
                                        <div class="news_in_list_box_left">
                                            <h2>小米的互联网手机神话几乎接近破灭乎接近破灭</h2>
                                            <div class="news_in_list_w65">
                                                <div class="news_in_list_p">
                                                    我因脾气不好和主管吵了一架，经理让我回家休息一段时间，说要我上班时会给我打电话。我离开了半个月
                                                </div>
                                                <div class="news_in_list_date">
                                                    <span class="news_in_eye_n"><i class="news_in_eye"></i>112</span>
                                                    <span class="news_in_eye_n"><i
                                                            class="news_in_date"></i>2016-07-16</span>
                                                </div>
                                            </div>
                                            <div class="news_in_cont_img"><img
                                                    src="../../images/wap_diy_article.png"
                                                    width="120" height="80"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--招聘会-->
                            <div class="wap_mb_show_sj" id="zph" v-if="item == 'zph' && tplmoblie.zph == '1'">
                                <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit1">
                                            <span class="wap_tit1_bg"><i class="wap_titzph"></i>招聘会 </span>
                                            <span class="zphmore wap_titmore" v-if="tplmoblie.zphmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit2">
                                            <span class="wap_tit2_bg">招聘会</span>
                                            <span class="zphmore wap_titmore" v-if="tplmoblie.zphmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit3">
                                            <span class="wap_tit3_bg"><i class="wap_titzph"></i>招聘会</span>
                                            <span class="zphmore wap_titmore" v-if="tplmoblie.zphmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit4">
                                            <span class="wap_tit4_bg"><i class="wap_tit4_icon_zph"></i>
                                                <font color="#6bbb16">招聘会</font>
                                            </span>
                                            <span class="zphmore wap_titmore" v-if="tplmoblie.zphmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                    <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                        <div class="wap_tit5">
                                            <span class="wap_tit5_bg">招聘会</span>
                                            <span class="zphmore wap_titmore" v-if="tplmoblie.zphmore == '1'">更多>></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="index_wap_joblist">
                                    <div class="job_list_box job_fair_state_ov" style="padding:15px 10px;">
                                        <div class="job_fair_state ">已开始</div>
                                        <div class="zphname">沭阳2017第一届招聘会</div>
                                        <div class="zphtime"><i class="zphtime_icon"></i>2017-11-29 至 2017-12-31</div>
                                        <div class="zphadd"><i class="zphadd_icon"></i>蓝天市场</div>
                                    </div>
                                </div>
                            </div>
                            <!--职位类别-->
                            <div v-if="item == 'jobclass'">
                                <div class="wap_mb_show_sj jobclassone" v-if="tplmoblie.jobclassone == '1'">
                                    <div class="wap_tit heardercssnone heardercss1" v-if="tplmoblie.heardercss == '1'">
                                        <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                            <div class="wap_tit1">
                                                <span class="wap_tit1_bg"><i class="wap_titlb"></i>职位类别 </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wap_tit heardercssnone heardercss2" v-if="tplmoblie.heardercss == '2'">
                                        <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                            <div class="wap_tit2">
                                                <span class="wap_tit2_bg">职位类别 </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wap_tit heardercssnone heardercss3" v-if="tplmoblie.heardercss == '3'">
                                        <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                            <div class="wap_tit3">
                                                <span class="wap_tit3_bg"><i class="wap_titlb"></i>职位类别 </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wap_tit heardercssnone heardercss4" v-if="tplmoblie.heardercss == '4'">
                                        <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                            <div class="wap_tit4">
                                                <span class="wap_tit4_bg"><i class="wap_tit4_icon_lb"></i>
                                                    <font color="#2383f0">职位类别</font>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wap_tit heardercssnone heardercss5" v-if="tplmoblie.heardercss == '5'">
                                        <div :class="tplmoblie.color ? 'bg' + tplmoblie.color + ' heardercss' : 'heardercss'">
                                            <div class="wap_tit5">
                                                <span class="wap_tit5_bg">职位类别 </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wap_category_list jobclassone" v-if="tplmoblie.jobclassone == '1'">
                                        <div class="wap_category_name">销售/客服/技术支持</div>
                                        <div class="wap_category_p jobclasstwo" v-if="tplmoblie.jobclasstwo == '1'">
                                            <span class="wap_category_a"> 销售人员 </span>
                                            <span class="wap_category_a">销售管理</span>
                                            <span class="wap_category_a">客服/售后 </span>
                                            <span class="wap_category_a">市场/营销</span>
                                            <span class="wap_category_a">公关/媒介</span>
                                        </div>
                                    </div>
                                    <div class="wap_category_list jobclassone" v-if="tplmoblie.jobclassone == '1'">
                                        <div class="wap_category_name">人资/行政/财务/管理</div>
                                        <div class="wap_category_p jobclasstwo" v-if="tplmoblie.jobclasstwo == '1'">
                                            <span class="wap_category_a"> 人力资源 </span>
                                            <span class="wap_category_a"> 行政后勤 </span>
                                            <span class="wap_category_a"> 财务/审计/税务 </span>
                                            <span class="wap_category_a"> 律师/法务 </span>
                                            <span class="wap_category_a"> 高级管理 </span>
                                        </div>
                                    </div>
                                    <div class="wap_category_list jobclassone" v-if="tplmoblie.jobclassone == '1'">
                                        <div class="wap_category_name">生产/工人/质控</div>
                                        <div class="wap_category_p jobclasstwo" v-if="tplmoblie.jobclasstwo == '1'">
                                            <span class="wap_category_a">生产/营运 </span>
                                            <span class="wap_category_a"> 普工/技工 </span>
                                            <span class="wap_category_a"> 质控/安防 </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="diywap_CZ">
                            <a href="javascript:void(0)" @click="code = true" class="diywap_CZbth"
                                style="display:inline-block;width:120px;">手机扫码预览</a>
                            <a href="javascript:void(0)" @click="styleClean" class="diywap_CZbth"
                                style="display:inline-block;width:90px; margin-left:10px; background:#f60">重置样式</a>
                            <div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="tck_setbox">
            <el-dialog title="手机首页预览" :visible.sync="code" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="300px">
                <div>
                    <img :src="preview_img" width="200" height="200">
                </div>
                <div class="code_p">
                    <div class="center">扫码手机预览</div>
                    温馨提示<br />
                    手机扫描二维码，即可在手机进行观看页面效果
                </div>
            </el-dialog>
        </div>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            islook: false,
            adlist: [],
            hd: [],
            indexnav1: {},
            indexnav2: {},
            indexnav3: {},
            nav: [],
            navigation: [],
            searchurl: [],
            sort: [],
            search: '',
            tplmoblie: {
                wapdiy: false,
                site: '1',
                logo: '1',
                heardercss: '1',
                searchshow: '1',
                search: '',
                searchurl: ''
            },
            curr_hdpicindex: 0,
            hdpicslist: [],
            curr_navpicindex: 0,
            navpicslist: [],
            curr_adpicindex: 0,
            adpicslist: [],
            sy_wap_logo: '',
            indexnavtype: '1',
            indexnavpic1: [],
            indexnavpic2: [],
            indexnavpic3: [],
            activeNames: [],
            code: false,
            preview_img: '',
            pic_accept: localStorage.getItem("pic_accept"),
            saveLoading: false
        }
    },
    mounted() {

    },
    computed: {
        jobclass1disabled() {
            return this.tplmoblie.jobclassonenumall
        },
        jobclass2disabled() {
            return this.tplmoblie.jobclasstwonumall
        }
    },
    methods: {
        handleChange(val) {
            console.log(val);
        },
        // 重置样式
        styleClean: function () {
            let that = this;
            delConfirm(this, {}, that.doClean, '确定要清空？');
        },
        doClean: function () {
            var that = this
            httpPost('m=tool&c=diy&a=clean', {}).then(function (res) {
                if (res.data.error == 0) {
                    message.success(res.data.msg);
                    that.getinfo();
                } else {
                    message.error(res.data.msg);
                }
            });
        },
        // 排序 上移、下移
        changesort(index, type) {
            if (index == 0 && type == 1) {
                message.error('已经是第一个了，不能上移');
                return false;
            }
            if (index == this.sort.length - 1 && type == 2) {
                message.error('已经是最后一个了，不能下移');
                return false;
            }
            if (type == 1) {// 上移
                var temp = this.sort[index - 1]
                this.$set(this.sort, index - 1, this.sort[index])
            } else {// 下移
                var temp = this.sort[index + 1]
                this.$set(this.sort, index + 1, this.sort[index])
            }
            this.$set(this.sort, index, temp)
            httpPost('m=tool&c=diy&a=wapsort', { sort: this.sort }).then(function (response) {

            }).catch(function (error) {
                console.log(error);
            })
        },
        // 添加幻灯片
        addhd() {
            this.hd.push({
                name: '',
                pic: '',
                url: '',
                id: ''
            });
        },
        // 删除幻灯片
        delhdpic(index) {
            var that = this
            that.$delete(that.hd, index)
        },
        // 添加广告
        addad() {
            this.adlist.push({
                name: '',
                pic: '',
                url: '',
                id: ''
            });
        },
        // 删除广告
        delad(index) {
            var that = this
            that.$delete(that.adlist, index)
        },
        setadpicindex(index) {
            this.curr_adpicindex = index
        },
        adpicChange(file) {
            // 预览文件处理
            this.adlist[this.curr_adpicindex].pic = URL.createObjectURL(file.raw);
            this.adlist[this.curr_adpicindex].new_pic = 1;
            // 复刻文件信息
            this.adpicslist.push(file.raw);
        },
        // 添加导航
        addnav() {
            this.nav.push({
                sort: '',
                name: '',
                pic: '',
                url: '',
                id: ''
            });
        },
        // 删除导航
        delnav(index) {
            var that = this
            that.$delete(that.nav, index)
        },
        sethdpicindex(index) {
            this.curr_hdpicindex = index
        },
        hdpicChange(file) {
            // 预览文件处理
            this.hd[this.curr_hdpicindex].pic = URL.createObjectURL(file.raw);
            this.hd[this.curr_hdpicindex].new_pic = 1;
            // 复刻文件信息
            this.hdpicslist.push(file.raw);
        },
        setnavpicindex(index) {
            this.curr_navpicindex = index
        },
        navpicChange(file) {
            // 预览文件处理
            this.nav[this.curr_navpicindex].pic = URL.createObjectURL(file.raw);
            this.nav[this.curr_navpicindex].new_pic = 1;
            // 复刻文件信息
            this.navpicslist.push(file.raw);
        },
        // 设置图片导航类型
        changeindexnavtype(type) {
            this.indexnavtype = type
        },
        // 图片导航图标上传
        indexnavpicChange(file) {
            // 预览文件处理
            if (this.indexnavtype == '1') {
                this.indexnav1.pic = URL.createObjectURL(file.raw)
                this.indexnavpic1 = file.raw
            } else if (this.indexnavtype == '2') {
                this.indexnav2.pic = URL.createObjectURL(file.raw)
                this.indexnavpic2 = file.raw
            } else if (this.indexnavtype == '3') {
                this.indexnav3.pic = URL.createObjectURL(file.raw)
                this.indexnavpic3 = file.raw
            }
        },
        setcolor(color) {
            this.tplmoblie.color = color
        },
        getinfo() {
            let that = this;
            httpPost('m=tool&c=diy&a=wap', {}).then(function (response) {
                if (response.data.error == 0) {
                    var res = response.data.data
                    that.adlist = res.adlist
                    that.hd = res.hd
                    that.indexnav1 = res.indexnav1
                    that.indexnav2 = res.indexnav2
                    that.indexnav3 = res.indexnav3
                    that.nav = res.nav
                    that.navigation = res.navigation
                    that.searchurl = res.searchurl
                    that.sort = res.sort
                    that.tplmoblie = res.tplmoblie
                    that.tplmoblie.wapdiy = that.tplmoblie.wapdiy == '1' ? true : false
                    if (that.tplmoblie.jobclassonenumall == '1' && that.tplmoblie.jobclassonenum == '') {
                        that.tplmoblie.jobclassonenumall = true;
                    } else {
                        that.tplmoblie.jobclassonenumall = false;
                    }
                    if (that.tplmoblie.jobclasstwonumall == '1' && that.tplmoblie.jobclasstwonum == '') {
                        that.tplmoblie.jobclasstwonumall = true;
                    } else {
                        that.tplmoblie.jobclasstwonumall = false;
                    }
                    that.sy_wap_logo = res.sy_wap_logo
                    that.preview_img = res.preview_index_url
                    that.islook = true
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        wapdiychange(data) {
            httpPost('m=tool&c=diy&a=wapdiy', { wapdiy: data }).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        async setsave(type) {
            let that = this;
            var params = new FormData();
            params.append('type', type)
            if (type == 'hearder') {// 头部
                params.append('color', this.tplmoblie.color)
                params.append('site', this.tplmoblie.site)
                params.append('logo', this.tplmoblie.logo)
                params.append('heardercss', this.tplmoblie.heardercss)
            } else if (type == 'hd') {// 幻灯片
                params.append('hdshow', this.tplmoblie.hdshow)
                params.append('hd', JSON.stringify(this.hd))
                for (fl in this.hdpicslist) {
                    params.append('hdpic[]', this.hdpicslist[fl])
                }
            } else if (type == 'search') {// 搜索
                params.append('searchshow', this.tplmoblie.searchshow)
                params.append('search', this.tplmoblie.search)
                params.append('searchurl', this.tplmoblie.searchurl)
            } else if (type == 'nav') {// 导航
                for (fl in this.navpicslist) {
                    params.append('navpic[]', this.navpicslist[fl])
                }
                params.append('nav', JSON.stringify(this.nav))
            } else if (type == 'notice') {// 公告
                params.append('notice', this.tplmoblie.notice)
            } else if (type == 'indexnav') {// 图片导航
                params.append('indexnav', this.tplmoblie.indexnav)
                params.append('indexnav1', JSON.stringify(this.indexnav1))
                if (this.indexnavpic1.length != 0) {
                    params.append('indexnavpic1', this.indexnavpic1)
                }
                params.append('indexnav2', JSON.stringify(this.indexnav2))
                if (this.indexnavpic2.length != 0) {
                    params.append('indexnavpic2', this.indexnavpic2)
                }
                params.append('indexnav3', JSON.stringify(this.indexnav3))
                if (this.indexnavpic3.length != 0) {
                    params.append('indexnavpic3', this.indexnavpic3)
                }
            } else if (type == 'reglogin') {// 登陆注册
                params.append('reglogin', this.tplmoblie.reglogin)
                params.append('reglogindesc', this.tplmoblie.reglogindesc)
                params.append('login', this.tplmoblie.login)
                params.append('logincolor', this.tplmoblie.logincolor)
                params.append('reg', this.tplmoblie.reg)
                params.append('regcolor', this.tplmoblie.regcolor)
            } else if (type == 'ad') {// 广告
                params.append('ad', this.tplmoblie.ad)
                params.append('adlist', JSON.stringify(this.adlist))
                for (fl in this.adpicslist) {
                    params.append('adlistpic[]', this.adpicslist[fl])
                }
            } else if (type == 'rewardjob') {// 赏金职位
                params.append('rewardjob', this.tplmoblie.rewardjob)
                params.append('rewardjobcss', this.tplmoblie.rewardjobcss)
                params.append('rewardjobcom', this.tplmoblie.rewardjobcom)
                params.append('rewardjobsalary', this.tplmoblie.rewardjobsalary)
                params.append('rewardjobreward', this.tplmoblie.rewardjobreward)
                params.append('rewardjobdate', this.tplmoblie.rewardjobdate)
                params.append('rewardjobmore', this.tplmoblie.rewardjobmore)
                params.append('rewardjobnum', this.tplmoblie.rewardjobnum)
            } else if (type == 'hotjob') {// 热门职位
                params.append('hotjob', this.tplmoblie.hotjob)
                params.append('hotjobmore', this.tplmoblie.hotjobmore)
                params.append('hotjobnum', this.tplmoblie.hotjobnum)
            } else if (type == 'newjob') {// 最新职位
                params.append('newjob', this.tplmoblie.newjob)
                params.append('newjobcom', this.tplmoblie.newjobcom)
                params.append('newjobsalary', this.tplmoblie.newjobsalary)
                params.append('newjobcity', this.tplmoblie.newjobcity)
                params.append('newjobdate', this.tplmoblie.newjobdate)
                params.append('newjobwelfare', this.tplmoblie.newjobwelfare)
                params.append('newjobmore', this.tplmoblie.newjobmore)
                params.append('newjobnum', this.tplmoblie.newjobnum)
            } else if (type == 'hotcom') {// 名企展示
                params.append('hotcom', this.tplmoblie.hotcom)
                params.append('hotcomlogo', this.tplmoblie.hotcomlogo)
                params.append('hotcomhy', this.tplmoblie.hotcomhy)
                params.append('hotcomcity', this.tplmoblie.hotcomcity)
                params.append('hotcommore', this.tplmoblie.hotcommore)
                params.append('hotcomnum', this.tplmoblie.hotcomnum)
            } else if (type == 'recjob') {// 推荐职位
                params.append('recjob', this.tplmoblie.recjob)
                params.append('recjobcom', this.tplmoblie.recjobcom)
                params.append('recjobsalary', this.tplmoblie.recjobsalary)
                params.append('recjobcity', this.tplmoblie.recjobcity)
                params.append('recjobdate', this.tplmoblie.recjobdate)
                params.append('recjobwelfare', this.tplmoblie.recjobwelfare)
                params.append('recjobmore', this.tplmoblie.recjobmore)
                params.append('recjobnum', this.tplmoblie.recjobnum)
            } else if (type == 'urgentjob') {// 紧急职位
                params.append('urgentjob', this.tplmoblie.urgentjob)
                params.append('urgentjobcom', this.tplmoblie.urgentjobcom)
                params.append('urgentjobsalary', this.tplmoblie.urgentjobsalary)
                params.append('urgentjobcity', this.tplmoblie.urgentjobcity)
                params.append('urgentjobdate', this.tplmoblie.urgentjobdate)
                params.append('urgentjobwelfare', this.tplmoblie.urgentjobwelfare)
                params.append('urgentjobmore', this.tplmoblie.urgentjobmore)
                params.append('urgentjobnum', this.tplmoblie.urgentjobnum)
            } else if (type == 'resume') {// 最新简历
                params.append('resume', this.tplmoblie.resume)
                params.append('resumepic', this.tplmoblie.resumepic)
                params.append('resumeexp', this.tplmoblie.resumeexp)
                params.append('resumecity', this.tplmoblie.resumecity)
                params.append('resumeedu', this.tplmoblie.resumeedu)
                params.append('resumeexpect', this.tplmoblie.resumeexpect)
                params.append('resumemore', this.tplmoblie.resumemore)
                params.append('resumenum', this.tplmoblie.resumenum)
            } else if (type == 'article') {// 职场资讯
                params.append('article', this.tplmoblie.article)
                params.append('articleclass', this.tplmoblie.articleclass)
                params.append('articlecss', this.tplmoblie.articlecss)
                params.append('articlemore', this.tplmoblie.articlemore)
                params.append('articlenum', this.tplmoblie.articlenum)
            } else if (type == 'zph') {// 招聘会
                params.append('zph', this.tplmoblie.zph)
                params.append('zphmore', this.tplmoblie.zphmore)
                params.append('zphnum', this.tplmoblie.zphnum)
            } else if (type == 'jobclass') {// 职位类别
                params.append('jobclassone', this.tplmoblie.jobclassone)
                params.append('jobclassonenum', this.tplmoblie.jobclassonenum)
                params.append('jobclassonenumall', this.tplmoblie.jobclassonenumall)

                params.append('jobclasstwo', this.tplmoblie.jobclasstwo)
                params.append('jobclasstwonum', this.tplmoblie.jobclasstwonum)
                params.append('jobclasstwonumall', this.tplmoblie.jobclasstwonumall)
            }
            that.saveLoading = true;
            httpPost('m=tool&c=diy&a=wapsave', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        this.getinfo();
                    });
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
.mouldappRigh {
    width: 600px;
}

.mouldappLeft {
    width: calc(100% - 600px);
}

.mouldapBanne .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    margin: 0;
    line-height: 90px;
    text-align: center;
}

.mouldapBanne .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
}

.mouldapBanne .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
}

.mouldapBanne .el-carousel__arrow {
    width: 28px;
    height: 28px;
}

.mouldapnavs .el-carousel__arrow {
    width: 28px;
    height: 28px;
    display: none;
}

.mouldapnavs .el-carousel__item {
    background: #fff;
}

.mouldapnavs .el-carousel__indicators {
    background: rgba(0, 0, 0, .2);
    border-radius: 8px;
    bottom: 8px;
}

.mouldapnavs .el-carousel__indicator--horizontal {
    padding: 4px 5px;

}

.mouldapnavs .el-carousel__button {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.wapCollapse .el-collapse-item__wrap {
    background: #f1f1f1;
}

.wapCollapse .el-collapse-item__content {
    padding: 12px;
    overflow: hidden;
    position: relative;
    width: calc(100% - 24px);
}

.wap_header_box {
    width: 100%;
    height: 40px;
}

.wap_header {
    width: 100%;
    height: 40px;
    text-align: center;
    line-height: 40px;
    color: #fff;
    background: #2f90f3;
}

.logo {
    display: inline-block;
    width: 130px;
    position: absolute;
    top: 10px;
    left: 10px;
}

.logo img {
    height: auto;
    width: 200px;
}

.search_con_l {
    color: #fff;
    height: 50px;
    line-height: 50px;
    font-size: 15px;
    position: absolute;
    right: 15px;
    top: 0px;
    z-index: 10300;
}

.city_icon {
    width: 18px;
    height: 18px;
    display: inline-block;
    background: url("../../../images/map.png") no-repeat;
    background-size: 100%;
    vertical-align: middle;
    margin-right: 5px;
}

.bg1 {
    background-color: #63B359;
}

.bg2 {
    background-color: #2C9F67;
}

.bg3 {
    background-color: #0180CC;
}

.bg4 {
    background-color: #5885CF;
}

.bg5 {
    background-color: #9062C0;
}

.bg6 {
    background-color: #D09A45;
}

.bg7 {
    background-color: #E4B138;
}

.bg8 {
    background-color: #EE903C;
}

.bg9 {
    background-color: #DD6549;
}

.bg10 {
    background-color: #CC463D;
}

.index_search_cont {
    border-bottom: 1px solid #f2f2f2;
    padding: 8px 10px;
    background: #fff
}

.index_formFiled {
    border: 1px solid #eee;
    height: 33px;
    overflow: hidden;
    background: #fff;
    position: relative;
    border-radius: 5px;
    padding-left: 10px;
}

.index_input_search {
    width: 100%;
    height: 33px;
    line-height: 33px;
    padding: 0;
    margin: 0;
    border: none;
    font-size: 10px;
    background: #fff
}

.index_input_btn {
    position: absolute;
    right: 0px;
    top: 0px;
    width: 60px;
    height: 40px;
    padding: 0;
    margin: 0;
    border: none;
    color: #fff;
    background: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), to(#fff));
    z-index: 2;
    opacity: 0.1;
    font-size: 14px;
    cursor: pointer;
    border-radius: 0px;
}

.index_input_btn_icon {
    position: absolute;
    right: 10px;
    top: 3px;
    font-size: 20px;
    color: #666;
    z-index: 1
}

.iconfont_index_search {
    width: 25px;
    height: 25px;
    background: url("../../../images/yun_wap_fotnav_user.png") no-repeat;
    background-size: 100%;
    position: absolute;
    right: 10px;
    top: 3px;
    font-size: 20px;
    color: #666;
    z-index: 1
}

.index_search_cont {
    border-bottom: 1px solid #f2f2f2;
    padding: 8px 10px;
    background: #fff
}

.index_formFiled {
    border: 1px solid #eee;
    height: 33px;
    overflow: hidden;
    background: #fff;
    position: relative;
    border-radius: 5px;
    padding-left: 10px;
}

.index_input_search {
    width: 100%;
    height: 33px;
    line-height: 33px;
    padding: 0;
    margin: 0;
    border: none;
    font-size: 10px;
    background: #fff
}

.index_input_btn {
    position: absolute;
    right: 0px;
    top: 0px;
    width: 60px;
    height: 40px;
    padding: 0;
    margin: 0;
    border: none;
    color: #fff;
    background: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), to(#fff));
    z-index: 2;
    opacity: 0.1;
    font-size: 14px;
    cursor: pointer;
    border-radius: 0px;
}

.index_input_btn_icon {
    position: absolute;
    right: 10px;
    top: 3px;
    font-size: 20px;
    color: #666;
    z-index: 1
}

.iconfont_index_search {
    width: 25px;
    height: 25px;
    background: url("../../../images/yun_wap_fotnav_user.png") no-repeat;
    background-size: 100%;
    position: absolute;
    right: 10px;
    top: 3px;
    font-size: 20px;
    color: #666;
    z-index: 1
}

.wap_mb_show_nav {
    background: #fff;
    border-bottom: 1px solid #eee;
    padding-top: 10px;
}

.wap_mb_show_nav_list_p {
    height: 30px;
    line-height: 30px;
    font-size: 10px;
}

.wap_mb_show_nav_list li {
    width: 23%;
    display: inline-block;
    text-align: center
}

.mbt10 {
    margin-bottom: 10px;
}

.index_nav_yd {
    width: 100%;
    background: #fff;
    float: left;
    border-top: 1px solid #eee;
}

.index_nav_yd_left {
    width: 43%;
    height: 130px;
    float: left;
    position: relative
}

.index_nav_yd_left_tit {
    width: 100%;
    font-size: 15px;
    color: #F00;
    padding: 10px 0px 5px 0;
    text-align: center
}

.index_nav_yd_left_icon {
    width: 76px;
    height: 76px;
    position: absolute;
    left: 50%;
    margin-left: -35px;
    bottom: 0px;
}

.index_nav_yd_left_job {
    padding-left: 0px;
    color: #999;
    text-align: center;
    font-size: 12px;
}

.index_nav_yd_right {
    width: 56%;
    float: left;
    border-left: 1px solid #eee;
}

.index_nav_yd_right_t {
    width: 100%;
    height: 65px;
    position: relative
}

.index_nav_yd_right_t_name {
    padding: 10px 0 5px 10px;
    color: #8972f1;
    font-size: 15px;
}

.index_nav_yd_right_t_p {
    color: #999;
    padding: 0px 0px 0px 10px;
    font-size: 12px;
}

.index_nav_yd_right_icon {
    width: 50px;
    height: 50px;
    position: absolute;
    right: 10px;
    top: 15px;
}

.index_nav_yd_right_b {
    width: 100%;
    height: 64px;
    border-top: 1px solid #eee;
    position: relative
}

.index_nav_yd_right_bname {
    padding: 10px 10px 5px 0px;
    color: #298ee0;
    font-size: 15px;
    text-align: right
}

.index_nav_yd_right_bp {
    color: #999;
    padding: 0px 10px 0px 0px;
    text-align: right;
    font-size: 12px;
}

.index_nav_yd_right_bicon {
    width: 50px;
    height: 50px;
    position: absolute;
    left: 5px;
    top: 10px;
}

.clear {
    clear: both
}

.yun_wap_notice {
    width: 100%;
    border-bottom: 1px solid #f2f2f2;
    background: #fff;
    padding: 8px 0;
    position: relative
}

.yun_wap_notice_tit {
    position: absolute;
    left: 10px;
    top: 12px;
    padding-right: 10px;
    border-right: 1px solid #eee
}

.yun_wap_notice_tit_s {
    width: 40px;
    height: 20px;
    display: inline-block;
    background: url("../../../images/yun_wap_gg.png") no-repeat center center;
    background-size: 100%
}

.yun_wap_notice_list {
    padding-left: 70px;
    height: 30px;
    overflow: hidden;
}

.yun_wap_notice_list li {
    width: 100%;
    height: 30px;
    line-height: 30px;
    font-size: 10px;
}

.index_login {
    padding: 5px;
    color: #6b5f5f;
    text-align: center
}

.index_login_p {
    font-size: 10px;
}

.index_logoin_sub {
    width: 100%
}

.index_logoin_sub .index_logoin_bth {
    display: inline-block;
    width: 90px;
    height: 38px;
    line-height: 38px;
    ;
    text-align: center;
    color: #fff;
    background: #1c99ef;
    margin: 0px 10px 0px;
    margin-top: 15px;
    border-radius: 5px;
    font-size: 16px;
}

.index_logoin_sub .index_reg_bth {
    background: #ff6a6a
}

.yun_companyList {
    padding: 1rem;
    background: #ffffff;
    overflow: hidden;
    padding: 0;
    margin-top: 10px;
}

.yun_companyList li {
    margin-top: 1rem;
    text-align: center;
}

.yun_companyList img {
    max-width: 90%;
    height: 50px;
    ;
    vertical-align: middle;
}

.yun_companyList ul {
    overflow: hidden;
}

.yun_companyList ul li {
    width: 32.5%;
    padding-top: 10px;
    padding-bottom: 10px;
    margin-top: 0;
    border-bottom: #f2f2f2 solid 1px;
    border-right: #f2f2f2 solid 1px;
    text-align: center;
    display: inline-table;
    float: left
}

.yun_companyList ul li:nth-child(3n) {
    border-right: none
}

.wap_tit1 {
    width: 100%;
    text-align: center;
    padding-bottom: 15px;
    margin-top: 15px;
}

.wap_tit1_bg {
    min-width: 40px;
    display: inline-block;
    padding: 0px 20px 0px 40px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 30px;
    color: #FFF;
    text-align: center;
    background: -webkit-linear-gradient(left, #01d5ff, #2975ff);
    background: -o-linear-gradient(left, #01d5ff, #2975ff);
    background: linear-gradient(left, #01d5ff, #2975ff);
    position: relative
}

.wap_titsj {
    width: 16px;
    height: 16px;
    position: absolute;
    left: 15px;
    top: 6px;
    background: url("../../../images/diyhb.png") no-repeat;
    display: inline-block
}

.wap_titmore {
    position: absolute;
    right: 10px;
    /*top: 5px;*/
}

.wap_tit2 {
    width: 100%;
    background: #fff;
    margin-top: 15px;
    border-bottom: 1px solid #eee;
    overflow: hidden;
    position: relative
}

.wap_tit2:after {
    width: 54px;
    height: 54px;
    -webkit-transform: rotate(-145deg);
    position: absolute;
    left: 90px;
    top: 8px;
    content: "";
    background: #fff
}

.wap_tit2_bg {
    background: #f60;
    width: 60px;
    padding: 10px 40px 10px 15px;
    color: #fff;
    display: inline-block;
    position: relative;
    font-size: 12px;
}

.wap_tit3 {
    width: 100%;
    background: #58b5e1;
    padding: 10px 0;
    color: #fff;
    margin-top: 15px;
}

.wap_tit3_bg {
    display: inline-block;
    padding-left: 35px;
    position: relative;
    font-size: 12px;
}

.wap_tit4 {
    width: 100%;
    background: #fff;
    border-bottom: 1px solid #eee;
    margin-top: 15px;
}

.wap_tit4_bg {
    display: inline-block;
    color: #666;
    padding: 10px 0 10px 35px;
    font-weight: bold;
    font-size: 12px;
    position: relative
}

.wap_tit5 {
    width: 100%;
    border-bottom: 2px solid #cc0000;
    margin-top: 15px;
}

.wap_tit5_bg {
    display: inline-block;
    padding: 8px 20px;
    background: #cc0000;
    color: #fff;
    border-radius: 3px 3px 0 0;
    font-size: 12px;
}

.wap_tit4_icon_sj {
    width: 18px;
    height: 18px;
    display: inline-block;
    position: absolute;
    left: 10px;
    top: 13px;
    background: url("../../../images/diy_hb.png") no-repeat;
    background-size: 100%
}

.wap_diy_sjcont {
    padding-right: 10px;
    background: #fff
}

.wap_diy_sjlist {
    width: 50%;
    display: inline-block;
    float: left;
}

.wap_diy_sjlist_a {
    display: block;
    margin: 10px 0px 0px 10px;
    background: #fff;
    border-radius: 5px;
}

.wap_diy_sj_n {
    width: 100%;
    height: 35px;
    background: -webkit-linear-gradient(left, #e52600, #c70000);
    background: -o-linear-gradient(left, #e52600, #c70000);
    background: linear-gradient(left, #e52600, #c70000);
    border-radius: 5px 5px 0 0;
    line-height: 35px;
    position: relative
}

.wap_diy_sj_n_s {
    color: #FF0;
    font-size: 18px;
    display: inline-block;
    padding-left: 10px;
}

.wap_diy_sj_n_dw {
    font-size: 12px;
}

.wap_diy_sj_n_time {
    font-size: 12px;
    color: #ee857a;
    position: absolute;
    right: 10px;
    top: 0px;
}

.wap_diy_sjbox {
    padding: 5px 10px;
}

.wap_diy_sj_jobname {
    width: 100%;
    height: 20px;
    line-height: 20px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.wap_diy_sj_xz {
    color: #f00;
}

.wap_diy_sj_comname {
    width: 100%;
    height: 20px;
    line-height: 20px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: 12px;
    color: #999
}

.wap_diy_sj_tj {
    font-size: 12px;
    color: #999
}

.wap_diy_sj_tj_s {
    width: 31.5%;
    display: inline-block;
    padding-top: 20px;
    position: relative;
    text-align: left;
}

.wap_diy_sj_tj_s:after {
    width: 1px;
    height: 20px;
    background: #f2f2f2;
    position: absolute;
    right: 10px;
    top: 10px;
    content: "";
}

.wap_diy_sj_tj_s_n {
    width: 100%;
    position: absolute;
    display: inline-block;
    left: 0px;
    top: 0px;
    color: #f00
}

.wap_diy_sj_tj_send:after {
    background: none
}

.index_sj_job_list_box {
    background: #fff;
    padding: 10px 10px 10px 0px
}

.index_sj_job_list_box_pd {
    padding: 0px 10px 0px 10px;
    position: relative;
}

.index_rewardjobs_money {
    padding-left: 45px;
    position: relative
}

.index_rewardjobs_money:after {
    width: 35px;
    height: 35px;
    background: url("../../../images/yun_jobshow_sj.png") no-repeat;
    background-size: 100%;
    display: inline-block;
    position: absolute;
    left: 0px;
    top: 5px;
    content: '';
}

.index_rewardjobs_money_n {
    font-size: 18px;
    color: #ff4400;
}

.index_rewardjobs_list_fs {
    font-size: 12px;
    padding: 0px 50px 0 0;
    color: #666
}

.index_rewardjobs_list_fs_name {
    display: inline-block;
    margin-right: 10px;
    margin-left: 5px;
}

.index_rewardjobs_list_ls {
    width: 50px;
    height: 25px;
    line-height: 25px;
    background: #ffae00;
    color: #fff;
    display: inline-block;
    position: absolute;
    right: 10px;
    top: 20px;
    font-size: 14px;
    border-radius: 2px;
    text-align: center
}

.index_rewardjobs_name {
    border-top: 1px dashed #eee;
    margin-top: 10px;
    padding-top: 5px;
    font-size: 12px;
}

.index_sj_comname {
    color: #999;
    width: 80%;
    height: 22px;
    line-height: 22px;
    margin: 5px 0;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    font-size: 12px;
}

.index_rewardjobs_info {
    padding: 5px 0 5px 0;
    font-size: 10px;
    color: #666
}

.index_rewardjob_line {
    padding: 0px 10px;
}

.index_rewardjobs_name_time {
    font-size: 12px;
}

.wap_tit1 {
    width: 100%;
    text-align: center;
    padding-bottom: 15px;
    margin-top: 15px;
}

.wap_tit1_bg {
    min-width: 40px;
    display: inline-block;
    padding: 0px 20px 0px 40px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 30px;
    color: #FFF;
    text-align: center;
    background: -webkit-linear-gradient(left, #01d5ff, #2975ff);
    background: -o-linear-gradient(left, #01d5ff, #2975ff);
    background: linear-gradient(left, #01d5ff, #2975ff);
    position: relative
}

.wap_tithot {
    width: 16px;
    height: 16px;
    position: absolute;
    left: 15px;
    top: 6px;
    background: url("../../../images/diyhot2.png") no-repeat;
    display: inline-block
}

.index_wap_hotjob {
    background: #fff;
    padding: 0px 0px;
}

.index_hotlist {
    width: 100%;
}

.index_hotlist li {
    width: 24.6%;
    height: 40px;
    line-height: 40px;
    border-right: 1px solid #f2f2f2;
    border-bottom: 1px solid #f2f2f2;
    display: inline-block;
    float: left
}

.index_hotlist li:nth-child(4n) {
    border-right: none
}

.index_hotlist a {
    display: inline-block;
    width: 100%;
    text-align: center;
}

.index_hotlist a span {
    display: block;
    height: 40px;
    line-height: 40px;
    overflow: hidden;
    color: #999;
    font-size: 10px;
}

.index_wap_joblist {
    border-bottom: 1px solid #f2f2f2;
    padding: 10px 10px 10px 10px;
    position: relative;
    background: #fff
}

.index_wap_joblist_comname {
    color: #666;
    padding: 6px 0;
}

.index_wap_joblist_comname_p {
    display: inline-block;
    max-width: 70%;
    height: 20px;
    line-height: 20px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    position: relative;
    font-size: 10px;
    color: #999
}

.index_wap_joblist_comcity {
    color: #666
}

.index_wap_joblist_fl {
    font-size: 10px;
    background: #edf9ff;
    color: #3d9ccc;
    line-height: 14px;
    padding: 2px 5px;
    border-radius: 3px;
    margin-right: 5px;
    white-space: nowrap;
    margin-bottom: 3px;
    display: inline-block;
}

.index_wap_joblist_xz {
    color: #ff6a6a;
    position: absolute;
    right: 13px;
    top: 10px;
    font-size: 12px;
}

.index_wap_joblist_time {
    font-size: 10px;
    position: absolute;
    right: 10px;
    top: 40px;
    color: #999
}

.index_wap_joblist h3 {
    font-size: 12px;
    font-weight: normal;
    max-width: 70%;
    height: 20px;
    line-height: 20px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.indexcom_list_box {
    width: 100%;
    overflow: hidden;
    background: #fff;
    border-bottom: 1px solid #f2f2f2;
    padding-bottom: 5px;
}

.indexcom_list_t_box {
    padding-right: 65px;
    position: relative
}

.indexcom_list_logo_box {
    width: 50px;
    height: 45px;
    border: 1px solid #f2f2f2;
    position: absolute;
    right: 10px;
    top: 15px;
    text-align: center
}

.indexcom_list_logo_box img {
    max-width: 45px;
    margin-top: 12px;
}

.indexcom_list_box_c {
    padding: 10px 10px 3px 10px;
}

.indexcom_list_box_js {
    padding: 10px 0px 10px 0;
    color: #999
}

.indexcom_list_box_js .indexcom_list_box_js_s {
    margin-left: 10px;
    height: 20px;
    line-height: 20px;
    display: inline-block;
    padding-left: 20px;
    position: relative;
    vertical-align: top;
    font-size: 10px;
}

.indexcom_list_box_js .indexcom_list_box_js_s_hy {
    max-width: 180px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.indexcom_list_box_js_icon {
    width: 16px;
    height: 16px;
    display: inline-block;
    position: absolute;
    left: 0px;
    top: 1px;
}

.indexcom_list_box_js_icon_map {
    background: url("../../../images/zph_icon_hc.png") no-repeat;
    background-size: 100%;
    left: 2px;
}

.indexcom_list_box_c img {
    max-width: 40px;
}

.indexcom_list_box_c h3 {
    font-weight: normal;
    width: 100%;
    height: 20px;
    line-height: 20px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    font-size: 12px;
}

.user_list_cont {
    position: relative;
    background: #fff;
}

.user_list {
    padding: 13px 5px 13px 10px;
    position: relative
}

.user_list h3 {
    font-size: 12px;
    font-weight: normal;
    color: #666;
}

.resume_t_date {
    font-size: 10px;
    position: absolute;
    right: 10px;
    top: 13px;
    color: #999
}

.user_list_p {
    padding: 10px 0 8px 0;
    ;
    width: 95%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #666;
    font-size: 10px;
}

.user_p_ov {
    font-size: 10px;
}

.user_list_p_line {
    font-size: 12px;
    padding: 0px 5px;
    color: #eee
}

.user_list_j {
    display: inline-block;
    color: #666
}

.user_img_box {
    background: #fff;
    padding-top: 15px;
}

.user_img_list {
    width: 24%;
    display: inline-block
}

.user_img {
    width: 100%;
    text-align: center
}

.user_img img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
}

.user_imgname {
    text-align: center;
    padding: 10px 0;
}

.news_in_imglist {
    background: #fff
}

.news_in_imglist li {
    width: 45%;
    display: inline-block;
    margin-left: 10px;
    margin-top: 10px;
    text-align: center
}

.news_in_imglist_p {
    width: 100%;
    height: 30px;
    line-height: 30px;
    overflow: hidden;
    font-size: 10px;
}

.news_in_plist {
    padding: 0px 10px;
    background: #fff
}

.news_in_plist li {
    width: 100%;
    height: 35px;
    line-height: 35px;
    overflow: hidden;
    border-bottom: 1px solid #eee
}

.news_in_plist_p {
    display: inline-block;
    padding-left: 10px;
    background: url("../../../images/arrow.png") no-repeat left 15px;
    font-size: 10px;
}

.news_in_list {
    width: 100%;
    border-bottom: 1px solid #e8e8e8;
    padding: 10px 0;
    background: #fff
}

.news_in_list h2 {
    font-size: 12px;
    font-weight: normal;
    padding-bottom: 5px;
}

.news_in_list_box {
    padding: 0px 10px;
}

.news_in_list_box_left {
    width: 100%;
    display: inline-block;
    vertical-align: top;
}

.news_in_list_w65 {
    width: 65%;
    display: inline-block;
    vertical-align: top;
}

.news_in_list_p {
    font-size: 10px;
    color: #7d7f80;
    line-height: 23px;
}

.news_in_list_date {
    color: #a3adb6;
    padding: 5px 0 0 0;
}

.news_in_eye_n {
    padding-left: 15px;
    position: relative;
    margin-right: 20px;
    font-size: 10px;
}

.news_in_eye {
    width: 12px;
    height: 12px;
    display: inline-block;
    background: url("../../../images/yun_wap_icon_l.png") no-repeat;
    background-size: 100%;
    position: absolute;
    left: 0px;
    top: 3px;
}

.news_in_date {
    width: 12px;
    height: 12px;
    display: inline-block;
    background: url("../../../images/yun_wap_partdate.png") no-repeat;
    background-size: 100%;
    position: absolute;
    left: 0px;
    top: 3px;
}

.news_in_cont_img {
    width: 33%;
    height: 79px;
    overflow: hidden;
    display: inline-block;
}

.job_list_box {
    position: relative;
    margin-top: 10px;
    background: #fff;
    padding: 10px;
}

.job_fair_state_ov {
    overflow: hidden;
}

.job_fair_state {
    width: 60px;
    padding-left: 25px;
    height: 20px;
    -webkit-transform: rotate(40deg);
    background: #2c81d6;
    color: #fff;
    position: absolute;
    right: -24px;
    top: 8px;
    font-size: 12px;
}

.zphname {
    font-size: 12px;
}

.zphtime {
    padding: 5px 0px 5px 20px;
    color: #666;
    position: relative;
    font-size: 10px;
}

.zphtime_icon {
    width: 16px;
    height: 16px;
    display: inline-block;
    position: absolute;
    left: 0px;
    top: 6px;
    background: url("../../../images/yun_wap_partdate.png") no-repeat;
    background-size: 90%;
}

.zphadd {
    color: #666;
    padding-left: 20px;
    position: relative;
    font-size: 10px;
}

.zphadd_icon {
    width: 20px;
    height: 20px;
    display: inline-block;
    position: absolute;
    left: 0px;
    top: 2px;
    background: url("../../../images/zph_icon_hc.png") no-repeat;
    background-size: 90%;
}

.wap_category_list {
    background: #fff;
    padding: 10px;
    margin-top: 10px;
}

.wap_category_name {
    height: 30px;
    line-height: 30px;
    position: relative;
    font-weight: bold;
    font-size: 12px;
}

.wap_category_a {
    display: inline-block;
    margin-right: 10px;
    margin-top: 10px;
    font-size: 10px;
}

.diywap_CZ {
    width: 100%;
    text-align: center;
    margin-top: 10px;
    margin-bottom: 10px;
}

.diywap_CZbth {
    background: #2d8cf0;
    color: #fff;
    height: 30px;
    line-height: 30px;
    width: 190px;
    border-radius: 20px;
    border: none;
    font-size: 12px;
}

.center {
    display: flex;
    justify-content: center;
    align-items: center;
}</style>