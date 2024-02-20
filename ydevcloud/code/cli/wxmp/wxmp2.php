<?php
use function yangzie\__;
include_once 'factory.php';

class wxmp_wxmp2 extends Base_Factory{
    /**
     * 同父类的addScaffoldFiles，只是把css后缀修改为wxss
     * @param $path
     * @param $relativePath
     */
    protected function _addScaffoldFiles($path, $relativePath, $exclude_files=['install.php']) {
        $path = rtrim($path, '/').'/';
        $relativePath = rtrim($relativePath, '/').'/';
        $dir = opendir($path);
        if (!$dir) {
            $this->output(sprintf(__('can not read directory: %s'), $path),'error');
            return;
        }
        while (($file = readdir($dir)) !== false) {
            if ($file == "." || $file == "..") continue;
            $file = $path.$file;
            $entry = $relativePath.preg_replace('{'.$path.'}',"", $file);
            $this->output(sprintf(__('Add %s'),$entry));
            if (is_dir($file)){
                $this->zip->addEmptyDir($entry);
                $this->_addScaffoldFiles($file, $relativePath.basename($file)."/");
            }else{
                if (in_array(basename($file), $exclude_files)) continue;
                $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                if ($ext == 'css'){
                    $entry = preg_replace('/\.css$/', '.wxss', $entry);
                }
                $this->zip->addFile($file, $entry);
            }
        }
        closedir($dir);
    }
    private function _rewrite_wxss($path) {
        file_get_contents($path);
    }
    private function _create_vendor() {
        $base_vendor_path = "assets/vendor";
        $this->zip->addEmptyDir($base_vendor_path);
        $packages = $this->project->get_front_project_packages();
        $packages = array_merge($packages['system'], $packages['user']);

        $includeCSSFiles = [];
        foreach ($packages as $package){
            if (! file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
            include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
            list ($packageName) = explode('@', $package);
            $requireClass = $packageName . '_install';
            if (!method_exists($requireClass, "install2wxmp2")){
                $this->output(sprintf(__(' can not found install2wxmp2 method in %s, ignore'), $requireClass), 'error');
                continue;
            }
            $install = $requireClass::install2wxmp2();
            foreach ((array)@$install['export'] as $path => $exports){
                foreach ($exports as $file){
                    $entry = "assets/{$package}/{$path}".basename($file);
                    $this->output($entry);
                    $this->zip->addFile(YZE_PUBLIC_HTML."vendor/{$package}/{$file}", $entry);
                }
            }

            $install['wxss'] = array_map(function($item) use($package){
                return '@import "assets/'.$package.'/'.$item.'";';
            }, $install['wxss']);
            $includeCSSFiles = array_merge($includeCSSFiles, $install['wxss']);
        }

        $this->output(sprintf(__('generating %s'), 'app.wxss'));
        $this->zip->addFromString('app.wxss', join("\r\n", $includeCSSFiles));

    }
    private function _exportIcon(){
        $path = YZE_UPLOAD_PATH."project/{$this->project->uuid}/iconfont";
        if (!file_exists($path)){
            return;
        }
        $this->output(__('exporting iconfont'));
        $this->_addScaffoldFiles($path, "assets/iconfont/");
    }
    private function _create_pages() {
        $pages = [];
        $this->zip->addEmptyDir("pages");

        foreach ($this->project->get_modules() as $module) {
            foreach ($module->get_pages('') as $page) {
                $page_file = $page->get_save_path('wxmp');

                if ($page->id == $this->project->home_page_id){
                    array_push($pages, $page_file);
                }else{
                    $pages[] = $page_file;
                }

                $this->output(sprintf(__('compile page %s to %s'), $page->name, $page_file));

                foreach (['wxml', 'wxss', 'js', 'json'] as $type){
                    $ydhttp = new YDHttp();
                    $ydhttp->request_header = ['token:' . $this->token];
                    $this->zip->addFromString($page_file . '.' . $type, $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid.'?code_type='.$type)."\r\n");// 行未加个空行
                    $this->extractImage(json_decode(html_entity_decode($page->config), true), 'assets/images');
                }
            }
        }
        return $pages;
    }

    public function compile(){
        $path = dirname(__FILE__).'/scaffold/wxmp2';
        $this->_addScaffoldFiles($path, '.', ['project.config.json','app.json','app.wxss']);

        $this->_create_vendor();

        $this->output(sprintf(__('generating %s'), 'project.config.json'));
        $projectConfig = json_decode(file_get_contents($path.'/project.config.json'));
        $projectConfig->projectname = $this->project->name;
        $this->zip->addFromString('project.config.json', json_encode($projectConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->_exportIcon();

        $pages = $this->_create_pages();

        $this->zip->addFile(YZE_PUBLIC_HTML."uibuilder.jpg", "assets/images/uibuilder.jpg");

        $this->output(sprintf(__('generating %s'), 'app.json'));
        $package = json_decode(file_get_contents($path.'/app.json'));
        $package->pages = $pages;
        $this->zip->addFromString('app.json', json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
