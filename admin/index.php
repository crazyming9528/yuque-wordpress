<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>语雀同步到WordPress</title>
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>
<div id="app">
    <div class="header">
        <div class="title">{{title}}</div>
    </div>
    <div class="main">
        <div class="hello">欢迎使用语雀同步WordPress插件，仓库地址：<a href="https://github.com/crazyming9528/yuque-wordpress"
                                                       target="_blank">https://github.com/crazyming9528/yuque-wordpress</a>。
            <p>本插件为 <b>语雀->WordPress</b> 单向同步工具，新建和修改文章请在 <a href="https://www.yuque.com/" target="_blank">语雀</a> 进行操作。
            </p>
            <div class="webhook" v-if="webhook">webhook：{{webhook}}</div>
        </div>
        <div class="tab-wrapper">
            <div :class="{tab:true,active:currentTab === 'config'}" @click="toggleTab('config')">
                <i class="icon el-icon-setting"></i>配置
            </div>
            <div :class="{tab:true,active:currentTab === 'history'}" @click="toggleTab('history')"><i
                        class="icon el-icon-time"></i>历史
            </div>
        </div>
        <div v-loading="loading" class="tab-content config" v-if="currentTab === 'config'">
            <div class="form">
                <div class="form-item">
                    <div class="label">插件 Token</div>
                    <div class="content">
                        <el-input
                                placeholder="建议设置token，防止webhook被恶意调用。"
                                style="width: 400px"
                                v-model="form.pluginToken"
                                clearable>
                        </el-input>
                        <span class="tips">当你的webhook地址被泄漏，你可以修改token来解决恶意调用。修改token后请注意webhook地址的变化</span>
                    </div>
                </div>
                <div class="form-item">
                    <div class="label">语雀 Access Token</div>
                    <div class="content">
                        <el-input
                                style="width: 400px"
                                placeholder="语雀access token"
                                v-model="form.accessToken"
                                clearable>
                        </el-input>
                        <span class="tips"><a href="https://www.yuque.com/settings/tokens" target="_blank">前往语雀获取 access token</a></span>
                    </div>
                </div>

                <div class="form-item">
                    <div class="label">文章作者用户名</div>
                    <div class="content">
                        <el-input
                                placeholder="将同步的文章分配给指定用户"
                                v-model="form.author"
                                clearable>
                        </el-input>
                    </div>
                </div>

                <div class="form-item">
                    <div class="label">是否解析xml</div>
                    <div class="content">
                        <el-switch
                                v-model="form.parseXml"
                                active-color="#13ce66"
                                inactive-color="#838383">
                        </el-switch>
                        <span class="tips">解析xml设置关键词、描述、分类、标签等信息</span>
                    </div>
                </div>


                <div class="form-item">
                    <div class="label">开启图片本地化</div>
                    <div class="content">
                        <el-switch
                                v-model="form.localImage"
                                active-color="#13ce66"
                                inactive-color="#838383">
                        </el-switch>
                        <span class="tips">语雀做了图片防盗链，如果不想本地化，<a href="https://developer.mozilla.org/zh-CN/docs/Web/HTML/Element/meta/name" target="_blank">设置meta标签referrer为no-referrer</a> 即可白嫖图片，但百度统计等可能失效。</span>
                    </div>
                </div>


            </div>
            <div class="button-wrapper">
                <el-button type="primary" @click="save" :loading="submitLoading">保存</el-button>
            </div>
        </div>
        <div class="tab-content history" v-if="currentTab === 'history'">历史记录已在数据库中记录，界面开发中</div>
    </div>
    <div class="footer"></div>
</div>
<script>
    var AJAX_URL = '<?php echo AJAX_URL?>';
</script>
<!--<script src="./js/vue.min.js"></script>-->
<!--<script src="./js/index.js"></script>-->
</body>
</html>
