var vm = new Vue({
    el: "#app",
    data: {
        title: "语雀同步WordPress",
        currentTab: 'config',// config history
        loading: true,
        submitLoading: false,
        webhook: "",
        form: {
            pluginToken: "",
            accessToken: "",
            author: "",
            parseXml: false,
            localImage: false,

        }

    },

    methods: {
        getUserIdByName: function (nameStr) {
            // 自从Wordpress 2.8开始,后台界面已经自动赋值了一个js全局变量ajaxurl
            // console.log(ajaxurl,AJAX_URL);


            const data = new FormData()
            data.append('action', 'yuque_wordpress_get_user_id')
            data.append('username', nameStr)
            return axios({
                method: 'post',
                url: AJAX_URL,
                headers: {
                    'Content-type': 'application/x-www-form-urlencoded;charset=utf-8'
                },
                data: data,
            })

        },
        save: async function () {
            // 自从Wordpress 2.8开始,后台界面已经自动赋值了一个js全局变量ajaxurl
            // console.log(ajaxurl,AJAX_URL);
            const _this = this;
            this.submitLoading = true;
            const form = JSON.parse(JSON.stringify(this.form));

            const res = await this.getUserIdByName(form.author);
            console.log(res);
            if (res.data.code === 200) {
                form.author = res.data.data;
                const data = new FormData()
                data.append('action', 'yuque_wordpress_set')
                data.append('save', JSON.stringify(form))
                axios({
                    method: 'post',
                    url: AJAX_URL,
                    headers: {
                        'Content-type': 'application/x-www-form-urlencoded;charset=utf-8'
                    },
                    data: data,
                }).then(function (res) {
                    if (res.data.code === 200) {
                        _this.$alert('配置已更新', '操作成功', {
                            confirmButtonText: '确定',
                        });
                    }
                }).finally(function () {
                    _this.submitLoading = false;
                    _this.getConfig();
                });

            } else {
                this.submitLoading = false;
                _this.$alert(res.data.message + ":" + _this.form.author, '操作失败', {
                    confirmButtonText: '确定',
                });
            }


            // axios.post(AJAX_URL,{
            //     action:'yuque_wordpress_set',
            //     contentType: 'application/json; charset=utf-8',
            //
            //     }
            // )
        },
        getConfig: function () {
            this.loading = true;
            const data = new FormData()
            const _this = this;
            data.append('action', 'yuque_wordpress_get_config')
            axios({
                method: 'post',
                url: AJAX_URL,
                headers: {
                    'Content-type': 'application/x-www-form-urlencoded;charset=utf-8'
                },
                data: data,
            }).then(function (res) {
                if (res.data.code === 200) {
                    const data = JSON.parse(JSON.stringify(res.data.data));
                    // console.log(form);
                    delete data.host;
                    _this.form = Object.assign(_this.form, data);
                    _this.webhook = res.data.data.host+'wp-admin/admin-ajax.php?action=yuque_wordpress_webhook&token='+res.data.data.pluginToken;
                }
            }).finally(function () {
                _this.loading = false;
            });
        },
        toggleTab: function (tab) {
            this.currentTab = tab;
            if (this.currentTab === 'config') {
                this.getConfig();
            }

        }

    },
    created: function () {

        this.getConfig();


    },
    mounted: function () {

    }
})
