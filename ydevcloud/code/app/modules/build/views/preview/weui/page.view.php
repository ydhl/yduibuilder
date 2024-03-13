<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Page_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function style_map()
    {
        $map = parent::style_map();
        if (!@$map['height'] && $this->data['pageType']!='popup'){
            $map['height']= 'height:100vh';
        }
        $map['flex-grow']= 'flex-grow:1';

        return $map;
    }
    public function build_code():Base_Code_Fragment{
        parent::build_code();
        ob_start();
?>
    $("body").attr('data-weui-theme', 'light')
<?php
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_Fragment();
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">";
        echo "\r\n";
        foreach ($this->childViews as $view){
            $view->output();
        }

        echo "{$space}</div>\r\n";
    }
}
