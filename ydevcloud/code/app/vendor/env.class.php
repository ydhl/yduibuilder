<?php
namespace app\vendor;
use function yangzie\__;

/**
 * <code>
 *    前端
 *    /
 *   框架     与       版本
 *  /      |            \
 * ui及版本 依赖的库与版本 语言与版本
 *
 *    后   端
 *    /
 *   框架与版本
 *  /          \
 * 依赖的库与版本 语言与版本
 * </code>
 */
class Env {
    /**
     * UI库
     */
    const UI = 'ui';
    const UI_VERSION = 'ui_version';
    /**
     * 前端环境
     */
    const FRONTEND = 'frontend';
    /**
     * 前端框架
     */
    const FRONTEND_FRAMEWORK = 'frontend_framework';
    const FRONTEND_FRAMEWORK_VERSION = 'frontend_framework_version';
    const FRONTEND_LANGUAGE = 'frontend_language';
    const FRONTEND_LANGUAGE_VERSION = 'frontend_language_version';

    /**
     * 后端环境
     */
    const BACKEND = 'backend';
    /**
     * 后端框架
     */
    const FRAMEWORK = 'framework';
    const FRAMEWORK_VERSION = 'framework_version';
    const BACKEND_LANGUAGE = 'backend_language';
    const BACKEND_LANGUAGE_VERSION = 'backend_language_version';
    /**
     * 包含的其他库
     */
    const REQUIRE = 'require';

    /**
     * 返回指定的package所依赖的其他所有的package
     *
     * @param $package 格式是包名@版本号
     * @return array 返回所有依赖的包（包含自己, 在最后）
     */
    public static function pickDependencyPackage($package){
        $return = [];
        list($packageName, $packageVersion) = explode('@', trim($package));
        if (!$packageName || !$packageVersion) return [$package];
        $allPackages = self::package();
        foreach (@(array)$allPackages[$packageName]['version'][$packageVersion]['require'] as $requirePackage){
            $dependPackage = self::pickDependencyPackage($requirePackage);
            if ($dependPackage) $return = array_merge($return, $dependPackage);
        }
        $return[] = $package;
        return array_unique($return);
    }

