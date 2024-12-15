<?php
namespace app\modules\build\views\code\web\bootstrap_vue;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\web\Vue;
use app\modules\build\views\preview\bootstrap\UIComponent_View as Preview_UIComponent_View;

use app\project\Action_Model;
use app\project\Page_Bind_Data_Model;
use function yangzie\__;

class UIComponent_View extends Preview_UIComponent_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_style($justSelf = true)
    {
        // 组件但sfp中编译，不在主页面上体现，所以这里不用调用父类的build_style
        // ui 组件被删除了，不编译其style
        if ($this->data['subPageDeleted']){
            return [];
        }
        $key = '[data-uiid='.$this->myId().']';
        if ($this->styles){
            return $justSelf ? [$key =>  $this->styles[$key]] : $this->styles;
        }
        $this->styles = [];
        $styleArray = $this->style_map();

        if ($styleArray) {
            $this->styles[$key] =  join(' !important;'.PHP_EOL, array_values($styleArray)).' !important;';
        }

        $this->style_of_state();
        $this->style_of_pseudo();

        if ($this->check_master()){
            $master = $this->master_view;
            $this->styles = array_merge($this->styles, $master->build_style(false));
        }

        foreach ($this->styles as $key => $styles){
            $this->styles[$key] = is_array($this->styles[$key]) ? array_unique($this->styles[$key]) : $this->styles[$key];
        }

        return $justSelf ? [$key =>  $this->styles[$key]] : $this->styles;
    }

    public function build_code(): Base_Code_Fragment
    {
        // 组件在sfp中编译，不在主页面上体现，所以这里不用调用父类的build_code
        $fragment = $this->get_code_fragment();
        if ($this->data['subPageDeleted']){
            return $fragment;
        }

        $this->build_event_code();
        $this->build_initialize_code();
        $this->build_custom_event_code();
        // 实际上只有一个item
        $uiPageId = $this->data['items'][0]['subPageId'];
        $page = $this->get_page($uiPageId);
        $fragment->add_import('@/' . $page->get_save_path('vue'), [], $uiPageId);
        return $fragment;
    }

    public function build_ui()
    {
        $space =  $this->indent();

        // 实际上只有一个item
        foreach ((array)@$this->childViews as $view){
            $data = $view->get_datas();
            $uiPageId = $data['subPageId'];
            if (@$data['subPageDeleted']){
                $view->get_build()->output_code(sprintf(__("UI COMPONENT [%s] HAS BEEN DELETED"), $data['meta']['title']));
            }else{
                echo $space.'<'.$uiPageId;
                $this->output_event_listen_props();
                $this->output_component_params($uiPageId);
                echo '></'.$uiPageId.'>'.PHP_EOL;
            }
        }
    }
    private function output_component_params($componentPageId){
        $componentPage = $this->get_page($componentPageId);

        $queryArgs = [];
        $variables = $this->build->get_bind_variables('from');
        foreach ($variables as $variable){
            $expression = $variable->get_expression();
            if ($variable->to_page_id == $componentPage->id && $expression){
                $queryArgs[] = $variable->to_data_path.': '. $this->remove_page_scope_data_prefix($expression->get_expression_code(false));
            }
        }
        if(!$queryArgs) return;
        echo $this->wrap_output(':param', "{".join(", ", $queryArgs)."}");
    }
}
