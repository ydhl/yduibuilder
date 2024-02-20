<?php
namespace app\vendor\css;
use function yangzie\__;

class Layui_factory extends Css_Factory{
    public function cssTranslat($version){
        return [
            'themeColor'=> ['primary'=>'#009688', 'secondary'=>'#1E9FFF', 'success'=>'#5FB878', 'danger'=>'#FF5722', 'warning'=>'#FFB800', 'info'=>'#2F4056', 'light'=>'#FAFAFA', 'dark'=>'#393D49', 'default'=>''],
            'foregroundTheme'=> ['primary'=>'layui-font-green', 'secondary'=>'layui-font-blue', 'success'=>'layui-font-success','danger'=>'layui-font-red', 'warning'=>'layui-font-orange', 'info'=>'layui-font-cyan', 'light'=>'layui-font-gray', 'dark'=>'layui-font-black', 'default'=>''],
            'backgroundTheme'=> ['primary'=>'layui-bg-green', 'secondary'=>'layui-bg-blue', 'success'=>'layui-bg-success','danger'=>'layui-bg-red', 'warning'=>'layui-bg-orange', 'info'=>'layui-bg-cyan', 'light'=>'layui-bg-gray', 'dark'=>'layui-bg-black', 'default'=>''],

            'buttonSizing'=> [ 'lg'=> 'layui-btn-lg', 'normal'=> '', 'sm'=> 'layui-btn-sm', 'xs'=>'layui-btn-xs'],
            'paginationSizing'=> [ 'lg'=> 'layui-pagination-lg', 'sm'=> 'layui-pagination-sm','normal'=> '' ],
            'dropdownSizing'=> [ 'normal'=> '', 'lg'=> 'layui-btn-lg', 'sm'=> 'layui-btn-sm', 'xs'=>'layui-btn-xs'],
            'formSizing'=> [ 'normal'=> '', 'lg'=> 'layui-form-lg', 'sm'=> 'layui-form-sm'],

            'container'=> [ 'fluid'=> 'layui-fluid', 'container'=> 'layui-container', 'none'=> '', 'row'=>'layui-row' ],

            'grid'=> [ 'none'=> '', 'col1'=> 'layui-col-xs1', 'col2'=> 'layui-col-xs2', 'col3'=> 'layui-col-xs3', 'col4'=> 'layui-col-xs4', 'col5'=> 'layui-col-xs5', 'col6'=> 'layui-col-xs6', 'col7'=> 'layui-col-xs7', 'col8'=> 'layui-col-xs8', 'col9'=> 'layui-col-xs9', 'col10'=> 'layui-col-xs10', 'col11'=> 'layui-col-xs11', 'col12'=> 'layui-col-xs12' ],
            'offset'=> [ 'none'=> 'None', 'offset1'=> 'layui-col-xs-offset1', 'offset2'=> 'layui-col-xs-offset2', 'offset3'=> 'layui-col-xs-offset3', 'offset4'=> 'layui-col-xs-offset4', 'offset5'=> 'layui-col-xs-offset5', 'offset6'=> 'layui-col-xs-offset6', 'offset7'=> 'layui-col-xs-offset7', 'offset8'=> 'layui-col-xs-offset8', 'offset9'=> 'layui-col-xs-offset9', 'offset10'=> 'layui-col-xs-offset10', 'offset11'=> 'layui-col-xs-offset11' ],

            'margin'=> [ 'inherit'=> '', 'm-0'=> 'layui-m-0', 'm-1'=> 'layui-m-1', 'm-2'=> 'layui-m-2', 'm-3'=> 'layui-m-3', 'm-4'=> 'layui-m-4', 'm-5'=> 'layui-m-5', 'm-auto'=> 'layui-m-auto' ],
            'margin-top'=> [ 'inherit'=> '', 'mt-0'=> 'layui-mt-0', 'mt-1'=> 'layui-mt-1', 'mt-2'=> 'layui-mt-2', 'mt-3'=> 'layui-mt-3', 'mt-4'=> 'layui-mt-4', 'mt-5'=> 'layui-mt-5', 'mt-auto'=> 'layui-mt-auto' ],
            'margin-right'=> [ 'inherit'=> '', 'mr-0'=> 'layui-mr-0', 'mr-1'=> 'layui-mr-1', 'mr-2'=> 'layui-mr-2', 'mr-3'=> 'layui-mr-3', 'mr-4'=> 'layui-mr-4', 'mr-5'=> 'layui-mr-5', 'mr-auto'=> 'layui-mr-auto' ],
            'margin-bottom'=> [ 'inherit'=> '', 'mb-0'=> 'layui-mb-0', 'mb-1'=> 'layui-mb-1', 'mb-2'=> 'layui-mb-2', 'mb-3'=> 'layui-mb-3', 'mb-4'=> 'layui-mb-4', 'mb-5'=> 'layui-mb-5', 'mb-auto'=> 'layui-mb-auto' ],
            'margin-left'=> [ 'inherit'=> '', 'ml-0'=> 'layui-ml-0', 'ml-1'=> 'layui-ml-1', 'ml-2'=> 'layui-ml-2', 'ml-3'=> 'layui-ml-3', 'ml-4'=> 'layui-ml-4', 'ml-5'=> 'layui-ml-5', 'ml-auto'=> 'layui-ml-auto' ],

            'padding'=> [ 'inherit'=> '', 'p-0'=> 'layui-p-0', 'p-1'=> 'layui-p-1', 'p-2'=> 'layui-p-2', 'p-3'=> 'layui-p-3', 'p-4'=> 'layui-p-4', 'p-5'=> 'layui-p-5' ],
            'padding-top'=> [ 'inherit'=> '', 'pt-0'=> 'layui-pt-0', 'pt-1'=> 'layui-pt-1', 'pt-2'=> 'layui-pt-2', 'pt-3'=> 'layui-pt-3', 'pt-4'=> 'layui-pt-4', 'pt-5'=> 'layui-pt-5' ],
            'padding-right'=> [ 'inherit'=> '', 'pr-0'=> 'layui-pr-0', 'pr-1'=> 'layui-pr-1', 'pr-2'=> 'layui-pr-2', 'pr-3'=> 'layui-pr-3', 'pr-4'=> 'layui-pr-4', 'pr-5'=> 'layui-pr-5'],
            'padding-bottom'=> [ 'inherit'=> '', 'pb-0'=> 'layui-pb-0', 'pb-1'=> 'layui-pb-1', 'pb-2'=> 'layui-pb-2', 'pb-3'=> 'layui-pb-3', 'pb-4'=> 'layui-pb-4', 'pb-5'=> 'layui-pb-5' ],
            'padding-left'=> [ 'inherit'=> '', 'pl-0'=> 'layui-pl-0', 'pl-1'=> 'layui-pl-1', 'pl-2'=> 'layui-pl-2', 'pl-3'=> 'layui-pl-3', 'pl-4'=> 'layui-pl-4', 'pl-5'=> 'layui-pl-5' ],

            'borderColorClass'=> ['primary'=>'layui-border-primary', 'secondary'=>'layui-border-secondary', 'success'=>'layui-border-success-ext', 'info'=>'layui-border-info-ext','light'=>'layui-border-light-ext', 'danger'=>'layui-border-danger', 'warning'=>'layui-border-warning', 'dark'=>'layui-border-dark', 'default'=>''],

            'verticalAlignment' => ['inherit'=>'', 'top' => 'layui-align-top','middle' => 'layui-align-middle','bottom' => 'layui-align-bottom'],
            'textAlignment' => ['inherit'=>'', 'left' => 'layui-text-left','center' => 'layui-text-center','right' => 'layui-text-right']
        ];
    }
}