    /**
     * 所有支持的库
     * [
     *  库id=>
     *   [
     *      name=>名称,
     *      desc=>描述,
     *      version=>[版本=>[依赖的库及器版本，如jquery@3.5.1]],
     *      type=>库类型，目前有framework,ui,language,lib
     *      ui=>[framework支持的ui]
     *      codeType=>[] framework框架一个功能单元的代码类型及其对应的语言，比如wxmp一个功能单元有wxml:xml wcss:css json:json js:js文件
     *      language=>[framework支持的语言]
     *   ]
     * ]
     * @return array[]
     */
    public static function package() {
        // TODO 需要改成从数据库读取
        return [
            // UI
            'bootstrap'=>[
                'name'=>'Bootstrap',
                'type'=>'ui',
                'desc'=>__('Quickly design and customize responsive mobile-first sites'),
                'version'=>[
                    '4.6.0'=>[]
                ]
            ],
            'layui'=>[
                'name'=>'LayUI',
                'type'=>'ui',
                'desc'=>__('由职业前端倾情打造，面向全层次的前后端开发者，易上手开箱即用的 Web UI 组件库, https://www.layuiweb.com/index.htm'),
                'version'=>[
                    '2.9.6'=>[]
                ]
            ],
            'weui'=>[
                'name'=>__('WEUI(微信官方UI库)'),
                'desc'=>'',
                'type'=>'ui',
                'version'=>[
                    '2.5.1'=>['require'=>['zepto@1.2.0']]
                ]
            ],
            'vant' => [
                'name'=>__('Vant'),
                'desc'=>'',
                'type'=>'ui',
                'version'=>['3.4.6'=>[] ]
            ],
            'android-ui'=>[
                'name'=>__('Android Native UI Theme'),
                'desc'=>'',
                'type'=>'ui',
                'version'=>['update'=>[]]
            ],
            'ios-ui'=>[
                'name'=>__('iOS Native UI Theme'),
                'desc'=>'',
                'type'=>'ui',
                'version'=>['update'=>[]]
            ],
            // LIB
            'jquery'=>[
                'name'=>'jQuery',
                'type'=>'lib',
                'desc'=>__('jQuery is a fast, small, and feature-rich JavaScript library'),
                'version'=>[
                    '3.5.1'=>[]
                ]
            ],
            'axios'=>[
                'name'=>'axios',
                'type'=>'lib',
                'desc'=>__('Promise based HTTP client for the browser and node.js'),
                'version'=>[
                    '1.1.2'=>[]
                ]
            ],
            'zepto'=>[
                'name'=>'zepto',
                'type'=>'lib',
                'desc'=>__('Zepto is a minimalist JavaScript library for modern browsers with a largely jQuery-compatible API, https://zeptojs.com/'),
                'version'=>[
                    '1.2.0'=>[]
                ]
            ],
            'alpinejs'=>[
                'name'=>'alpinejs',
                'type'=>'lib',
                'desc'=>__('Alpine is a rugged, minimal tool for composing behavior directly in your markup. Think of it like jQuery for the modern web. Plop in a script tag and get going., https://alpinejs.dev/'),
                'version'=>[
                    '3.x.x'=>[]
                ]
            ],
            'petitevue' => [
                'name'=>__('petite-vue'),
                'desc'=>'https://github.com/vuejs/petite-vue',
                'type'=>'lib',
                'version'=>['0.4.0'=>[] ]
            ],
            //Language
            'java' => [
                'name'=>'Java',
                'desc'=>'',
                'type'=>'language',
                'version'=>[ '7'=>[] ]
            ],
            'objective-c' => [
                'name'=>'Objective-c',
                'desc'=>'',
                'type'=>'language',
                'version'=>[ '2'=>[] ]
            ],
            'javascript' => [
                'name'=>'Javascript',
                'desc'=>'',
                'type'=>'language',
                'version'=>[ 'ECMAScript 5'=>[] ]
            ],
            'typescript' => [
                'name'=>'Typescript',
                'type'=>'language',
                'desc'=>'',
                'version'=>[ '4.0'=>[] ]
            ],
            'php' => [
                'name'=>__('PHP Language'),
                'desc'=>'',
                'type'=>'language',
                'version'=>[ '7.0.0'=>[] ]
            ],
            //Framework
            'yangzie' => [
                'name'=>__('Yangzie Framework By YDHL Team'),
                'desc'=>'',
                'type'=>'framework',
                'language'=>['php'],
                'version'=>[ '2.0.0'=>[] ]
            ],
            'vue' => [
                'name'=>__('VUE'),
                'desc'=>'',
                'type'=>'framework',
                'rewrite'=>true,
                'ui'=>['bootstrap','vant'],
                'language'=>['typescript'],
                'version'=>['3.x'=>[
                    'require'=>['axios@1.1.2', 'ydecloud@0.0.1']
                ] ]
            ],
            'html' => [
                'name'=>__('HTML'),
                'desc'=>'',
                'type'=>'framework',
                'rewrite'=>false,
                'ui'=>['bootstrap','layui'],
                'codeType'=>['html'=>'html','css'=>'css','js'=>'javascript'],
                'language'=>['javascript'],
                'version'=>[ '5.0'=>[
                    // 固定使用 jquery操作dom，alpinejs做界面操作逻辑，axios做http请求，ydecloud是前端数据处理封装
                    'require'=>['jquery@3.5.1', 'alpinejs@3.x.x','axios@1.1.2','ydecloud@0.0.1']
                ] ]
            ],
            'wxmp' => [
                'name'=>__('微信小程序'),
                'desc'=>'',
                'rewrite'=>true,
                'type'=>'framework',
                'ui'=>['weui','vant'],
                'codeType'=>['wxml'=>'xml','wxss'=>'css','json'=>'json','js'=>'javascript'],
                'language'=>['javascript'],
                'version'=>[ '2.x'=>[] ]
            ],
            'android' => [
                'name'=>__('Android Native'),
                'desc'=>'',
                'type'=>'framework',
                'ui'=>['android-ui'],
                'language'=>['java'],
                'version'=>[23=>[],30=>[]]
            ],
            'ios' => [
                'name'=>__('iOS Native'),
                'desc'=>'',
                'type'=>'framework',
                'ui'=>['ios-ui'],
                'language'=>['objective-c'],
                'version'=>[14.1=>[]]
            ]
        ];
    }

    /**
     * 返回frontend及其下面的ui库及框架
     * @return array [前端环境=>[name, framework=>[支持的框架]]]
     */
    public static function getFrontendAndFramework() {
        return [
            'web' => [
                'name'=>__('Web Application'),
                'endKind'=>['mobile','pc'],
                'framework'=>['html','vue']
            ],
            // wxmp， android， ios前端环境本身也是框架
            'wxmp' => [
                'name'=>__('WeiXin Mini Program'),
                'endKind'=>['mobile'],
                'framework'=>['wxmp'],
            ],
//            'android' => [
//                'name'=>__('Android App'),
//                'endKind'=>['mobile'],
//                'framework'=>['android'],
//            ],
//            'ios' => [
//                'name'=>__('iOS App'),
//                'endKind'=>['mobile'],
//                'framework'=>['ios'],
//            ],
        ];
    }

    /**
     * 返回backend及其下面的框架
     * @return array  [前端环境=>[name, framework=>[支持的框架]]]
     */
    public static function getBackendAndFramework() {
        return [
            'php' => [
                'name'=>'PHP',
                'framework'=>['yangzie']
            ]
        ];
    }


    /**
     * 项目所有的设置选项名称列表
     *
     * @return string[]
     */
    public static function getOptionKeys(){
        return [
            self::UI,
            self::UI_VERSION,
            self::FRONTEND,
            self::BACKEND,
            self::FRAMEWORK,
            self::FRAMEWORK_VERSION,
            self::FRONTEND_FRAMEWORK,
            self::FRONTEND_FRAMEWORK_VERSION,
            self::FRONTEND_LANGUAGE,
            self::FRONTEND_LANGUAGE_VERSION,
            self::BACKEND_LANGUAGE,
            self::BACKEND_LANGUAGE_VERSION,
            self::REQUIRE
        ];
    }
}
