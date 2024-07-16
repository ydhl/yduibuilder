<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\common\File_Model;

use app\modules\build\views\preview\ValueList_View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use function yangzie\__;

class Table_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;
    public function build_valuelist_static()
    {
        $this->build_ui_begin();
        $this->build_static_table($this->get_table_data());
        $this->build_ui_end();
    }
    public function build_valuelist_iterator($outputData, $outDataName, $iteratorName, $itemName, $is2D = false, $firstIndex = '')
    {
        // table的第一维时行，第二维时列
        $this->build_ui_begin($outputData);

        //header
        if (!$this->data['meta']['custom']['headless']){
            list('name'=>$headText, 'value'=>$xValue) = $this->get_bind_name_value($outputData['item'], 'Header');

            echo $this->indent(2);
            echo "<thead";
            echo $this->wrap_output('class', $this->header_css())
                .">".PHP_EOL;
            echo $this->indent(3)."<tr>".PHP_EOL;

            echo $this->indent(4) . '<template x-for="(itemOfHeader, idxOfHeader) in header" :key="idxOfHeader">'.PHP_EOL;
            echo $this->indent(4)."<th";
            echo $this->wrap_output('class', $this->td_css());
            echo $this->wrap_output('x-text', $headText);
            echo "></th>".PHP_EOL;
            echo $this->indent(4) . "</template>".PHP_EOL;

            echo $this->indent(3)."</tr>".PHP_EOL;
            echo $this->indent(2)."</thead>".PHP_EOL;
        }

        // row
        list('name'=>$tdText, 'value'=>$xValue) = $this->get_bind_name_value($outputData['item'], 'Column');
        echo $this->indent(2) . "<tbody>".PHP_EOL;
        echo $this->indent(2) . '<template x-for="(row, idxOfRow) in row" :key="idxOfRow">'.PHP_EOL;
        echo $this->indent(3) . "<tr>".PHP_EOL;
        echo $this->indent(4) . '<template x-for="(itemOfColumn, idxOfColumn) in row" :key="idxOfColumn">'.PHP_EOL;
        echo $this->indent(4) . "<th";
        echo $this->wrap_output('class', $this->td_css());
        echo $this->wrap_output('x-text', $tdText);
        echo "></th>".PHP_EOL;
        echo $this->indent(4) . "</template>".PHP_EOL;
        echo $this->indent(3) . "</tr>".PHP_EOL;
        echo $this->indent(2) . "</template>".PHP_EOL;
        echo $this->indent(2) . "</tbody>".PHP_EOL;

        //footer
        if (!$this->data['meta']['custom']['footless']){
            list('name'=>$footerText, 'value'=>$xValue) = $this->get_bind_name_value($outputData['item'], 'Footer');
            echo $this->indent(2);
            echo "<tfoot";
            echo $this->wrap_output('class', $this->footer_css()).">".PHP_EOL;
            echo $this->indent(3)."<tr>".PHP_EOL;

            echo $this->indent(4) . "<template x-for=\"(itemOfFooter, idxOfFooter) in footer\" :key=\"idxOfFooter\">".PHP_EOL;
            echo $this->indent(4)."<th";
            echo $this->wrap_output('class', $this->td_css());
            echo $this->wrap_output('x-text', $footerText);
            echo "></th>".PHP_EOL;
            echo $this->indent(4) . "</template>".PHP_EOL;

            echo $this->indent(3)."</tr>".PHP_EOL;
            echo $this->indent(2)."</tfoot>".PHP_EOL;
        }


        $this->build_ui_end();
    }
    protected function build_ui_begin($outputData=null)
    {
        $space =  $this->indent();
        $headless = $this->data['meta']['custom']['headless'];
        $footless = $this->data['meta']['custom']['footless'];
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1)."<table";
        if ($outputData){
            $xdata = [];
            if (!$headless) {
                $xdata[] = "header: []";
                $headerCode = "header = {$outputData['name']}?.shift();";
            }
            if (!$footless) {
                $xdata[] = "footer: []";
                $footerCode = "footer = {$outputData['name']}?.pop(); ";
            }
            $xdata[] = "row: []";
            echo $this->wrap_output('x-data', "{ ".join(',', $xdata)." }");
            echo $this->wrap_output('x-init', "{$footerCode}{$headerCode}row={$outputData['name']}");
        }
        echo $this->wrap_output('class', $this->table_class());
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo $this->indent(1)."</table>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myId().'] tfoot'] = $this->footer_style();
        $style['[data-uiid='.$this->myId().'] thead'] = $this->header_style();
        $style['[data-uiid='.$this->myId().'] table'] = $this->table_style();
        return $style;
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

    private function get_table_data() {
        $excelData = $this->data['meta']['custom']['data'];
        $data = ['header'=>[], 'row'=>[], 'footer'=>[]];
        $data['header'] = $excelData['header'] ?: [];
        $data['row'] = $excelData['row'] ?: [];
        $data['footer'] = $excelData['footer'] ?: [];

        if (!$data['header']) {
            if ($data['row'] && @!$this->data['meta']['custom']['headless']){
                $data['header'] = $data['row'][0];
            }else{
                $data['header'] = [
                    ['name'=>'Header 1'],
                    ['name'=>'Header 2'],
                    ['name'=>'Header 3'],
                ];
            }
        }
        if (!$data['footer']) {
            if ($data['row'] && @!$this->data['meta']['custom']['footless']){
                $data['footer'] =  count($data['row']) > 1 ? end($data['row']) : [];
            }else {
                $data['footer'] = [
                    ['name' => 'Footer 1'],
                    ['name' => 'Footer 2'],
                    ['name' => 'Footer 3'],
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
                    ['name'=>'row 1 column 1'],
                    ['name'=>'row 1 column 2'],
                    ['name'=>'row 1 column 3'],
                ],
                [
                    ['name'=>'row 2 column 1'],
                    ['name'=>'row 2 column 2'],
                    ['name'=>'row 2 column 3'],
                ],
                [
                    ['name'=>'row 3 column 1'],
                    ['name'=>'row 3 column 2'],
                    ['name'=>'row 3 column 3'],
                ],
                [
                    ['name'=>'row 4 column 1<br/> new line'],
                    ['name'=>'row 4 column 2'],
                    ['name'=>'row 4 column 3'],
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
    private function header_css() {
        $css = [];
        if (@$this->data['meta']['css']['header'] && $this->data['meta']['css']['header']!='default'){
            $css[] = 'table-'.$this->data['meta']['css']['header'];
        }
        return $css ? join(' ', $css) : null;
    }
    private function footer_style() {
        $styleMap = @$this->data['meta']['style'];
        if ($this->data['meta']['custom']['footer']){
            return "background-color:".$this->data['meta']['custom']['footer'];
        }else{
            return "background-color:".$styleMap['background-color'];
        }
    }
    private function footer_css() {
        $css = [];
        if (@$this->data['meta']['css']['footer']){
            $css[] = 'table-'.$this->data['meta']['css']['footer'];
        }
        return $css ? join(' ', $css) : null;
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
    private function table_class()
    {
        $cssArray = parent::css_map();
        $styleMap = parent::style_map();

        $newCss[] = 'table overflow-hidden';
        if (@$this->data['meta']['custom']['small']){
            $newCss[] = 'table-sm';
        }
        if (@$this->data['meta']['custom']['stripedRow']){
            $newCss[] = 'table-striped';
        }
        if (@$this->data['meta']['custom']['hoverableRow']){
            $newCss[] = 'table-hover';
        }

        if ($this->data['meta']['custom']['grid'] == 'bordered') {
            $newCss[] = 'table-bordered';
        }else if ($this->data['meta']['custom']['grid'] == 'borderless') {
            $newCss[] = 'table-borderless';
        }
        $backgroundTheme = $this->data['meta']['css']['backgroundTheme'] === 'default' ? '' : $this->data['meta']['css']['backgroundTheme'];
        if ($cssArray['backgroundTheme'] && !$styleMap['background-color']){
            $newCss[] = 'table-' . $backgroundTheme;
        }
        if ($cssArray['foregroundTheme'] && !$styleMap['color']){
            $newCss[] = $cssArray['foregroundTheme'];
        }
        foreach ($cssArray as $cssKey => $item) {
            if(preg_match("/^border/", $cssKey)){
                $newCss[] = $item;
            }
        }
        return $newCss ? join(' ',$newCss) : null;
    }
    private function td_css(){
        $css = parent::css_map();
        $newCss = [];
        if (@$css['verticalAlignment']){
            $newCss[] = $css['verticalAlignment'];
        }
        if (@$css['textAlignment']) {
            $newCss[] = $css['textAlignment'];
        }
        return $newCss ? join(' ', $newCss) : null;
    }

    private function build_static_table($staticData=null){
        //header
        if (!$this->data['meta']['custom']['headless']){
            echo $this->indent(2);
            echo "<thead";
            echo $this->wrap_output('class', $this->header_css())
                .">".PHP_EOL;
            echo $this->indent(3)."<tr>".PHP_EOL;
            foreach($staticData['header'] as $index => $item){
                echo $this->indent(4)."<th";
                echo $this->wrap_output('class', $this->td_css());
                echo ">";
                echo $item['name'];
                echo "</th>".PHP_EOL;
            }
            echo $this->indent(3)."</tr>".PHP_EOL;
            echo $this->indent(2)."</thead>".PHP_EOL;
        }

        // row
        echo $this->indent(2);
        echo "<tbody>".PHP_EOL;
        foreach($staticData['row'] as $rindex => $row){
            echo $this->indent(3)."<tr>".PHP_EOL;
            foreach($row as $cindex => $item) {
                echo $this->indent(4)
                    . "<td";
                echo $this->wrap_output('class', $this->td_css());
                echo ">";
                echo $item['name'];
                echo "</td>".PHP_EOL;
            }
            echo $this->indent(3)."</tr>".PHP_EOL;
        }
        echo $this->indent(2)."</tbody>".PHP_EOL;


        //footer
        if (!$this->data['meta']['custom']['footless']){
            echo $this->indent(2);
            echo "<tfoot";
            echo $this->wrap_output('class', $this->footer_css()).">".PHP_EOL;
            echo $this->indent(3)."<tr>".PHP_EOL;
            foreach($staticData['footer'] as $index => $item){
                echo $this->indent(4)."<th";
                echo $this->wrap_output('class', $this->td_css());
                echo ">";
                echo $item['name'];
                echo "</th>".PHP_EOL;
            }
            echo $this->indent(3)."</tr>".PHP_EOL;
            echo $this->indent(2)."</tfoot>".PHP_EOL;
        }
    }

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        // build_valuelist_static 和 build_valuelist_iterator中实现
    }
}
