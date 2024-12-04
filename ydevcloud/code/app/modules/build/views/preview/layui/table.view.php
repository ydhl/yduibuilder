<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\Preview_View;
use app\common\File_Model;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Table_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function parseExcel() {
        $files = @$this->data['meta']['files']['datasource'] ?: [];
        $fid = $files[0]["id"];
        $file = find_by_uuid(File_Model::CLASS_NAME, $fid);
        if (!$file) return [];
        $data = [
            'header'=>[],
            'footer'=>[],
            'row'=>[]
        ];
        $tmp = tempnam('/tmp', 'excel');
        file_put_contents($tmp, file_get_contents($file->url));
        $spreadsheet = IOFactory::load($tmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rowData = $worksheet->toArray();
        $data['row'] = array_map(function ($row) {
            return array_map(function ($column) {
                return ['text'=>$column];
            }, $row);
        }, $rowData);
        return $data;
    }
    private function get_table_data() {
        $excelData = $this->parseExcel();
        $data = ['header'=>[], 'row'=>[], 'footer'=>[]];
        $data['header'] = $excelData['header'] ?: [];
        $data['row'] = $excelData['row'] ?: [];
        $data['footer'] = $excelData['footer'] ?: [];

        if (!$data['header']) {
            if ($data['row'] && @!$this->data['meta']['custom']['headless']){
                $data['header'] = $data['row'][0];
            }else{
                $data['header'] = [
                    ['text'=>'Header 1'],
                    ['text'=>'Header 2'],
                    ['text'=>'Header 3'],
                ];
            }
        }
        if (!$data['footer']) {
            if ($data['row'] && @!$this->data['meta']['custom']['footless']){
                $data['footer'] = end($data['row']);
            }else {
                $data['footer'] = [
                    ['text' => 'Footer 1'],
                    ['text' => 'Footer 2'],
                    ['text' => 'Footer 3'],
                ];
            }
        }
        if ($data['row']) {
            if (@!$this->data['meta']['custom']['headless']){
                array_splice($data['row'], 0, 1);
            }
            if (@!$this->data['meta']['custom']['footless']){
                array_splice($data['row'], -1, 1);
            }
        }else{
            $data['row'] = [
                [
                    ['text'=>'row 1 column 1'],
                    ['text'=>'row 1 column 2'],
                    ['text'=>'row 1 column 3'],
                ],
                [
                    ['text'=>'row 2 column 1'],
                    ['text'=>'row 2 column 2'],
                    ['text'=>'row 2 column 3'],
                ],
                [
                    ['text'=>'row 3 column 1'],
                    ['text'=>'row 3 column 2'],
                    ['text'=>'row 3 column 3'],
                ],
                [
                    ['text'=>'row 4 column 1<br/> new line'],
                    ['text'=>'row 4 column 2'],
                    ['text'=>'row 4 column 3'],
                ],
            ];
        }
        return $data;
    }
    private function header_style() {
        $styleMap = $this->data['meta']['style'];
        if ($this->data['meta']['custom']['header']){
            return "background-color:".$this->data['meta']['custom']['header'];
        }else{
            return "background-color:".$styleMap['background-color'];
        }
    }
    private function footer_style() {
        $styleMap = @$this->data['meta']['style'];
        if ($this->data['meta']['custom']['footer']){
            return "background-color:".$this->data['meta']['custom']['footer'];
        }else{
            return "background-color:".$styleMap['background-color'];
        }
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myId().'] tfoot'] = $this->footer_style();
        $style['[data-uiid='.$this->myId().'] thead'] = $this->header_style();
        $style['[data-uiid='.$this->myId().'] table'] = $this->table_style();
        return $style;
    }

    private function header_css() {
        $css = [];
        if (@$this->data['meta']['css']['header'] && $this->data['meta']['css']['header']!='default'){
            $css[] = 'layui-table-'.$this->data['meta']['css']['header'];
        }
        return join(' ', $css);
    }

    private function footer_css() {
        $css = [];
        if (@$this->data['meta']['css']['footer']){
            $css[] = 'layui-table-'.$this->data['meta']['css']['footer'];
        }
        return join(' ', $css);
    }

    private function backgroundTheme(){
        return $this->data['meta']['css']['backgroundTheme'] === 'default' ? '' : $this->data['meta']['css']['backgroundTheme'];
    }

    /**
     * 单元格的对齐css
     * @return string
     */
    private function align_css(){
        $css = parent::css_map();
        $newCss = [];
        if (@$css['verticalAlignment']){
            $newCss[] = $css['verticalAlignment'];
        }
        if (@$css['textAlignment']) {
            $newCss[] = $css['textAlignment'];
        }
        return $newCss ? join(' ', $newCss) : '';
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        unset($cssArray['textAlignment'],
            $cssArray['verticalAlignment'],
            $cssArray['header'],
            $cssArray['footer'],
            $cssArray['backgroundTheme'],
            $cssArray['foregroundTheme']);
        return $cssArray;
    }
    private function table_class(){
        $cssArray = parent::css_map();

        $newCss['-'] = 'layui-table layui-overflow-hidden';

        if (@$this->data['meta']['custom']['stripedRow']){
            $newCss['striped'] = 'layui-table-striped';
        }
        if (@$this->data['meta']['custom']['hoverableRow']){
            $newCss['hoverable'] = 'layui-table-hover';
        }

        foreach ($cssArray as $cssKey => $item) {
            if ($cssKey=='backgroundTheme' && $this->backgroundTheme()){
                $newCss[$cssKey] = 'layui-table-' . $this->backgroundTheme();
            }else if (preg_match("/^border/", $cssKey) || preg_match("/^outline/", $cssKey)){
                $newCss[$cssKey] = $item;
            }else if ($cssKey=='foregroundTheme'){
                $newCss[$cssKey] = $item;
            }
        }
        return join(' ',$newCss);
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        foreach ($style as $name => $value) {
            if (preg_match("/^background/", $name)) unset($style[$name]);
            if (preg_match("/^color/", $name)) unset($style[$name]);
        }
        $style['overflow'] = 'overflow:hidden';
        return $style;
    }

    private function table_style(){
        $style = parent::style_map();
        $newStyle = [];
        foreach ($style as $name => $value) {
            if (preg_match("/^background/", $name)) $newStyle[] = $value;
            if (preg_match("/^color/", $name)) $newStyle[] = $value;
            if (preg_match("/^border/", $name)) $newStyle[] = $value;
        }
        $newStyle[] = 'margin:0px';
        $newStyle[] = 'overflow:hidden';
        return join(';', $newStyle);
    }

    public function build_ui()
    {
        $data = $this->get_table_data();
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">\r\n";
        echo $this->indent(1) . "<table";
        echo $this->wrap_output('lay-size', $this->data['meta']['custom']['small']?'sm':null);
        if ($this->data['meta']['custom']['grid'] == 'borderless'){
            echo $this->wrap_output('lay-skin', 'nob');
        }else if ($this->data['meta']['custom']['grid'] == 'bordered'){
            echo $this->wrap_output('lay-skin', '');
        }else{
            echo $this->wrap_output('lay-skin', 'line');
        }
        echo $this->wrap_output('class', $this->table_class());
        echo ">\r\n";

        //header
        if (!$this->data['meta']['custom']['headless']){
            echo $this->indent(1);
            echo "<thead";
            echo $this->wrap_output('class', $this->header_css());
            echo ">\r\n";
            echo $this->indent(2)."<tr>\r\n";
            foreach($data['header'] as $index => $item){
                echo $this->indent(3)."<th";
                echo $this->wrap_output('class', $this->align_css());
                echo ">";
                echo $item['text'];
                echo "</th>\r\n";
            }
            echo $this->indent(2)."</tr>\r\n";
            echo $this->indent(1);
            echo "</thead>\r\n";
        }

        // row
        echo $this->indent(1);
        echo "<tbody>\r\n";
        foreach($data['row'] as $rindex => $row){
            echo $this->indent(2)."<tr>\r\n";
            foreach($row as $cindex => $item) {
                echo $this->indent(3)."<td";
                echo $this->wrap_output('class', $this->align_css());
                echo ">";
                echo $item['text'];
                echo "</td>\r\n";
            }
            echo $this->indent(2)."</tr>\r\n";
        }
        echo $this->indent(1);
        echo "</tbody>\r\n";


        //footer
        if (!$this->data['meta']['custom']['footless']){
            echo $this->indent(1);
            echo "<tfoot";
            echo $this->wrap_output('class', $this->footer_css());
            echo ">\r\n";
            echo $this->indent(2)."<tr>\r\n";
            foreach($data['footer'] as $index => $item){
                echo $this->indent(3)."<th";
                echo $this->wrap_output('class', $this->align_css());
                echo ">";
                echo $item['text'];
                echo "</th>\r\n";
            }
            echo $this->indent(2)."</tr>\r\n";
            echo $this->indent(1);
            echo "</tfoot>\r\n";
        }

        echo "{$space}</table>\r\n";
        echo "{$space}</div>\r\n";
    }

}
