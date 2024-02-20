<?php
namespace app\vendor\css;
use yangzie\YZE_FatalException;
use function yangzie\__;

abstract class Css_Factory{
    /**
     * 对getDefine里面定义的预设演示进行对应的css转换
     * @param $version
     * @return mixed
     */
    public abstract function cssTranslat($version);

    /**
     * UI Builder定义的所有的预设样式定义，每种UI库根据自身的特点对这些预设样式进行对应翻译，如果有对应的css class，则用之
     * 如果没有，则翻译成对应的style
     * @return array
     */
    public static function getDefine(){
        return [
            'themeColor'=> ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'default'],
            'foregroundTheme'=> ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'default'],
            'backgroundTheme'=> ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'default'],

            'buttonSizing'=> [ 'lg'=> __('Large'), 'default'=> __('Default'), 'sm'=> __('Small'), 'xs'=> __('X-Small') ],
            'paginationSizing'=> [ 'lg'=> __('Large'), 'default'=> __('Default'), 'sm'=> __('Small')],
            'dropdownSizing'=> [ 'lg'=> __('Large'), 'default'=> __('Default'), 'sm'=> __('Small'), 'xs'=> __('X-Small') ],
            'formSizing'=> [ 'lg'=> __('Large'), 'default'=> __('Default'), 'sm'=> __('Small')],

            'container'=> [ 'fluid'=> __('Fluid'), 'container'=> __('Container'), 'none'=> __('Default') ],
            /*具体的ui框架在翻译row时同container一起翻译*/
            'row'=> [ 'none'=> __('None'), 'row'=> __('Row') ],
            'grid'=> [ 'none'=> __('None'), 'col1'=> __('1/12 col'), 'col2'=> __('2/12 col'), 'col3'=> __('3/12 col'), 'col4'=> __('4/12 col'), 'col5'=> __('5/12 col'), 'col6'=> __('6/12 col'), 'col7'=> __('7/12 col'), 'col8'=> __('8/12 col'), 'col9'=> __('9/12 col'), 'col10'=> __('10/12 col'), 'col11'=> __('11/12 col'), 'col12'=> __('full col') ],
            'offset'=> [ 'none'=> __('None'), 'offset1'=> __('1/12 offset'), 'offset2'=> __('2/12 offset'), 'offset3'=> __('3/12 offset'), 'offset4'=> __('4/12 offset'), 'offset5'=> __('5/12 offset'), 'offset6'=> __('6/12 offset'), 'offset7'=> __('7/12 offset'), 'offset8'=> __('8/12 offset'), 'offset9'=> __('9/12 offset'), 'offset10'=> __('10/12 offset'), 'offset11'=> __('11/12 offset') ],

            'margin'=> [ 'inherit'=> __('Inherit'), 'm-0'=> __('None'), 'm-1'=> __('spacer * .25'), 'm-2'=> __('spacer * .5'), 'm-3'=> __('spacer'), 'm-4'=> __('spacer * 1.5'), 'm-5'=> __('spacer * 3'), 'm-auto'=> __('Auto') ],
            'margin-top'=> [ 'inherit'=> __('Inherit'), 'mt-0'=> __('None'), 'mt-1'=> __('spacer * .25'), 'mt-2'=> __('spacer * .5'), 'mt-3'=> __('spacer'), 'mt-4'=> __('spacer * 1.5'), 'mt-5'=> __('spacer * 3'), 'mt-auto'=> __('Auto') ],
            'margin-right'=> [ 'inherit'=> __('Inherit'), 'mr-0'=> __('None'), 'mr-1'=> __('spacer * .25'), 'mr-2'=> __('spacer * .5'), 'mr-3'=> __('spacer'), 'mr-4'=> __('spacer * 1.5'), 'mr-5'=> __('spacer * 3'), 'mr-auto'=> __('Auto') ],
            'margin-bottom'=> [ 'inherit'=> __('Inherit'), 'mb-0'=> __('None'), 'mb-1'=> __('spacer * .25'), 'mb-2'=> __('spacer * .5'), 'mb-3'=> __('spacer'), 'mb-4'=> __('spacer * 1.5'), 'mb-5'=> __('spacer * 3'), 'mb-auto'=> __('Auto') ],
            'margin-left'=> [ 'inherit'=> __('Inherit'), 'ml-0'=> __('None'), 'ml-1'=> __('spacer * .25'), 'ml-2'=> __('spacer * .5'), 'ml-3'=> __('spacer'), 'ml-4'=> __('spacer * 1.5'), 'ml-5'=> __('spacer * 3'), 'ml-auto'=> __('Auto') ],

            'padding'=> [ 'inherit'=> __('Inherit'), 'p-0'=> __('None'), 'p-1'=> __('spacer * .25'), 'p-2'=> __('spacer * .5'), 'p-3'=> __('spacer'), 'p-4'=> __('spacer * 1.5'), 'p-5'=> __('spacer * 3') ],
            'padding-top'=> [ 'inherit'=> __('Inherit'), 'pt-0'=> __('None'), 'pt-1'=> __('spacer * .25'), 'pt-2'=> __('spacer * .5'), 'pt-3'=> __('spacer'), 'pt-4'=> __('spacer * 1.5'), 'pt-5'=> __('spacer * 3')],
            'padding-right'=> [ 'inherit'=> __('Inherit'), 'pr-0'=> __('None'), 'pr-1'=> __('spacer * .25'), 'pr-2'=> __('spacer * .5'), 'pr-3'=> __('spacer'), 'pr-4'=> __('spacer * 1.5'), 'pr-5'=> __('spacer * 3')],
            'padding-bottom'=> [ 'inherit'=> __('Inherit'), 'pb-0'=> __('None'), 'pb-1'=> __('spacer * .25'), 'pb-2'=> __('spacer * .5'), 'pb-3'=> __('spacer'), 'pb-4'=> __('spacer * 1.5'), 'pb-5'=> __('spacer * 3')],
            'padding-left'=> [ 'inherit'=> __('Inherit'), 'pl-0'=> __('None'), 'pl-1'=> __('spacer * .25'), 'pl-2'=> __('spacer * .5'), 'pl-3'=> __('spacer'), 'pl-4'=> __('spacer * 1.5'), 'pl-5'=> __('spacer * 3')],

            'borderColorClass'=> ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'default'],

            'verticalAlignment' => ['inherit'=>__('Inherit'), 'top' => __('Top'),'middle' => __('Middle'),'bottom' => __('Bottom')],
            'textAlignment' => ['inherit'=>__('Inherit'), 'left' => __('Left'),'center' => __('Center'),'right' => __('Right')],
            /* 设备的尺寸，不需要ui框架翻译*/
            'deviceSize' => ['xs'=>__('Extra small (<576px)'), 'sm' => __('Small (≥576px)'),'md' => __('Medium (≥768px)'),'lg' => __('Large (≥992px)'),'xl' => __('Extra large (≥1200px)')],
            'outlineStyle' => ['inherit'=>__('Inherit'), 'auto' => __('Auto'),'none' => __('None'),'dotted' => __('Dotted'),'dashed' => __('Dashed'),'solid' => __('Solid'),'double' => __('Double'),'ridge' => __('Ridge'),'groove' => __('Groove'),'inset' => __('Inset'),'outset' => __('Outset')],
            'fontSize' => ['default','unit'],
            'spacer' => ['default','unit'],
        ];
    }

    public static function getFactory($ui): Css_Factory {
        $class = 'app\\vendor\\css\\'.ucfirst(strtolower($ui)).'_Factory';
        if (!class_exists($class, true)) throw new YZE_FatalException("{$class} not found");
        return new $class();
    }
}
