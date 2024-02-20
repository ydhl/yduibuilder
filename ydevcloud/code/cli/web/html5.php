<?php
use function yangzie\__;
include_once 'factory.php';

class web_html5 extends Base_Factory{

    private function exportIcon(){
        $path = YZE_UPLOAD_PATH."project/{$this->project->uuid}/iconfont";
        if (!file_exists($path)){
            return;
        }
        $this->server->push($this->frame->fd, __('exporting iconfont'));
        $this->addScaffoldFiles($path, "vendor/iconfont/");
    }

    private function generateIndex($files) {
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
        <ol style="position: fixed; left:0; top:0px;width: 300px;bottom:0px;text-overflow: ellipsis">
        <?php
            foreach ($files as $file=>$info){
                list('url'=>$url, 'name'=>$name) = $info;
                echo "<li><a target='preview' href='{$url}'>{$file} ({$name})</a></li>";
            }
        ?>
        </ol>
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

            $this->server->push($this->frame->fd, sprintf(__('add %s'), "{$package}"));
            $vendor_path = rtrim(YZE_PUBLIC_HTML . "vendor/{$package}", DS);
            $this->addScaffoldFiles($vendor_path, "vendor/{$package}/");
        }

        $this->zip->addEmptyDir('assets/img');
        $this->zip->addEmptyDir('assets/js');
        $this->zip->addEmptyDir('assets/css');

        // 编译ui文件
        $files = [];
        foreach ($this->project->get_modules() as $module) {
            $this->server->push($this->frame->fd, sprintf(__('generate folder for modlue %s'), $module->name));
            foreach ($module->get_pages() as $page) {
                $page_file = $page->get_save_path('html');
                $pageName = basename($page_file);
                $this->server->push($this->frame->fd, sprintf(__('%scompile page %s => %s%s'), "<strong>", $page->name, $page_file, "</strong>"));
                $files[$pageName] = ['url'=>$page_file,'name'=>$page->name];

                $this->extractImage(json_decode(html_entity_decode($page->config), true));

                $ydhttp = new YDHttp();
                $ydhttp->request_header = ['token:' . $this->token];
                $htmlContent = $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid . '?code_type=html');
                $this->zip->addFromString($page_file, $htmlContent);


                $assetFileName = $page->get_export_file_name('html');
                foreach (['css'=>"assets/css/{$assetFileName}.css", 'js'=>"assets/js/{$assetFileName}.js"] as $code_type=>$assetFileName) {
                    $this->server->push($this->frame->fd, sprintf(__('%scompile %s %s => %s%s'), "<strong>", $code_type, $page->name, $assetFileName, "</strong>"));

                    $ydhttp = new YDHttp();
                    $ydhttp->request_header = ['token:' . $this->token];
                    $htmlContent = $ydhttp->get(SITE_URI . 'code/page/' . $page->uuid . '?mode=compile&code_type='.$code_type);
                    $this->zip->addFromString($assetFileName, $htmlContent);

                }
            }
        }

        // 编译公共selector style
        $this->server->push($this->frame->fd, __('compile common style file： common.css'));

        $ydhttp = new YDHttp();
        $ydhttp->request_header = ['token:' . $this->token];
        $htmlContent = $ydhttp->get(SITE_URI . 'code/'.$this->project->uuid.'/common?code_type=css');
        $this->zip->addFromString("assets/css/common.css", $htmlContent);

        $this->exportIcon();

        $this->server->push($this->frame->fd, __('generating index page'));
        $this->zip->addFromString('index.html', $this->generateIndex($files));
    }
}
