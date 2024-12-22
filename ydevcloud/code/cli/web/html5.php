<?php
use function yangzie\__;
use app\project\Page_Model;
include_once 'factory.php';

class web_html5 extends Base_Factory{

    private function exportIcon(){
        $path = YZE_UPLOAD_PATH."project/{$this->project->uuid}/iconfont";
        if (!file_exists($path)){
            return;
        }
        $this->server->push(__('exporting iconfont'));
        $this->addScaffoldFiles($path, "vendor/iconfont/");
    }

    private function generateIndex($fileTree, $files) {
        $project_setting = $this->project->get_setting();
        ob_start();
?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <!-- CSS Files-->
            <link rel="stylesheet" href="<?= "./vendor/{$project_setting['ui']}@{$project_setting['ui_version']}/index.css"?>">
            <title><?= $this->project->name?></title>
        </head>
        <body>
        <ul style="position: fixed; left:0; top:0px;width: 300px;bottom:0px;text-overflow: ellipsis;overflow: auto">
        <?php
        foreach ($fileTree as $moduleName => $functions){
            echo "<li>{$moduleName}<ul>".PHP_EOL;
            foreach ($functions as $functionName => $fileNames){
                echo "<li>{$functionName}".PHP_EOL;
                echo "<ol>".PHP_EOL;
                foreach ($fileNames as $file){
                    list('url'=>$url, 'name'=>$name) = $files[$file];
                    echo "<li><a target='preview' href='{$url}'>{$file} ({$name})</a></li>".PHP_EOL;
                }
                echo "</ol></li>".PHP_EOL;
            }
            echo "</ul></li>".PHP_EOL;
        }
        ?>
        </ul>
        <iframe name="preview" src="about:blank" style="position: fixed; left:300px; top:0px; right:0px;bottom:0px;width: calc(100% - 300px);height: 100%" frameborder="0"></iframe>
        </body>
        </html>
<?php
        return ob_get_clean();
    }

    public function compile(){
        // 编译资源
        $project_setting = $this->project->get_setting();

        // vendor
        $packages = $this->project->get_front_project_packages();
        $packages = array_merge($packages['system'], $packages['user']);
        foreach ($packages as $package){
            if (! file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
            include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";

            $this->server->push(sprintf(__('add %s'), "{$package}"));
            $vendor_path = rtrim(YZE_PUBLIC_HTML . "vendor/{$package}", DS);
            $this->addScaffoldFiles($vendor_path, "vendor/{$package}/");
        }

        $this->zip->addEmptyDir('assets/img');
        $this->zip->addEmptyDir('assets/js');
        $this->zip->addEmptyDir('assets/css');
        $this->zip->addEmptyDir('popup');

        $files = [];
        foreach ($this->project->get_modules() as $module) {
            $this->server->push($this->output(sprintf(__('compile module %s'), $module->name)));
            $moduleName = $module->name.($module->folder ?"($module->folder)": '');
            $fileTree[$moduleName] = [];
            foreach ($module->get_functions() as $function) {
                $this->server->push($this->output(sprintf(__('compile function %s'), $function->name), 'secondary'));
                $fileTree[$moduleName][$function->name] = [];
                foreach ($function->get_pages('') as $index => $page) {
                    $page_file = $page->get_save_path('html');
                    $this->server->push($this->output(sprintf(__('compile %s %s => %s'), $page->page_type, $page->name, $page_file),'success'));
                    if ($page->page_type == 'page') {
                        $pageName = basename($page_file);
                        $files[$pageName] = ['url' => $page_file, 'name' => $page->name];
                        $fileTree[$moduleName][$function->name][] = $pageName;
                    }
                    $this->extractImage(json_decode(html_entity_decode($page->config), true));

                    $ydhttp = new YDHttp();
                    $ydhttp->request_header = ['token:' . $this->token];
                    $htmlContent = $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid . '?code_type=html');
                    $this->zip->addFromString($page_file, $htmlContent);

                    $assetFileName = $page->get_export_file_name('html');
                    foreach (['css'=>"assets/css/{$assetFileName}.css", 'js'=>"assets/js/{$assetFileName}.js"] as $code_type=>$assetFileName) {
                        $this->server->push($this->output(sprintf(__('compile %s %s => %s'), $code_type, $page->name, $assetFileName), 'primary'));

                        $ydhttp = new YDHttp();
                        $ydhttp->request_header = ['token:' . $this->token];
                        $htmlContent = $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid . '?mode=compile&code_type='.$code_type);
                        $this->zip->addFromString($assetFileName, $htmlContent);
                    }
                }
            }
        }

        $this->server->push(sprintf(__('generate popup/component/subpage...')));
        foreach (Page_Model::from()->where('is_deleted = 0 and module_id is null and project_id=:pid')
                     ->select([':pid'=>$this->project->id]) as $page) {
            $page_file = $page->get_save_path('html');
            $this->server->push($this->output(sprintf(__('compile %s %s => %s'), $page->page_type, $page->name, $page_file),'success'));
            $this->extractImage(json_decode(html_entity_decode($page->config), true));

            $ydhttp = new YDHttp();
            $ydhttp->request_header = ['token:' . $this->token];
            $htmlContent = $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid . '?code_type=html');
            $this->zip->addFromString($page_file, $htmlContent);

            $assetFileName = $page->get_export_file_name('html');
            foreach (['css'=>"assets/css/{$assetFileName}.css", 'js'=>"assets/js/{$assetFileName}.js"] as $code_type=>$assetFileName) {
                $this->server->push($this->output(sprintf(__('compile %s %s => %s'), $code_type, $page->name, $assetFileName), 'primary'));

                $ydhttp = new YDHttp();
                $ydhttp->request_header = ['token:' . $this->token];
                $htmlContent = $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid . '?mode=compile&subpage=1&code_type='.$code_type);
                $this->zip->addFromString($assetFileName, $htmlContent);
            }
        }

        // 编译公共selector style
        $this->server->push(__('compile common style file： common.css'));

        $ydhttp = new YDHttp();
        $ydhttp->request_header = ['token:' . $this->token];
        $htmlContent = $ydhttp->get(SITE_URI . 'code/'.$this->project->uuid.'/common?code_type=css');
        $this->zip->addFromString("assets/css/common.css", $htmlContent);

        $this->exportIcon();

        $this->server->push(__('generating index page'));
        $this->zip->addFromString('index.html', $this->generateIndex($fileTree, $files));
    }
}
