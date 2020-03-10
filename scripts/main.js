var messages = {
    cn: {
        'Releases': '发布版本',
        'Home': '首页',
        'MySpot is a PHP persistence framework based on PDO with SQLMap support.': 'MySpot 是基于 PDO 的, 支持 SQLMap 的一个 PHP 持久化框架',
        'Features': '特性',
        'A visual utility to generate the code of Data Access Object class file and configuration file, check out': '一个可视化工具, 可以用来生成 DAO 类文件和配置文件, 可以访问: ',
        'A simple syntax support for simple condition sub statement': '一个简单的语法支持: 条件子语句',
        'A simple syntax support for SELECT..IN query which is poor in PDO': '一个简单语法支持: PDO 支持的并不好的 SELECT..IN 语句',
        'Lightweight, all configuration stored in PHP native array': '轻量级, 所有的配置均使用 PHP 数组存储',
        'Install': '安装',
        'A live demo': '演示应用',
        'Document': '文档',
        'Under construction': '建设中',
        'currently available on github': '现在可以在 Github 查看 ',
        'License': '授权许可',
    },
}

var i18n = new VueI18n({
    locale: 'cn',
    fallbackLocale: 'en',
    formatFallbackMessages: true,
    messages: messages
})

Vue.use(VueI18n)

var windowLocation = location.hostname

new Vue({
    el: '#app',
    i18n: i18n,
    mounted: function () {
        this.setLanguage()
    },
    data: function () {
        var cookieLang = Cookies.get('_lang')
        if (!cookieLang) {
            var lang = (window.navigator.userLanguage || window.navigator.language || 'en').toLowerCase()
            if (lang.indexOf('zh') !== -1 && lang.indexOf('cn') !== -1) {
                cookieLang = 'cn'
            }
        }
        return {
            lang: cookieLang,
        }
    },
    methods: {
        setLanguage: function () {
            this.$i18n.locale = this.lang
        },
        handleSelect: function (index) {
            this.$refs.menu.activeIndex = ''
            var domain = windowLocation
            if (domain.indexOf('vifix.cn') !== -1) {
                domain = '.vifix.cn'
            }
            if (index.indexOf('lang') !== -1) {
                switch (index) {
                    case 'lang-cn':
                        this.lang = 'cn'
                        break;
                    case 'lang-en':
                        this.lang = 'en'
                        break;
                }
                Cookies.set('_lang', this.lang, {
                    domain: domain,
                    expires: new Date('01/01/3100')
                })
                this.setLanguage()
            }
        }
    }
})