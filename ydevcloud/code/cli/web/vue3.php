<?php
use function yangzie\__;
include_once 'factory.php';

class web_vue3 extends Base_Factory{
    private $index = '<!DOCTYPE html>
<html lang="">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="icon" href="<%= BASE_URL %>favicon.ico">
    <title><%= htmlWebpackPlugin.options.title %></title>
{{globalFiles}}
  </head>
  <body>
    <noscript>
      <strong>We\'re sorry but <%= htmlWebpackPlugin.options.title %> doesn\'t work properly without JavaScript enabled. Please enable it to continue.</strong>
    </noscript>
    <div id="app"></div>
    <!-- built files will be auto injected -->
  </body>
</html>';
    /**
     * @var array[] npm 包含的包，如果vendor支持npm安装，则通过install2xx函数返回对应的安装包和版本
     */
    private $packageJson = ['devDependencies'=>[], 'dependencies'=>[]];
    /**
     * @var array 代码中include的文件， 格式包名=>[包含的文件=>,是否是本库导出文件]
     */
    private $includeCSSFiles = [];
    /**
    * @var array 在index.html 全局包含到文件，格式：包名=>[js=>[],'css'=>]
     */
    private $globalFiles = [];
    private function _create_vendor() {
        $this->zip->addEmptyDir("src/assets/vendor");
        $packages = $this->project->get_front_project_packages();
        $packages = array_merge($packages['system'], $packages['user']);
        foreach ($packages as $package){
            if (! file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
            include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
            list ($packageName) = explode('@', $package);
            $requireClass = $packageName . '_install';
            if (!method_exists($requireClass, "install_vue3")){
                    $this->server->push($this->frame->fd, '<span class="text-danger">'.sprintf(__(' can not found install_vue3 method in %s, ignore'), $requireClass).'</span>');
                    continue;
            }
            $install = $requireClass::install_vue3();
            if ($install['devDependencies']) $this->packageJson['devDependencies'] += $install['devDependencies'];
            if ($install['dependencies']) $this->packageJson['dependencies'] += $install['dependencies'];
            if ($install['includeCSSFiles']) $this->includeCSSFiles[$package] = $install['includeCSSFiles'];
            if ($install['globalFiles']) $this->globalFiles[$package] = $install['globalFiles'];

            // 未定义任何导出，那么就不导出该包
            if (!@$install['includeCSSFiles'] && !@$install['exportFiles'] && !@$install['globalFiles']) continue;

            $relativePath = "src/assets/vendor/{$package}/";
            $vendor_path = rtrim(YZE_PUBLIC_HTML . "vendor/{$package}", DS).'/';
            if (@$install['includeCSSFiles'] || @$install['exportFiles']) $this->zip->addEmptyDir($relativePath); //需要导出到src/assets

            foreach((array)@$install['includeCSSFiles'] as $file=>$needExport){
                if (!$needExport) continue;
                $entry = $relativePath.ltrim($file, DS);
                $this->server->push($this->frame->fd, sprintf(__('Add %s'), $entry));
                $this->zip->addFile($vendor_path.$file, $entry);
            }

            foreach((array)@$install['exportFiles'] as $file){
                $entry = $relativePath.ltrim($file, DS);
                $this->server->push($this->frame->fd, sprintf(__('Add %s'), $entry));
                $this->zip->addFile($vendor_path.$file, $entry);
            }

            //导出到public
            foreach((array)@$install['globalFiles'] as $files){
                foreach ($files as $file){
                    $entry = 'public'.DS.$package.DS.ltrim($file, DS);
                    $this->server->push($this->frame->fd, sprintf(__('Add %s'), $entry));
                    $this->zip->addFile($vendor_path.$file, $entry);
                }
            }
        }

        $this->server->push($this->frame->fd, vsprintf(__('generating %s'), 'src/assets/index.scss'));
        $includeCSSFiles = [];
        foreach ($this->includeCSSFiles as $package => $files) {
            foreach ($files as $file => $isVendorFile){
                if (!$isVendorFile) {
                    $includeCSSFiles[] = '@import "'.$file.'";';
                }else{
                    $includeCSSFiles[] = '@import "./vendor/'.$package.'/'.$file.'";';
                }
            }
        }
        $this->zip->addFromString('src/assets/index.scss', join("\r\n", $includeCSSFiles));

        $this->server->push($this->frame->fd, vsprintf(__('generating %s'), 'public/index.html'));
        $globalFiles = [];
        foreach ($this->globalFiles as $package => $typeFiles) {
            foreach ($typeFiles as $type => $files){
                $type = strtolower($type);
                if ($type == 'js') {
                    foreach ($files as $file){
                        $globalFiles[] = '<script type="text/javascript" src="/'.$package.'/'.$file.'"></script>';
                    }
                }else if($type == 'css'){
                    foreach ($files as $file){
                        $globalFiles[] = '<link rel="stylesheet" type="text/css" href="/'.$package.'/'.$file.'"/>';
                    }
                }
            }
        }
        $path = dirname(__FILE__).'/scaffold/vue3/public/index.html';
        $indexHtml = preg_replace("/{{globalFiles}}/", "    ".join("\r\n    ", $globalFiles), $this->index);
        $this->zip->addFromString('public/index.html', $indexHtml, ZipArchive::OVERWRITE);

    }
    private function exportIcon(){
        $path = YZE_UPLOAD_PATH."project/{$this->project->uuid}/iconfont";
        if (!file_exists($path)){
            return;
        }
        $this->server->push($this->frame->fd, __('exporting iconfont'));
        $this->addScaffoldFiles($path, "src/assets/iconfont/");
    }
    private function _generater_routers($routers) {
        $imports = [];
        $routerMap = [];
        foreach ($routers as $file=>$info){
            list('url'=>$url, 'name'=>$name) = $info;
            $basename = ucfirst(strtolower(pathinfo($file, PATHINFO_FILENAME)));
            $imports[] = "import {$basename} from '../{$file}'";
            $routerMap[] = ['path'=>$url, 'name'=>$basename, 'component'=> $basename, 'import'=>"'../{$file}'"];
        }
        ob_start();
?>
import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
<?php
//echo  implode("\r\n", $imports);
?>

const routes: Array<RouteRecordRaw> = [
<?php
    foreach($routerMap as $index=>$map){
?>
  {
    path: '<?= $map['path']?>',
    name: '<?= $map['name']?>',
    component: () => import(/* webpackChunkName: "<?= $map['name']?>" */ <?= $map['import']?>)
  }<?= $index < count($routerMap)-1 ? ",\r\n" :''?>
<?php
    }
?>
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
<?php
        return ob_get_clean();
    }
    private function _create_views() {
        // 编译ui文件
        $this->zip->addEmptyDir("src/assets/img");
        $router = [];
        $hasIndexHtml = false;
        foreach ($this->project->get_modules() as $module) {
            foreach ($module->get_pages('') as $index => $page) {
                if ($page->id == $this->project->home_page_id){
                    $hasIndexHtml = true;
                }

                $page_file = $page->get_save_path('vue');
                $page_url = $page->id == $this->project->home_page_id ? '/' : ($page->url?:"/page{$page->id}");
                $this->server->push($this->frame->fd, "<strong>".sprintf(__('compile page %s to %s, url: %s'), $page->name, $page_file, $page_url)."</strong>");

                // 弹窗页面不输出路由
                if (strtolower($page->page_type)!='popup'){
                    $router[$page_file] = ['url'=>$page_url, 'name'=>$page->name];
                }

                $ydhttp = new YDHttp();
                $ydhttp->request_header = ['token:' . $this->token];
                $this->zip->addFromString('src/'.$page_file, $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid)."\r\n");// 行未加个空行
                $this->extractImage(json_decode(html_entity_decode($page->config), true), 'src/assets/img');
            }
        }

        if (!$hasIndexHtml){
            $this->server->push($this->frame->fd, __('generating index page'));
            $this->zip->addFromString('src/views/Index.vue', $this->generateIndex($router));
            $router['views/Index.vue'] = ['name'=>__('Index page'),'url'=>"/"];
        }

        $this->server->push($this->frame->fd, __('generating router'));
        $this->zip->addFromString('src/router/index.ts', $this->_generater_routers($router));

        $this->server->push($this->frame->fd, sprintf(__('compiled use : %s, you can <ol><li>npm install: install all need node modules</li><li>npm run serve: start the vue serve</li><li>npm run build: build the dist files</li></ol>'), 'Vue 3, babel, eslint, npm'));
    }
    private function generateIndex($files) {
    $project_setting = $this->project->get_setting();
    $uiFrameworkClass = $project_setting['ui'].'_install';
    ob_start();
?>
<template>
  <ol>
    <?php
        foreach ($files as $file=> $info){
            list('url'=>$url, 'name'=>$name) = $info;
            echo "    <li><a href='{$url}'>{$file} ({$name})</a></li>";
        }
    ?>
    </ol>
</template>
<?php
    return ob_get_clean();
}
    public function compile(){
        $project_setting = $this->project->get_setting();
        $path = dirname(__FILE__).'/scaffold/vue3';
        $this->addScaffoldFiles($path, '.', ['package.json']);
        $this->zip->addEmptyDir("node_modules");
        $this->_create_vendor();

        $this->server->push($this->frame->fd, __('generating package.json'));
        $package = json_decode(file_get_contents($path.'/package.json'), true);
        $package['name'] = 'YDECloudProject'.$this->project->id;
        $package['description'] = $this->project->name." ".$this->project->desc;
        if ($this->packageJson['dependencies']) $package['dependencies'] += $this->packageJson['dependencies'];
        if ($this->packageJson['devDependencies']) $package['devDependencies'] += $this->packageJson['devDependencies'];
        $this->zip->addFromString('package.json', json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ZipArchive::OVERWRITE);
        $this->exportIcon();
        $this->_create_views();
    }
}
