<?php
use function yangzie\__;
use \app\project\Page_Model;
include_once 'factory.php';

class web_vue3 extends Base_Factory{
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
    /**
     * [import=>codes]
     * @var array
     */
    private $maints = [];
    private function create_vendor() {
        $this->zip->addEmptyDir("src/assets/vendor");
        $packages = $this->project->get_front_project_packages();
        $packages = array_merge($packages['system'], $packages['user']);
        foreach ($packages as $package){
            if (! file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
            include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
            list ($packageName) = explode('@', $package);
            $requireClass = $packageName . '_install';
            if (!method_exists($requireClass, "installInVue3")){
                    $this->server->push('<span class="text-danger">'.sprintf(__(' can not found installInVue3 method in %s, ignore'), $requireClass).'</span>');
                    continue;
            }
            $install = $requireClass::installInVue3();
            if ($install['devDependencies']) $this->packageJson['devDependencies'] += $install['devDependencies'];
            if ($install['dependencies']) $this->packageJson['dependencies'] += $install['dependencies'];
            if ($install['includeCSSFiles']) $this->includeCSSFiles[$package] = $install['includeCSSFiles'];
            if ($install['globalFiles']) $this->globalFiles[$package] = $install['globalFiles'];
            if ($install['main.ts']) $this->maints[$package] = $install['main.ts'];

            // 未定义任何导出，那么就不导出该包
            if (!@$install['includeCSSFiles'] && !@$install['exportFiles'] && !@$install['globalFiles']) continue;

            $relativePath = "src/assets/vendor/{$package}/";
            $vendor_path = rtrim(YZE_PUBLIC_HTML . "vendor/{$package}", DS).'/';
            if (@$install['includeCSSFiles'] || @$install['exportFiles']) $this->zip->addEmptyDir($relativePath); //需要导出到src/assets

            foreach((array)@$install['includeCSSFiles'] as $file=>$needExport){
                if (!$needExport) continue;
                $entry = $relativePath.ltrim($file, DS);
                $this->server->push(sprintf(__('Add %s'), $entry));
                $this->zip->addFile($vendor_path.$file, $entry);
            }

            foreach((array)@$install['exportFiles'] as $file){
                $entry = $relativePath.ltrim($file, DS);
                $this->server->push(sprintf(__('Add %s'), $entry));
                $this->zip->addFile($vendor_path.$file, $entry);
            }

            //导出到public
            foreach((array)@$install['globalFiles'] as $files){
                foreach ($files as $file){
                    $entry = 'public'.DS.$package.DS.ltrim($file, DS);
                    $this->server->push(sprintf(__('Add %s'), $entry));
                    $this->zip->addFile($vendor_path.$file, $entry);
                }
            }
        }

        $this->server->push(sprintf(__('generating %s'), 'src/assets/index.css'));
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
        $this->zip->addFromString('src/assets/index.css', join("\r\n", $includeCSSFiles));

        $this->server->push(sprintf(__('generating %s'), 'index.html'));
        $indexHtml = file_get_contents(dirname(__FILE__).'/scaffold/vue3/index.html');
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
        $indexHtml = preg_replace("/{{globalFiles}}/", "    ".join("\r\n    ", $globalFiles), $indexHtml);
        $indexHtml = preg_replace("/{{projectName}}/", $this->project->name, $indexHtml);
        $this->zip->addFromString('index.html', $indexHtml, ZipArchive::FL_OVERWRITE);

        $this->server->push(sprintf(__('generating %s'), 'src/main.ts'));
        $maintsContent = file_get_contents(dirname(__FILE__).'/scaffold/vue3/src/main.ts');
        $imports = [];
        foreach ($this->maints as $package => $maints) {
            foreach ($maints as $type => $codes) {
                $imports[] = $codes;
            }

        $maintsContent = preg_replace("/{{import}}/", join("\r\n", $imports), $maintsContent);}
        $this->zip->addFromString('src/main.ts', $maintsContent, ZipArchive::FL_OVERWRITE);

    }
    private function exportIcon(){
        $path = YZE_UPLOAD_PATH."project/{$this->project->uuid}/iconfont";
        if (!file_exists($path)){
            return;
        }
        $this->server->push(__('exporting iconfont'));
        $this->addScaffoldFiles($path, "src/assets/iconfont/");
    }
    private function _generater_routers($routers) {
        $imports = [];
        $routerMap = [];
        foreach ($routers as $file=>$info){
            list('url'=>$url, 'name'=>$name) = $info;
            $basename = ucfirst(strtolower(pathinfo($file, PATHINFO_FILENAME)));
            $routerMap[] = ['path'=>$url, 'name'=>$basename, 'component'=> $basename, 'import'=>"'../{$file}'"];
        }
        ob_start();
?>
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

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
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

export default router
<?php
        return ob_get_clean();
    }
    private function create_views() {
        // 编译ui文件
        $this->zip->addEmptyDir("src/assets/img");
        $router = [];
        $fileTree = [];
        $hasIndexHtml = false;
        foreach ($this->project->get_modules() as $module) {
            $this->server->push($this->output(sprintf(__('compile module %s'), $module->name)));
            $moduleName = $module->name.($module->folder ?"($module->folder)": '');
            $fileTree[$moduleName] = [];
            foreach ($module->get_functions() as $function){
                $this->server->push($this->output(sprintf(__('compile function %s'), $function->name), 'secondary'));
                $fileTree[$moduleName][$function->name] = [];
                foreach ($function->get_pages('') as $index => $page) {
                    if ($page->id == $this->project->home_page_id){
                        $hasIndexHtml = true;
                    }

                    $page_file = $page->get_save_path('vue');
                    $page_url = $page->id == $this->project->home_page_id ? '/' : ($page->url?:"/page{$page->id}");
                    $this->server->push($this->output(sprintf(__('compile page %s => %s(%s)'), $page->name, $page_file, $page_url),'success'));

                    // 弹窗页面、组件页面不输出路由
                    if (!in_array(strtolower($page->page_type), ['popup', 'component'])){
                        $router[$page_file] = ['url'=>$page_url, 'name'=>$page->name];
                        $fileTree[$moduleName][$function->name][] = $page_file;
                    }

                    $ydhttp = new YDHttp();
                    $ydhttp->request_header = ['token:' . $this->token];
                    $this->zip->addFromString('src/'.$page_file, $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid)."\r\n");// 行未加个空行
                    $this->extractImage(json_decode(html_entity_decode($page->config), true), 'src/assets/img');
                }
            }
        }
        foreach (Page_Model::from()->where('is_deleted = 0 and module_id is null and project_id=:pid')
            ->select([':pid'=>$this->project->id]) as $page) {

            $page_file = $page->get_save_path('vue');
            $this->server->push($this->output(sprintf(__('compile component %s => %s'), $page->name, $page_file),'warning'));

            $ydhttp = new YDHttp();
            $ydhttp->request_header = ['token:' . $this->token];
            $this->zip->addFromString('src/'.$page_file, $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid)."\r\n");// 行未加个空行
            $this->extractImage(json_decode(html_entity_decode($page->config), true), 'src/assets/img');
        }

