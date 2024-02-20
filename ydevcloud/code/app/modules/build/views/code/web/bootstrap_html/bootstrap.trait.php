<?php
namespace app\modules\build\views\code\web\bootstrap_html;


use app\build\Build_Model;
/**
 * 由于bootstrap 生成的代码和在线预览一样，所以web前端不同框架代码生成Class都继承之Preview
 * 这里通过trait都方式统一重载相关的实现
 * @package app\api\views
 */
trait Bootstrap {
    public static function get_View_Class(array $uiconfig, Build_Model $build){
        $type = strtolower($uiconfig['type']);
        return "app\\modules\\build\\views\\code\\web\\bootstrap_html\\{$type}_View";
    }
    protected function get_Img_Src($imgSrc){
        return rtrim($this->build->get_img_Asset_Path(), '/').'/'.basename(urldecode($imgSrc), PATHINFO_BASENAME);
    }
    protected function get_popup_page_url($page) {
        return $page->get_save_path('html');
    }
}
