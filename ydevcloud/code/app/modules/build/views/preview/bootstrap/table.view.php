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
    protected $columnItems = [];
    public function build_valuelist_static()
    {
        $this->build_ui_begin();
        $this->build_static_table($this->get_table_data());
        $this->build_ui_end();
    }
    protected function build_valuelist_iterator($outputData, $outDataName, $iteratorName, $itemName, $is2D = false, $firstIndex = '')
    {
        // table的第一维是行，第二维是列
        $this->build_ui_begin($outputData);

        //header
        if (!$this->data['meta']['custom']['headless']){
            list('name'=>$headText, 'value'=>$xValue, 'data'=>$boundData) = $this->get_bind_name_value($outputData['item'], 'Header');

            echo $this->indent(2);
            echo "<thead";
            echo $this->wrap_output('class', $this->header_css()).">".PHP_EOL;

            echo $this->indent(3) . '<template x-for="(headerRow, idxOfHeaderRow) in header" :key="idxOfHeaderRow">'.PHP_EOL;
            echo $this->indent(3) . "<tr>".PHP_EOL;

            echo $this->indent(4) . '<template x-for="(itemOfHeader, idxOfHeader) in headerRow" :key="idxOfHeader">'.PHP_EOL;
            echo $this->indent(4) . "<th";
            echo $this->wrap_output('class', $this->td_css());
            echo $this->wrap_output('x-text', $headText);
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output('data-bound', $boundData);
            echo "></th>".PHP_EOL;
            echo $this->indent(4) . "</template>".PHP_EOL;

            echo $this->indent(3)."</tr>".PHP_EOL;
            echo $this->indent(3) . "</template>".PHP_EOL;

            echo $this->indent(2)."</thead>".PHP_EOL;
        }

        // row
        list('name'=>$tdText, 'value'=>$xValue, 'data'=>$boundData) = $this->get_bind_name_value($outputData['item'], 'Column');
        echo $this->indent(2) . "<tbody>".PHP_EOL;
        echo $this->indent(2) . '<template x-for="(row, idxOfRow) in body" :key="idxOfRow">'.PHP_EOL;
        echo $this->indent(3) . "<tr>".PHP_EOL;
        echo $this->indent(4) . '<template x-for="(itemOfColumn, idxOfColumn) in row" :key="idxOfColumn">'.PHP_EOL;
        echo $this->indent(4) . "<th";
        echo $this->wrap_output('class', $this->td_css());
        echo $this->wrap_output('x-text', $tdText);
        echo $this->wrap_output(':data-value', $xValue);
        echo $this->wrap_output('data-bound', $boundData);
        echo "></th>".PHP_EOL;
        echo $this->indent(4) . "</template>".PHP_EOL;
        echo $this->indent(3) . "</tr>".PHP_EOL;
        echo $this->indent(2) . "</template>".PHP_EOL;
        echo $this->indent(2) . "</tbody>".PHP_EOL;

        //footer
        if (!$this->data['meta']['custom']['footless']){
            list('name'=>$footerText, 'value'=>$xValue, 'data'=>$boundData) = $this->get_bind_name_value($outputData['item'], 'Footer');
            echo $this->indent(2);
            echo "<tfoot";
            echo $this->wrap_output('class', $this->footer_css()).">".PHP_EOL;
            echo $this->indent(3) . "<template x-for=\"(footerRow, idxFooterRow) in footer\" :key=\"idxFooterRow\">".PHP_EOL;
            echo $this->indent(3)."<tr>".PHP_EOL;

            echo $this->indent(4) . "<template x-for=\"(itemOfFooter, idxOfFooter) in footerRow\" :key=\"idxOfFooter\">".PHP_EOL;
            echo $this->indent(4)."<th";
            echo $this->wrap_output('class', $this->td_css());
            echo $this->wrap_output('x-text', $footerText);
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output('data-bound', $boundData);
            echo "></th>".PHP_EOL;
            echo $this->indent(4) . "</template>".PHP_EOL;

            echo $this->indent(3)."</tr>".PHP_EOL;
            echo $this->indent(3) . "</template>".PHP_EOL;
            echo $this->indent(2)."</tfoot>".PHP_EOL;
        }


        $this->build_ui_end();
    }
    protected function build_ui_begin($outputData=null)
    {
        $space =  $this->indent();
        $headless = $this->data['meta']['custom']['headless'];
        $footless = $this->data['meta']['custom']['footless'];
        $headerRow = @!$this->data['meta']['custom']['headless'] ? intval($this->data['meta']['custom']['headerRow'])?:1 : 0;
        $footerRow = !$this->data['meta']['custom']['footless'] ? intval($this->data['meta']['custom']['footerRow'])?:1 : 1;

        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1)."<table";
        if ($outputData){
            $xdata = [];
            if (!$headless) {
                $xdata[] = "header: []";
                $headerCode = "header = {$outputData['name']}?.splice(0, {$headerRow});";
            }
            if (!$footless) {
                $xdata[] = "footer: []";
                $footerCode = "footer = {$outputData['name']}?.splice(-{$footerRow}); ";
            }
            $xdata[] = "body: []";
            echo $this->wrap_output('x-data', "{ ".join(',', $xdata)." }");
            echo $this->wrap_output('x-init', "{$footerCode}{$headerCode}body={$outputData['name']}");
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
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        // build_valuelist_static 和 build_valuelist_iterator中实现
    }

    private function get_demo($row, $column) {
        $data = [];
        for($i = 0; $i < $row; $i++){
            for ($j =0; $j < $column; $j++){
                $data[$i][$j] = ['name'=>"row {$i} column {$j}"];
            }
        }
        return $data;
    }

    protected function get_table_data() {
        $excelData = $this->data['meta']['custom']['data'];
        if ($excelData) return $excelData;

        $headerRow = @!$this->data['meta']['custom']['headless'] ? intval($this->data['meta']['custom']['headerRow'])?:1 : 0;
        $footerRow = !$this->data['meta']['custom']['footless'] ? intval($this->data['meta']['custom']['footerRow'])?:1 : 1;
        $bodyRow = intval($this->data['meta']['custom']['bodyRow'])?:1;
        $columnCount = intval($this->data['meta']['custom']['columnCount'])?:2;

        return $this->get_demo($bodyRow + $headerRow + $footerRow, $columnCount);
    }
    private function header_style() {
        $styleMap = $this->data['meta']['style'];
        if ($this->data['meta']['custom']['header']){
            return "background-color:".$this->data['meta']['custom']['header'];
        }else if($styleMap['background-color']){
            return "background-color:".$styleMap['background-color'];
        }
        return '';
    }
    protected function header_css() {
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
        }else if($styleMap['background-color']){
            return "background-color:".$styleMap['background-color'];
        }
        return '';
    }
    protected function footer_css() {
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
    protected function table_class()
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
    protected function td_css(){
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
    protected function get_column_items() {
        if ($this->columnItems) return $this->columnItems;
        if (!$this->childViews) return [];
        $items = [];
        foreach ($this->childViews as $view){
            $placeInParent = $view->data['placeInParent'];
            if (!$placeInParent) continue;
            list($row, $column) = explode('-', $placeInParent);
            if (!$items[$row]) $items[$row] = [];
            if (!$items[$row][$column]) $items[$row][$column] = [];
            $items[$row][$column][] = $view;
        }
        $this->columnItems = $items;
        return $this->columnItems;
    }
    protected function output_td_content($row, $column, $indent, $staticData=[]) {
        $columnItems = $this->get_column_items();
        if (!$columnItems[$row][$column]){
            echo $this->indent(3) . @$staticData[$row][$column]['name'] . PHP_EOL;
            return;
        }

        foreach ($columnItems[$row][$column] as $view){
            $view->increase_indent($indent);
            $view->output();
        }

    }
    protected function get_td_width($column){
        $size = @$this->data['meta']['custom']['size']['width'][$column];
        return $size ? "width:${size}px": null;
    }
    protected function get_tr_height($row){
        $size = @$this->data['meta']['custom']['size']['height'][$row];
        return $size ? "height:${size}px": null;
    }

    protected function build_static_table($staticData=null){
        $headerRow = $this->data['meta']['custom']['headless'] ? 0 : (intval($this->data['meta']['custom']['headerRow'])?:1);
        $footerRow = $this->data['meta']['custom']['footless'] ? 0 : (intval($this->data['meta']['custom']['footerRow'])?:1);
        $bodyRow = intval($this->data['meta']['custom']['bodyRow'])?:1;
        $columnCount = intval($this->data['meta']['custom']['columnCount'])?:2;
        $columnConfig = $this->data['meta']['custom']['columnConfig'];

        //header
        if (!$this->data['meta']['custom']['headless']){
            echo $this->indent(1);
            echo "<thead";
            echo $this->wrap_output('class', $this->header_css())
                .">".PHP_EOL;
            for($row=0; $row < $headerRow; $row++){
                echo $this->indent(2)."<tr";
                echo $this->wrap_output('style', $this->get_tr_height($row));
                echo ">".PHP_EOL;
                for($column=0; $column < $columnCount; $column++){
                    if ($columnConfig[$row][$column]['isMerged']) continue;
                    echo $this->indent(3)."<th";
                    echo $this->wrap_output('class', $this->td_css());
                    echo $this->wrap_output('style', $this->get_td_width($column));
                    echo $this->wrap_output('colspan', $columnConfig[$row][$column]['colspan']);
                    echo $this->wrap_output('rowspan', $columnConfig[$row][$column]['rowspan']);
                    echo ">".PHP_EOL;
                    $this->output_td_content($row, $column, 3, $staticData);
                    echo $this->indent(3) . "</th>".PHP_EOL;
                }
                echo $this->indent(2)."</tr>".PHP_EOL;
            }

            echo $this->indent(1)."</thead>".PHP_EOL;
        }

        // row
        $this->build_static_body($headerRow,$bodyRow,$columnCount,$columnConfig,$staticData);

        //footer
        if (!$this->data['meta']['custom']['footless']){
            echo $this->indent(1);
            echo "<tfoot";
            echo $this->wrap_output('class', $this->footer_css()).">".PHP_EOL;
            for($row=0; $row < $footerRow; $row++) {
                echo $this->indent(2) . "<tr";
                echo $this->wrap_output('style', $this->get_tr_height($row + $bodyRow + $headerRow));
                echo ">".PHP_EOL;
                for($column=0; $column < $columnCount; $column++){
                    if ($columnConfig[$row + $bodyRow + $headerRow][$column]['isMerged']) continue;
                    echo $this->indent(3) . "<th";
                    echo $this->wrap_output('class', $this->td_css());
                    echo $this->wrap_output('colspan', $columnConfig[$row + $bodyRow + $headerRow][$column]['colspan']);
                    echo $this->wrap_output('rowspan', $columnConfig[$row + $bodyRow + $headerRow][$column]['rowspan']);
                    echo ">".PHP_EOL;
                    $this->output_td_content($row + $headerRow + $bodyRow, $column, 3, $staticData);
                    echo $this->indent(3) . "</th>" . PHP_EOL;
                }
                echo $this->indent(2) . "</tr>" . PHP_EOL;
            }
            echo $this->indent(1)."</tfoot>".PHP_EOL;
        }
    }
    protected function build_static_body($headerRow,$bodyRow,$columnCount,$columnConfig,$staticData) {
        echo $this->indent(1);
        echo "<tbody>".PHP_EOL;
        for($row=0; $row < $bodyRow; $row++) {
            echo $this->indent(2) . "<tr";
            echo $this->wrap_output('style', $this->get_tr_height($row + $headerRow));
            echo ">".PHP_EOL;
            for ($column=0; $column < $columnCount; $column++) {
                if ($columnConfig[$row + $headerRow][$column]['isMerged']) continue;
                echo $this->indent(3). "<td";
                echo $this->wrap_output('style', $this->get_td_width($column));
                echo $this->wrap_output('class', $this->td_css());
                echo $this->wrap_output('colspan', $columnConfig[$row + $headerRow][$column]['colspan']);
                echo $this->wrap_output('rowspan', $columnConfig[$row + $headerRow][$column]['rowspan']);
                echo ">".PHP_EOL;
                $this->output_td_content($row + $headerRow, $column, 3, $staticData);
                echo $this->indent(3) . "</td>" . PHP_EOL;
            }
            echo $this->indent(2) . "</tr>" . PHP_EOL;
        }
        echo $this->indent(1)."</tbody>".PHP_EOL;
    }
    protected function build_data_bind_body($headerRow,$columnCount,$columnConfig,$outputDatas, $dataNames) {
        echo $this->indent(1);
        echo "<tbody>".PHP_EOL;
        $dataName = $dataNames['NONE'];
        $iterateDataName = $outputDatas['NONE']['name'] ?: $dataName;

        echo $this->indent(2) . '<template x-for="(itemOf'.$iterateDataName.', idxOf'.$iterateDataName.') in '.$dataName.'" :key="idxOf'.$iterateDataName.'">'.PHP_EOL;
        $this->set_iterator_index_name("idxOf{$iterateDataName}");
        $this->set_iterator_data_name("itemOf{$iterateDataName}");

        echo $this->indent(2) . "<tr";
        echo $this->wrap_output('style', $this->get_tr_height($headerRow));
        echo ">".PHP_EOL;
        for ($column=0; $column < $columnCount; $column++) {
            if ($columnConfig[$headerRow][$column]['isMerged']) continue;
            echo $this->indent(3). "<td";
            echo $this->wrap_output('style', $this->get_td_width($column));
            echo $this->wrap_output('class', $this->td_css());
            echo $this->wrap_output('colspan', $columnConfig[$headerRow][$column]['colspan']);
            echo $this->wrap_output('rowspan', $columnConfig[$headerRow][$column]['rowspan']);
            echo ">".PHP_EOL;
            $this->output_td_content($headerRow, $column, 4, []);
            echo $this->indent(3) . "</td>" . PHP_EOL;
        }
        echo $this->indent(2) . "</tr>" . PHP_EOL;
        echo $this->indent(2) . "</template>" . PHP_EOL;

        echo $this->indent(1)."</tbody>".PHP_EOL;
    }

    public function is_input_ui() {
        return false;
    }
}