        if (!$hasIndexHtml){
            $this->server->push(__('generating index page'));
            $this->zip->addFromString('src/views/Index.vue', $this->generateIndex($fileTree, $router));
            $router['views/Index.vue'] = ['name'=>__('Index page'),'url'=>"/"];
        }

        $this->server->push(__('generating router'));
        $this->zip->addFromString('src/router/index.ts', $this->_generater_routers($router));

        $this->server->push(sprintf(__('compiled use : %s, you can <ol><li>npm install: install all need node modules</li><li>npm run dev: start the vue serve</li><li>npm run build: build the dist files</li></ol>'), 'Vue 3.5(Typescript, JSX, Vue Router, Pinia, ESLint, Prettier, Vue DevTools 7)'));
    }
    private function generateIndex($fileTree, $files) {
    ob_start();
?>
<template>
<ul>
<?php
foreach ($fileTree as $moduleName => $functions){
    echo "<li>{$moduleName}<ul>".PHP_EOL;
    foreach ($functions as $functionName => $fileNames){
        echo "<li>{$functionName}".PHP_EOL;
        echo "<ol>".PHP_EOL;
        foreach ($fileNames as $file){
            list('url'=>$url, 'name'=>$name) = $files[$file];
            echo "<li><a href='{$url}'>".basename($file)." ({$name})</a></li>".PHP_EOL;
        }
        echo "</ol></li>".PHP_EOL;
    }
    echo "</ul></li>".PHP_EOL;
}
?>
</ul>
</template>
<?php
    return ob_get_clean();
}
    public function compile(){
        $project_setting = $this->project->get_setting();
        $path = dirname(__FILE__).'/scaffold/vue3';
        $this->addScaffoldFiles($path, '.', ['package.json','index.html','main.ts']);
        $this->zip->addEmptyDir("node_modules");
        $this->create_vendor();

        $this->server->push(__('generating package.json'));
        $package = json_decode(file_get_contents($path.'/package.json'), true);
        $package['name'] = 'YDECloudProject'.$this->project->id;
        $package['description'] = $this->project->name." ".$this->project->desc;
        if ($this->packageJson['dependencies']) $package['dependencies'] += $this->packageJson['dependencies'];
        if ($this->packageJson['devDependencies']) $package['devDependencies'] += $this->packageJson['devDependencies'];
        $this->zip->addFromString('package.json', json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->exportIcon();
        $this->create_views();
    }
}
