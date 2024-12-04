<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\File_View as Preview_File_View;
use app\modules\build\views\code\web\Vue;
use app\project\Page_Bind_Api_Model;

class File_View extends Preview_File_View {
    use Vue {
        Vue::build_code as vueBuildCode;
        Vue::get_base_data_attrs as vueBaseAttrs;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<FileComponent";
        $this->output_component_props();
        $bind_api = Page_Bind_Api_Model::find_by_uuid($this->data['meta']['custom']['apiUuid']);
        if ($bind_api){
            $inputPath = $bind_api->get_input_configs('path');
            if ($inputPath){
                $bindVariables = $this->build->get_bind_variables('to', Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
                $formatVariables = [];
                foreach ($bindVariables as $bindVariable){
                    $formatVariables[$bindVariable->to_data_id] = $bindVariable;
                }
                $params = $this->get_param_variable($formatVariables, $inputPath);
                $url = $this->replace_param($bind_api->path, $params);
                if ($params){
                    echo $this->wrap_output(":uploadUrl", "`{$url}`");
                }else{
                    echo $this->wrap_output("uploadUrl", $url);
                }
            }else{
                echo $this->wrap_output("uploadUrl", $bind_api->path);
            }
        }
        if ($this->data['meta']['custom']['multiple']){
            echo $this->wrap_output(":multiple", 'true');
        }
        if ($this->data['meta']['custom']['isAutoUpload']){
            echo $this->wrap_output(":isAutoUpload", 'true');
        }
        echo $this->wrap_output("accept", $this->data['meta']['custom']['accept']);
        echo $this->wrap_output("maxFileSize", $this->data['meta']['custom']['maxFileSize']);

        echo PHP_EOL."{$space}";

        $this->output_v_model();
        echo "></FileComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/FileComponent.vue', [], 'FileComponent');
        return $fragment;
    }

    protected function output_v_model(){
        $needIterate = $this->get_iterator_index_names();
        $inputDataName = $this->get_input_data_name($inputIsArr, $inputDataConfig);
        echo $needIterate && $inputIsArr ? $this->wrap_output('v-input.file', $inputDataName) : $this->wrap_output('v-model', $inputDataName);
    }
    protected function get_base_data_attrs(){
        $attrs = $this->vueBaseAttrs();

        if (@$this->data['meta']['form']['state']=='disabled') $attrs[] = 'disabled:true';
        if (@$this->data['meta']['form']['state']=='readonly') $attrs[] = 'readonly:true';

        return $attrs;
    }
}
