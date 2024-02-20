<?php
namespace app\vendor\css;
class Vant_factory extends Css_Factory{
    public function cssTranslat($version){
        return [
            'themeColor'=> ['primary'=>'var(--van-primary-color)', 'secondary'=>'var(--van-gray-8)', 'success'=>'var(--van-success-color)', 'danger'=>'var(--van-danger-color)', 'warning'=>'var(--van-warning-color)', 'info'=>'var(--van-text-link-color)', 'light'=>'var(--van-gray-3)', 'dark'=>'var(--van-gray-8)', 'default'=>''],
            'foregroundTheme'=> ['primary'=>'van-text-primary', 'secondary'=>'van-text-secondary', 'success'=>'van-text-success', 'danger'=>'van-text-danger', 'warning'=>'van-text-warning', 'info'=>'van-text-info', 'light'=>'van-text-light', 'dark'=>'van-text-dark', 'default'=>''],
            'backgroundTheme'=> ['primary'=>'van-bg-primary', 'secondary'=>'van-bg-secondary', 'success'=>'van-bg-success', 'danger'=>'van-bg-danger', 'warning'=>'van-bg-warning', 'info'=>'van-bg-info', 'light'=>'van-bg-light', 'dark'=>'van-bg-dark', 'default'=>''],
            'buttonSizing'=> [ 'lg'=> 'van-button--large', 'default'=> 'van-button--normal', 'sm'=> 'van-button--small','xs'=>'van-button--mini'],

            'grid'=>[ 'none'=> '', 'col1'=> 'van-col--2', 'col2'=> 'van-col--4', 'col3'=> 'van-col--6', 'col4'=> 'van-col--8', 'col5'=> 'van-col--10', 'col6'=> 'van-col--12', 'col7'=> 'van-col--14', 'col8'=> 'van-col--16', 'col9'=> 'van-col--18', 'col10'=> 'van-col--20', 'col11'=> 'van-col--22', 'col12'=> 'van-col--24' ],
            'offset'=>[ 'none'=> '', 'offset1'=> 'van-col--offset-2', 'offset2'=> 'van-col--offset-4', 'offset3'=> 'van-col--offset-6', 'offset4'=> 'van-col--offset-8', 'offset5'=> 'van-col--offset-10', 'offset6'=> 'van-col--offset-12', 'offset7'=> 'van-col--offset-14', 'offset8'=> 'van-col--offset-16', 'offset9'=> 'van-col--offset-18', 'offset10'=> 'van-col--offset-20', 'offset11'=> 'van-col--offset-22' ],

            'margin'=> [ 'inherit'=> '', 'm-0'=> 'van-m-0', 'm-1'=> 'van-m-1', 'm-2'=> 'van-m-2', 'm-3'=> 'van-m-3', 'm-4'=> 'van-m-4', 'm-5'=> 'van-m-5', 'm-auto'=> 'van-m-auto' ],
            'margin-top'=> [ 'inherit'=> '', 'mt-0'=> 'van-mt-0', 'mt-1'=> 'van-mt-1', 'mt-2'=> 'van-mt-2', 'mt-3'=> 'van-mt-3', 'mt-4'=> 'van-mt-4', 'mt-5'=> 'van-mt-5', 'mt-auto'=> 'van-mt-auto' ],
            'margin-right'=> [ 'inherit'=> '', 'mr-0'=> 'van-mr-0', 'mr-1'=> 'van-mr-1', 'mr-2'=> 'van-mr-2', 'mr-3'=> 'van-mr-3', 'mr-4'=> 'van-mr-4', 'mr-5'=> 'van-mr-5', 'mr-auto'=> 'van-mr-auto' ],
            'margin-bottom'=> [ 'inherit'=> '', 'mb-0'=> 'van-mb-0', 'mb-1'=> 'van-mb-1', 'mb-2'=> 'van-mb-2', 'mb-3'=> 'van-mb-3', 'mb-4'=> 'van-mb-4', 'mb-5'=> 'van-mb-5', 'mb-auto'=> 'van-mb-auto' ],
            'margin-left'=> [ 'inherit'=> '', 'ml-0'=> 'van-ml-0', 'ml-1'=> 'van-ml-1', 'ml-2'=> 'van-ml-2', 'ml-3'=> 'van-ml-3', 'ml-4'=> 'van-ml-4', 'ml-5'=> 'van-ml-5', 'ml-auto'=> 'van-ml-auto' ],

            'padding'=> [ 'inherit'=> '', 'p-0'=> 'van-p-0', 'p-1'=> 'van-p-1', 'p-2'=> 'van-p-2', 'p-3'=> 'van-p-3', 'p-4'=> 'van-p-4', 'p-5'=> 'van-p-5' ],
            'padding-top'=> [ 'inherit'=> '', 'pt-0'=> 'van-pt-0', 'pt-1'=> 'van-pt-1', 'pt-2'=> 'van-pt-2', 'pt-3'=> 'van-pt-3', 'pt-4'=> 'van-pt-4', 'pt-5'=> 'van-pt-5'],
            'padding-right'=> [ 'inherit'=> '', 'pr-0'=> 'van-pr-0', 'pr-1'=> 'van-pr-1', 'pr-2'=> 'van-pr-2', 'pr-3'=> 'van-pr-3', 'pr-4'=> 'van-pr-4', 'pr-5'=> 'van-pr-5'],
            'padding-bottom'=> [ 'inherit'=> '', 'pb-0'=> 'van-pb-0', 'pb-1'=> 'van-pb-1', 'pb-2'=> 'van-pb-2', 'pb-3'=> 'van-pb-3', 'pb-4'=> 'van-pb-4', 'pb-5'=> 'van-pb-5'],
            'padding-left'=> [ 'inherit'=> '', 'pl-0'=> 'van-pl-0', 'pl-1'=> 'van-pl-1', 'pl-2'=> 'van-pl-2', 'pl-3'=> 'van-pl-3', 'pl-4'=> 'van-pl-4', 'pl-5'=> 'van-pl-5'],

            'borderColorClass'=> ['primary'=>'van-border-primary', 'secondary'=>'van-border-secondary', 'success'=>'van-border-success', 'danger'=>'van-border-danger', 'warning'=>'van-border-warning', 'info'=>'van-border-info', 'light'=>'van-border-light', 'dark'=>'van-border-dark', 'default'=>''],

            'verticalAlignment' => ['inherit'=>'', 'top' => 'van-align-top','middle' => 'van-align-middle','bottom' => 'van-align-bottom'],
            'textAlignment' => ['inherit'=>'', 'left' => 'van-text-left','center' => 'van-text-center','right' => 'van-text-right']
        ];
    }
}
