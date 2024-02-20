<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;
use app\project\Page_Model;
use function yangzie\__;

class UIComponent_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$this->childViews as $view){
            $data = $view->get_datas();
            if (@$data['subPageDeleted']){
                $view->get_build()->output_code(vsprintf(__("UI COMPONENT [%s] HAS BEEN DELETED"), $data['meta']['title']));
            }else{
                $view->output();
            }
        }

        echo "{$space}</div>\r\n";
    }
}
