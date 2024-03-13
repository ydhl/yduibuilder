<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\Preview_View;


class Image_View extends Preview_View {
    use Wxmp;

    protected function style_map()
    {
        $map = parent::style_map();

        if (strtolower(@$this->data['meta']['style']['width']) == 'auto'){
            unset($map['width']);
        }
        if (strtolower(@$this->data['meta']['style']['height']) == 'auto'){
            unset($map['height']);
        }
        return $map;
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
this.setData({
    <?=$this->myId()?>_Height: e.detail.height,
    <?=$this->myId()?>_Width: e.detail.width
})
<?php
        $this->get_code_fragment()->add_function("{$this->myId()}_Load(e)", ob_get_clean());
        $this->get_code_fragment()->add_data($this->myId().'_Height', "");
        $this->get_code_fragment()->add_data($this->myId().'_Width', "");
        return $this->get_code_fragment();
    }

    private function imageSizeStyle(){
        $style = [];
        //image组件默认宽度320px、高度240px, 当没有指定图片当尺寸时，默认宽度100% 高度自适应
        //如果高宽度的值是auto，则用原图大小
        if (!@$this->data['meta']['style']['width']){
            $style[] = "width:100%";
        }
        if (strtolower(@$this->data['meta']['style']['height']) == 'auto'){
            $style[] = "height:{{".$this->myId()."_Height}}px";
        }

        if (strtolower(@$this->data['meta']['style']['width']) == 'auto'){
            $style[] = "width:{{".$this->myId()."_Width}}px";
        }

        return join(";", $style);
    }
    public function build_ui()
    {
        $space =  $this->indent();
        $imgSrc = @$this->data['meta']['value']?:'/assets/images/uibuilder.jpg';
        $imgSrc = $this->get_Img_Src($imgSrc);
        $this->add_attr('bindload', $this->myId()."_Load");
        $this->add_attr('mode', 'widthFix');
        $this->add_attr('style', $this->imageSizeStyle(), ';');
        $this->add_attr('src', $imgSrc);

        echo "{$space}<image";
        echo $this->build_main_attrs();
        echo "/>\r\n";

    }
}
