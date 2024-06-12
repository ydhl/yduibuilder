<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\code\Base_Code_Fragment;


use app\modules\build\views\preview\Preview_View;

class Pagination_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;

    public function build_ui()
    {

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo "></div>\r\n";
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $this->get_code_Fragment()->add_use('laypage');
        ob_start();
?>
var laypage = layui.laypage;
laypage.render({
    elem: '<?= $this->myId(true)?>'
    ,count: 100
    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
    ,jump: function(obj){
        console.log(obj)
    }
});
<?php
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_fragment();
    }
}
