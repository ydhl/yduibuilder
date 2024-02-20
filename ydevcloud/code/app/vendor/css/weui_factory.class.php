<?php
namespace app\vendor\css;
class Weui_factory extends Css_Factory{
    public function cssTranslat($version){
        return [
            'themeColor'=> ['primary'=>'var(--weui-INDIGO)', 'secondary'=>'var(--weui-BG-4)', 'success'=>'var(--weui-BRAND)', 'danger'=>'var(--weui-RED)', 'warning'=>'var(--weui-ORANGE)', 'info'=>'var(--weui-LINK)', 'light'=>'var(--weui-WHITE)', 'dark'=>'var(--weui-BG-4)', 'default'=>''],
            'foregroundTheme'=> ['primary'=>'text-primary', 'secondary'=>'text-secondary', 'success'=>'text-success', 'danger'=>'text-danger', 'warning'=>'text-warning', 'info'=>'text-info', 'light'=>'text-light', 'dark'=>'text-dark', 'default'=>''],
            'backgroundTheme'=> ['primary'=>'bg-primary', 'secondary'=>'bg-secondary', 'success'=>'bg-success', 'danger'=>'bg-danger', 'warning'=>'bg-warning', 'info'=>'bg-info', 'light'=>'bg-light', 'dark'=>'bg-dark', 'default'=>''],

            'buttonSizing'=> [ 'lg'=> 'weui-btn_lg', 'default'=> '', 'sm'=> 'weui-btn_mini','xs'=>'weui-btn_xs'],
            'paginationSizing'=> [ 'lg'=> 'pagination-lg', 'default'=> '', 'sm'=> 'pagination-sm' ],
            'dropdownSizing'=> [ 'lg'=> 'btn-lg', 'default'=> '', 'sm'=> 'btn-sm','xs'=>'btn-xs' ],
            'formSizing'=> [ 'lg'=> 'weui-form-lg', 'default'=> '', 'sm'=> 'weui-form-sm'],

            'container'=> [ 'fluid'=> 'fluid-container', 'container'=> 'container', 'none'=> '', 'row'=>'row' ],

            // grid需要考虑设备的尺寸大小，包含默认，sm，md，lg，xl几种
            'grid'=>[
                'xs'=>[ 'none'=> '', 'col1'=> 'col-1', 'col2'=> 'col-2', 'col3'=> 'col-3', 'col4'=> 'col-4', 'col5'=> 'col-5', 'col6'=> 'col-6', 'col7'=> 'col-7', 'col8'=> 'col-8', 'col9'=> 'col-9', 'col10'=> 'col-10', 'col11'=> 'col-11', 'col12'=> 'col-12' ],
                'sm'=>[ 'none'=> '', 'col1'=> 'col-sm-1', 'col2'=> 'col-sm-2', 'col3'=> 'col-sm-3', 'col4'=> 'col-sm-4', 'col5'=> 'col-sm-5', 'col6'=> 'col-sm-6', 'col7'=> 'col-sm-7', 'col8'=> 'col-sm-8', 'col9'=> 'col-sm-9', 'col10'=> 'col-sm-10', 'col11'=> 'col-sm-11', 'col12'=> 'col-sm-12' ],
                'md'=>[ 'none'=> '', 'col1'=> 'col-md-1', 'col2'=> 'col-md-2', 'col3'=> 'col-md-3', 'col4'=> 'col-md-4', 'col5'=> 'col-md-5', 'col6'=> 'col-md-6', 'col7'=> 'col-md-7', 'col8'=> 'col-md-8', 'col9'=> 'col-md-9', 'col10'=> 'col-md-10', 'col11'=> 'col-md-11', 'col12'=> 'col-md-12' ],
                'lg'=>[ 'none'=> '', 'col1'=> 'col-lg-1', 'col2'=> 'col-lg-2', 'col3'=> 'col-lg-3', 'col4'=> 'col-lg-4', 'col5'=> 'col-lg-5', 'col6'=> 'col-lg-6', 'col7'=> 'col-lg-7', 'col8'=> 'col-lg-8', 'col9'=> 'col-lg-9', 'col10'=> 'col-lg-10', 'col11'=> 'col-lg-11', 'col12'=> 'col-lg-12' ],
                'xl'=>[ 'none'=> '', 'col1'=> 'col-xl-1', 'col2'=> 'col-xl-2', 'col3'=> 'col-xl-3', 'col4'=> 'col-xl-4', 'col5'=> 'col-xl-5', 'col6'=> 'col-xl-6', 'col7'=> 'col-xl-7', 'col8'=> 'col-xl-8', 'col9'=> 'col-xl-9', 'col10'=> 'col-xl-10', 'col11'=> 'col-xl-11', 'col12'=> 'col-xl-12' ]
            ],
            'offset'=>[
                'xs'=>[ 'none'=> '', 'offset1'=> 'offset-1', 'offset2'=> 'offset-2', 'offset3'=> 'offset-3', 'offset4'=> 'offset-4', 'offset5'=> 'offset-5', 'offset6'=> 'offset-6', 'offset7'=> 'offset-7', 'offset8'=> 'offset-8', 'offset9'=> 'offset-9', 'offset10'=> 'offset-10', 'offset11'=> 'offset-11' ],
                'sm'=>[ 'none'=> '', 'offset1'=> 'offset-sm-1', 'offset2'=> 'offset-sm-2', 'offset3'=> 'offset-sm-3', 'offset4'=> 'offset-sm-4', 'offset5'=> 'offset-sm-5', 'offset6'=> 'offset-sm-6', 'offset7'=> 'offset-sm-7', 'offset8'=> 'offset-sm-8', 'offset9'=> 'offset-sm-9', 'offset10'=> 'offset-sm-10', 'offset11'=> 'offset-sm-11' ],
                'md'=>[ 'none'=> '', 'offset1'=> 'offset-md-1', 'offset2'=> 'offset-md-2', 'offset3'=> 'offset-md-3', 'offset4'=> 'offset-md-4', 'offset5'=> 'offset-md-5', 'offset6'=> 'offset-md-6', 'offset7'=> 'offset-md-7', 'offset8'=> 'offset-md-8', 'offset9'=> 'offset-md-9', 'offset10'=> 'offset-md-10', 'offset11'=> 'offset-md-11' ],
                'lg'=>[ 'none'=> '', 'offset1'=> 'offset-lg-1', 'offset2'=> 'offset-lg-2', 'offset3'=> 'offset-lg-3', 'offset4'=> 'offset-lg-4', 'offset5'=> 'offset-lg-5', 'offset6'=> 'offset-lg-6', 'offset7'=> 'offset-lg-7', 'offset8'=> 'offset-lg-8', 'offset9'=> 'offset-lg-9', 'offset10'=> 'offset-lg-10', 'offset11'=> 'offset-lg-11' ],
                'xl'=>[ 'none'=> '', 'offset1'=> 'offset-xl-1', 'offset2'=> 'offset-xl-2', 'offset3'=> 'offset-xl-3', 'offset4'=> 'offset-xl-4', 'offset5'=> 'offset-xl-5', 'offset6'=> 'offset-xl-6', 'offset7'=> 'offset-xl-7', 'offset8'=> 'offset-xl-8', 'offset9'=> 'offset-xl-9', 'offset10'=> 'offset-xl-10', 'offset11'=> 'offset-xl-11' ],
            ],
            'margin'=> [ 'inherit'=> '', 'm-0'=> 'm-0', 'm-1'=> 'm-1', 'm-2'=> 'm-2', 'm-3'=> 'm-3', 'm-4'=> 'm-4', 'm-5'=> 'm-5', 'm-auto'=> 'm-auto' ],
            'margin-top'=> [ 'inherit'=> '', 'mt-0'=> 'mt-0', 'mt-1'=> 'mt-1', 'mt-2'=> 'mt-2', 'mt-3'=> 'mt-3', 'mt-4'=> 'mt-4', 'mt-5'=> 'mt-5', 'mt-auto'=> 'mt-auto' ],
            'margin-right'=> [ 'inherit'=> '', 'mr-0'=> 'mr-0', 'mr-1'=> 'mr-1', 'mr-2'=> 'mr-2', 'mr-3'=> 'mr-3', 'mr-4'=> 'mr-4', 'mr-5'=> 'mr-5', 'mr-auto'=> 'mr-auto' ],
            'margin-bottom'=> [ 'inherit'=> '', 'mb-0'=> 'mb-0', 'mb-1'=> 'mb-1', 'mb-2'=> 'mb-2', 'mb-3'=> 'mb-3', 'mb-4'=> 'mb-4', 'mb-5'=> 'mb-5', 'mb-auto'=> 'mb-auto' ],
            'margin-left'=> [ 'inherit'=> '', 'ml-0'=> 'ml-0', 'ml-1'=> 'ml-1', 'ml-2'=> 'ml-2', 'ml-3'=> 'ml-3', 'ml-4'=> 'ml-4', 'ml-5'=> 'ml-5', 'ml-auto'=> 'ml-auto' ],

            'padding'=> [ 'inherit'=> '', 'p-0'=> 'p-0', 'p-1'=> 'p-1', 'p-2'=> 'p-2', 'p-3'=> 'p-3', 'p-4'=> 'p-4', 'p-5'=> 'p-5' ],
            'padding-top'=> [ 'inherit'=> '', 'pt-0'=> 'pt-0', 'pt-1'=> 'pt-1', 'pt-2'=> 'pt-2', 'pt-3'=> 'pt-3', 'pt-4'=> 'pt-4', 'pt-5'=> 'pt-5'],
            'padding-right'=> [ 'inherit'=> '', 'pr-0'=> 'pr-0', 'pr-1'=> 'pr-1', 'pr-2'=> 'pr-2', 'pr-3'=> 'pr-3', 'pr-4'=> 'pr-4', 'pr-5'=> 'pr-5'],
            'padding-bottom'=> [ 'inherit'=> '', 'pb-0'=> 'pb-0', 'pb-1'=> 'pb-1', 'pb-2'=> 'pb-2', 'pb-3'=> 'pb-3', 'pb-4'=> 'pb-4', 'pb-5'=> 'pb-5'],
            'padding-left'=> [ 'inherit'=> '', 'pl-0'=> 'pl-0', 'pl-1'=> 'pl-1', 'pl-2'=> 'pl-2', 'pl-3'=> 'pl-3', 'pl-4'=> 'pl-4', 'pl-5'=> 'pl-5'],

            'borderColorClass'=> ['primary'=>'border-primary', 'secondary'=>'border-secondary', 'success'=>'border-success', 'danger'=>'border-danger', 'warning'=>'border-warning', 'info'=>'border-info', 'light'=>'border-light', 'dark'=>'border-dark', 'default'=>''],

            'verticalAlignment' => ['inherit'=>'', 'top' => 'align-top','middle' => 'align-middle','bottom' => 'align-bottom'],
            'textAlignment' => ['inherit'=>'', 'left' => 'text-left','center' => 'text-center','right' => 'text-right']
        ];
    }
}
