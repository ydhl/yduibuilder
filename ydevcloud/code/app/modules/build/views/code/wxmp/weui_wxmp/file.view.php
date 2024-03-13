<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\File_View as Preview_File_View;

class File_View extends Preview_File_View {
    use Wxmp;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_ui()
    {
        $space =  $this->indent(4);
        echo "\r\n{$space}";
        echo "<view class='weui-uploader__bd'>\r\n";
        echo $this->indent(5);
        echo "<view class='weui-uploader__input-box'>\r\n";
        echo $this->indent(6);
        echo '<view class="weui-uploader__input" bindtap="'.$this->myId().'_ChooseFile"';
        echo $this->build_form_attrs();

        echo "></view>\r\n";
        echo $this->indent(5);
        echo "</view>\r\n";
        echo "{$space}";
        echo "</view>\r\n";
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
wx.chooseMessageFile({
    count: <?= @$this->data['meta']['custom']['multiple'] ? 9 : 1 ?>,
})
<?php
        $this->get_code_fragment()->add_function($this->myId().'_ChooseFile(e)', ob_get_clean());
        return $this->get_code_fragment();
    }
}
