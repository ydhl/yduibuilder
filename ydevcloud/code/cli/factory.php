<?php
use function yangzie\__;
abstract class Base_Factory{
    /**
     * @var ZipArchive
     */
    protected $zip;
    protected $server;
    protected $frame;
    protected $project;
    protected $token;
    public function __construct ($server, $frame, $project, $token) {
        $this->server = $server;
        $this->frame = $frame;
        $this->project = $project;
        $this->token = $token;
    }
    /**
     * @return Base_Factory
     */
    public static function get_factory($server, $frame, $project, $token) {
        $project_setting = $project->get_setting();
        $frontend_framework = $project_setting['frontend_framework'];
        $frontend_framework_version = substr($project_setting['frontend_framework_version'], 0, strpos($project_setting['frontend_framework_version'], '.'));
        $target = $frontend_framework.$frontend_framework_version;
        include_once $project_setting['frontend'].'/'.$target.'.php';
        $class = $project_setting['frontend'].'_'.$target;
        if (!class_exists($class)) {
            throw new \yangzie\YZE_FatalException(sprintf(__('factory %s not found'), $class));
        }
        $factory = new $class($server, $frame, $project, $token);
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
            $this->server->push($this->frame->fd, sprintf(__('can not read directory: %s'), $path));
            return;
        }
        while (($file = readdir($dir)) !== false) {
            if ($file == "." || $file == "..") continue;
            $file = $path.$file;
            $entry = $relativePath.preg_replace('{'.$path.'}',"", $file);
            $this->server->push($this->frame->fd, sprintf(__('Add %s'),$entry));
            if (is_dir($file)){
                $this->zip->addEmptyDir($entry);
                $this->addScaffoldFiles($file, $relativePath.basename($file)."/");
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
                $this->server->push($this->frame->fd, sprintf(__('%sdownload image: %s => %s%s'), "<div class='text-info'>",$src, $dist_image,'</div>'));
                $this->zip->addFromString($dist_image, file_get_contents($src));
            }
        }

        foreach ((array)@$uiconfig['meta']['style']['background-image'] as $img){
            if($img['type']!='image' || !$img['url']) continue;

            $name = pathinfo(urldecode($img['url']), PATHINFO_BASENAME);
            $dist_image = rtrim($imageFolder, '/')."/".$name;

            $this->server->push($this->frame->fd, sprintf(__('%sdownload image: %s => %s%s'), "<div class='text-info'>",$img['url'], $dist_image,'</div>'));
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
        if ($this->zip->open($fullpath, \ZipArchive::CREATE) !== TRUE) {
            $this->server->push($this->frame->fd, __('can not create zip file'));
            return;
        }
        $this->server->push($this->frame->fd, __("\n\nstart compile..."));

        $this->compile();
        $this->zip->close();
        \yangzie\yze_move_file($fullpath, YZE_UPLOAD_PATH);
        return UPLOAD_SITE_URI.urlencode($zipFileName);
    }
    public abstract function compile();

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
        }
        $this->server->push($this->frame->fd, $wrap);
    }

}
