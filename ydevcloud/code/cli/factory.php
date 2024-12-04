<?php
use function yangzie\__;
abstract class Base_Factory{
    /**
     * @var ZipArchive
     */
    protected $zip;
    protected $server;
    /**
     * @var \app\project\Project_Model
     */
    protected $project;
    protected $token;
    public function __construct ($server, $project, $token) {
        $this->server = $server;
        $this->project = $project;
        $this->token = $token;
    }
    /**
     * @return Base_Factory
     */
    public static function get_factory($server, $project, $token) {
        $project_setting = $project->get_setting();
        $frontend_framework = $project_setting['frontend_framework'];
        $frontend_framework_version = substr($project_setting['frontend_framework_version'], 0, strpos($project_setting['frontend_framework_version'], '.'));
        $target = $frontend_framework.$frontend_framework_version;
        include_once $project_setting['frontend'].'/'.$target.'.php';
        $class = $project_setting['frontend'].'_'.$target;
        if (!class_exists($class)) {
            throw new \yangzie\YZE_FatalException(sprintf(__('factory %s not found'), $class));
        }
        $factory = new $class($server, $project, $token);
        return $factory;
    }
    /**
     * @param $path 绝对路径
     * @param $relativePath 放入zip但相对路径
     */
    protected function addScaffoldFiles($path, $relativePath, $exclude_files=['install.php']) {
        $path = rtrim($path, '/').'/';
        $relativePath = rtrim($relativePath, '/').'/';
        $dir = opendir($path);
        if (!$dir) {
            $this->server->push(sprintf(__('can not read directory: %s'), $path));
            return;
        }
        while (($file = readdir($dir)) !== false) {
            if ($file == "." || $file == "..") continue;
            $file = $path.$file;
            $entry = $relativePath.preg_replace('{'.$path.'}',"", $file);
            $this->server->push(sprintf(__('Add %s'),$entry));
            if (is_dir($file)){
                $this->zip->addEmptyDir($entry);
                $this->addScaffoldFiles($file, $relativePath.basename($file)."/", $exclude_files);
            }else{
                if (in_array(basename($file), $exclude_files)) continue;
                $this->zip->addFile($file, $entry);
            }
        }
        closedir($dir);
    }

    /**
     * 提取页面中的图片资源, 图片和背景设置
     * @param array uiconfig
     */
    protected function extractImage($uiconfig, $imageFolder='assets/img'){
        if (strtolower($uiconfig['type']) == 'image'){
            $src = $uiconfig['meta']['value'] ?: rtrim(SITE_URI, '/').'/uibuilder.jpg';
            if ($src){
                $name = pathinfo(urldecode($src), PATHINFO_BASENAME);
                $dist_image = rtrim($imageFolder, '/')."/".$name;
                $this->server->push($this->output(sprintf(__('download image: %s => %s'), $src, $dist_image), 'info'));
                $this->zip->addFromString($dist_image, file_get_contents($src));
            }
        }

        foreach ((array)@$uiconfig['meta']['style']['background-image'] as $img){
            if($img['type']!='image' || !$img['url']) continue;

            $name = pathinfo(urldecode($img['url']), PATHINFO_BASENAME);
            $dist_image = rtrim($imageFolder, '/')."/".$name;

            $this->server->push($this->output(sprintf(__('download image: %s => %s'), $img['url'], $dist_image), 'info'));
            $this->zip->addFromString($dist_image, file_get_contents($img['url']));
        }
        foreach ((array)@$uiconfig['items'] as $item){
            $this->extractImage($item, $imageFolder);
        }
    }
    /**
     * 编译代码并得到zip路径
     * @return mixed
     */
    public function build(){
        // 压缩包
        $zipFileName = $this->project->id . "-" . $this->project->name . ".zip";
        $this->zip = new \ZipArchive();
        $fullpath = dirname(__FILE__) . '/' . $zipFileName;
        if ($this->zip->open($fullpath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            $this->server->push(__('can not create zip file'));
            return;
        }
        $this->server->push(__("\n\nstart compile..."));

        $this->compile();
        $this->zip->close();
        return upload2oss($fullpath, "build/{$this->project->uuid}/{$zipFileName}");
    }
    public abstract function compile();

    /**
     * @param $string
     * @param $type error|info|success|secondary|warning
     * @return void
     */
    protected function output($string, $type='normal'){
        $wrap = $string;
        switch ($type){
            case 'error':
                $wrap = "<div class='text-danger'>{$string}</div>";
                break;
            case 'info':
                $wrap = "<div class='text-info'>{$string}</div>";
                break;
            case 'success':
                $wrap = "<div class='text-success'>{$string}</div>";
                break;
            case 'secondary':
                $wrap = "<div class='text-secondary'>{$string}</div>";
                break;
            case 'warn':
                $wrap = "<div class='text-warning'>{$string}</div>";
                break;
        }
        $this->server->push($wrap);
    }

}
