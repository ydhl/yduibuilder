<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Alpinejs_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;
use app\project\Page_Bind_Api_Model;
use app\vendor\Mime;
use yangzie\YZE_View_Component;

class File_View extends Preview_View implements Valuable_View {
    use Bootstrap_Popup,Html_Code_Helper, Mime, Alpine{
        Alpine::eventName as alpineEventName;
        Alpine::build_code as alpineBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent(0);
        $myid = $this->myid();
        $accept = $this->data['meta']['custom']['accept'];
        $inputDataName = $this->get_input_data_name();

        echo "{$space}";
        echo "<div";
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<input type="file" class="d-block"';
        echo $this->output_form_attrs();
        echo $this->wrap_output('multiple', null, $this->data['meta']['custom']['multiple']?:null);

        if ($accept){
            echo $this->wrap_output('accept', join(',',array_map(function($item){
                return $this->get_mime_by_ext($item);
            }, explode(',', $accept))));
        }

        echo PHP_EOL.$this->indent(1);
        echo $this->wrap_output('x-init', "\$watch(alpinejs_get_input_data_name(\$el, '{$inputDataName}'), (value, oldValue) => {$myid}_handler(value))");
        echo ">".PHP_EOL;
        echo "{$space}";
        echo "</div>".PHP_EOL;
    }
    protected function default_value()
    {
        return $this->data['meta']['custom']['multiple'] ? [] : null;
    }

    protected function eventName($eventName)
    {
        $eventName = $this->alpineEventName($eventName);
        if (in_array(strtolower($eventName),['onfilechange','onbeforeupload','onuploadprogress','onfileuploaded', 'onuploadcomplete'])) return '';
        return $eventName;
    }

    protected function css_map() {
        $css = parent::css_map();
        $styleMap = parent::style_map();
        $css[] = 'd-flex align-items-center overflow-hidden';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal'){
            $css[] = ' form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = ' readonly';
        }
        if (@$styleMap['color']){
            unset($css['foregroundTheme']);
        }
        if (@$styleMap['background-color']){
            unset($css['backgroundTheme']);
        }
        return $css;
    }
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();
        $myid = $this->myid();
        if ($this->data['meta']['custom']['isAutoUpload']){
            $autoupload = $this->build_autoupload_code();
        }
        if ($this->data['meta']['custom']['maxFileSize']){
            $validate_size = $this->build_filesize_validate_code();
        }
        if ($this->data['meta']['custom']['accept']){
            $validate_ext = $this->build_ext_validate_code();
        }

        $onFileChange = $this->has_event('onFileChange') ? "page.{$myid}_onFileChange(files)" : '';
        $onUploadComplete = $this->has_event('onUploadComplete') ? "page.{$myid}_onUploadComplete()" : '';

        $codes = [];
        $codes[] = "{$myid}_handler(files){";
        $codes[] = $this->indent(1, true)."const page = this;";
        if ($onFileChange) $codes[] = $this->indent(1, true).$onFileChange;
        $codes[] = $this->indent(1, true)."if (!files) return;";
        $codes[] = $this->indent(1, true)."const promises = []";
        $codes[] = $this->indent(1, true)."for(const index in files){";
        $codes[] = $this->indent(2, true)."const file = files[index]";
        if ($validate_ext) $codes = array_merge($codes, $this->build->indent_code(2, $validate_ext));
        if ($validate_size) $codes = array_merge($codes, $this->build->indent_code(2, $validate_size));
        if ($autoupload) $codes = array_merge($codes, $this->build->indent_code(2, $autoupload));
        $codes[] = $this->indent(1, true)."}";

        if ($this->data['meta']['custom']['isAutoUpload']){
            $codes[] = "";
            $codes[] = $this->indent(1, true)."Promise.all(promises).then((value)=>{";

            if ($onUploadComplete){
                $codes[] = $this->indent(2, true)."if (files?.length > 0){";
                $codes[] = $this->indent(3, true).$onUploadComplete;
                $codes[] = $this->indent(2, true).'}';
            }
            $codes[] = $this->indent(1, true)."});";
        }
        $codes[] = "},";

        $fragment->add_code(Alpinejs_Code_Fragment::SECTION_EVENT, $codes);
        return $fragment;
    }

    private function build_autoupload_code(){
        $bindApiUuid = $this->data['meta']['custom']['apiUuid'];
        $bind_api = $bindApiUuid ? Page_Bind_Api_Model::find_by_uuid($bindApiUuid) : null;
        if (!$bind_api) return '';
        $myid = $this->myid();

        $codeLines = ['promises.push(new Promise((resolve, reject) => {'];
        if ($this->has_event('onBeforeUpload')){
            $codeLines[] = $this->indent(1, true)."page.{$myid}_onBeforeUpload(index, file)";
        }

        $onUploadProgress = $this->has_event('onUploadProgress') ? ["page.{$myid}_onUploadProgress(index, file, progress)"] : '';
        $onFileUploaded = $this->has_event('onFileUploaded') ? ["page.{$myid}_onFileUploaded(index, file, rst)",'resolve(rst)'] : '';

        $axios_codes = [];
        $this->build_axios_code($bind_api, $axios_codes, $onUploadProgress, $onFileUploaded);
        $codeLines = array_merge($codeLines, $this->build->indent_code(1, $axios_codes));
        $codeLines[] = '}))';
        return  $codeLines;
    }

    function convertSizeToBytes($sizeStr) {
        $size = intval($sizeStr);
        $suffix = strtolower(substr($sizeStr, -2));

        switch ($suffix) {
            case 'kb':
                $size *= 1024;
                break;
            case 'mb':
                $size *= 1024 * 1024;
                break;
            case 'gb':
                $size *= 1024 * 1024 * 1024;
                break;
            case 'tb':
                $size *= 1024 * 1024 * 1024 * 1024;
                break;
            default:
                break;
        }

        return $size;
    }
    protected function build_event_args_code($eventModel, $html_event_name, &$eventCodes, &$actionCodeLines){
        $base_event_args = $this->get_base_event_args();
        $eventCodes[$html_event_name]['args'] = [];
        foreach ($base_event_args as $event_name => $arg){
            if (preg_match("/{$html_event_name}|on{$html_event_name}/i", $event_name)) {
                foreach ($arg['args'] as $item){
                    $eventCodes[$html_event_name]['args'][$item['name']] = $item;
                }
            }
        }
    }
    private function build_filesize_validate_code(){
        $maxFileSize = $this->data['meta']['custom']['maxFileSize'];
        $size = $this->convertSizeToBytes($maxFileSize);

        return <<<EXT_VALIDATE
if(file.size > {$size}) {
    alert(file.name + " exceeds the size limit {$maxFileSize}");
    return;
}
EXT_VALIDATE;
    }

    private function build_ext_validate_code(){
        $accept = $this->data['meta']['custom']['accept'];
        $exts = explode(',', $accept);
        $exts = array_map(function ($item) { return trim($item); } ,array_filter($exts));
        if (!$exts) return;
        $exts = json_encode($exts);

        return <<<EXT_VALIDATE
const ext = file.name.replace(/^.+\./, '');
if({$exts}.indexOf(ext) === -1) {
    alert("accept file: {$accept}");
    return;
}
EXT_VALIDATE;
    }
}
