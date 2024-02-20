;

function yd_ts_remove_item(selectId, id, object) {
    $("#"+selectId).find("option[value="+id+"]:selected").remove();
    $(object).parents(".ts-select-item").remove();
}
function yd_ts_remove_all(selectId,  panelid) {
    $("#"+selectId).find("option").remove();
    $("#"+panelid+"panel").find(".ts-select-item").remove();
}
/**
 * 把yd-tree-select的select渲染成树形下拉框
 * @returns {undefined}
 */
function yd_tree_select_render() {

	function re_init_data(data, def){
		if(!def) return data;
		var res = new Array();
		for(var key in data){
			var d = data[key];
			if(def.indexOf(d["id"]+"") != -1 || def.indexOf(parseInt(d["id"])) != -1){
				d["state"] ={ opened:true, selected:true };
			}
			res.push(d);
		}
		return res;
	}

    /**
     * 返回节点的完整路径，如果是在复选情况下，如果子级节点都全部选中了，那么就只显示父节点
     *
     * @param jstreeObj
     * @param node
     * @param showCategory 是否显示树上的归类节点
     * @returns {string|*}
     */
	function get_full_name(jstreeObj, node, showCategory){
	    var names = [];
	    // console.log(node);
	    if (node.parents.length===1)return node.text;
	    for(var i=node.parents.length-1; i>=0;i--){
	        if ( node.parents[i]==="#" )continue;
	        var pnode = jstreeObj.get_node(node.parents[i]);
	        if (pnode.data && pnode.data.tree_type==="category")continue;//分类不是有效的节点，不显示
            names.push( pnode.text );
        }
        names.push(node.text);
        return names.join("/");
    }

    function format_item(selectId, id, name){
	    var tpl = "<div class='d-inline-block border rounded align-self-center pl-1 pr-1 ts-select-item'>" +
            "<span class='nowrap text-justify' style='display: inline-block;min-width: 50px;text-align-last: justify'>"
            +name+"</span><span style='padding: 3px 5px;cursor: pointer;color: red' onclick='yd_ts_remove_item(\""+selectId+"\",\""+id+"\",this)'>×</span></div>";
	    return tpl;
    }

    var render = function (el) {
        var selectDom = $(el);

        if ( selectDom.attr("yd-component") ) return;

        selectDom.attr("yd-component","yd-tree-select");

        var selectId  = $(el).attr("id")||YDJS.uuid(16,16,"tree-select-");
        $(el).prop("id", selectId);
        var datas = window[ selectDom.attr("data-datas") ];
        var url = selectDom.attr("data-url");
        var _default = [];
        var callback = selectDom.attr("data-callback");
        var argcallback = selectDom.attr("data-query-callback");
        var placeholder = selectDom.attr("data-placeholder")||"";
        var css = selectDom.attr("data-input-class");
        var multiple = selectDom.attr("multiple");
        var disable_cascade = selectDom.attr("data-disable-cascade");
        var query_arg = selectDom.attr("data-query-name")||"query";
        var selectedOptions = [];
        $(el).find("option:selected").each(function(idx, el){
            if ( ! $(el).val() )return;
            selectedOptions.push( $(el).text() );
            _default.push( $(el).val() );
        });
        var inputQuery = null;//输入框输入后进行查询的内容，查询完后会立即情况


        //1. hide select and show input control
        selectDom.css("display", "none");

        var inputId = selectId + (Math.random() * 1000).toFixed();
        var input = "<div id='" + inputId + "panel' class='border " + css + " p-0 pl-2 d-flex align-items-center flex-wrap' style='height: auto;min-height: 38px'>";
        for(var i=0; i<selectedOptions.length; i++){
            input += format_item(selectId, _default[i], selectedOptions[i]);
        }
        input += "<input type='text' id='" + inputId + "' placeholder='"
            +(placeholder||"请选择")+"' class='yd-tree-select-input flex-grow-1 m-0 border-0'/><span class='text-muted ts-remove-all pr-1' style='padding: 3px 5px;cursor: pointer' onclick='yd_ts_remove_all(\""+selectId+"\",\""+inputId+"\")'>×</span></div>";
        selectDom.after(input);

        //2. insert tree panel
        var treePanelId = selectId + (Math.random() * 10000).toFixed();
        var treePanel = "<div id='" + treePanelId + "' class='yd-tree-select-panel' style='z-index:999;display:none;background:#fff;border:1px solid #ccc;max-height:300px;overflow:auto'>暂无数据</div>";
        selectDom.after(treePanel);

        var plugins = ["search"];
        if (multiple) {
            plugins.push("checkbox");
        }

        var data = function (obj, cb) {
            if (datas) {
                cb.call(this, re_init_data(datas, _default));
            }
        };
        if (url){
            data = {
                'url': url,
                'data': function(node){
                    var args = {};
                    if (argcallback){
                        args = window[argcallback]();
                    };
                    if(inputQuery){
                        args[query_arg] = inputQuery;
                    }else{
                        args["parent_id"] = node.id=="#"?"":node.id;
                    }
                    return args
                }
            };

            //当是通过url动态加载节点时，默认显示的数据可能在加载的数据中，这是要标记其为选中状态
            $("#" + treePanelId).on('load_node.jstree', function(node, status){
                var defaultSelected = selectDom.find("option:selected");
                var defaultSelectedId = [];
                for(var i=0; i<defaultSelected.length; i++){
                    defaultSelectedId.push ( $(defaultSelected[i]).val() );
                }
                $("#" + treePanelId).jstree(true).select_node(defaultSelectedId,true,true);
            });
        }

        //3. instance tree, bind tree change event, set default value
        $('#' + treePanelId).jstree({
            "plugins": plugins,
            'core': {
                "multiple": multiple ? true : false,
                'data': data
            },
            "checkbox":{"three_state":disable_cascade ? false : true}
        });

        var selectEvent = function (e, data) {
            // //防止第一次加载是触发select_node.jstree事件
            // if( ! $(el).attr("data-is-loaded")){
            //     $(el).attr("data-is-loaded","1");
            //     return;
            // }

            var i, j, r = [], options = "", selected = [];
            var values = $("#" + treePanelId).jstree(true).get_selected (true);

            //多选模式并且是级联模式下 如果下级全部选中，则不显示出下级，只显示上级
            var node_parent_check_all = {};//被全部选中的某个父级的所有子节点，这些子节点不显示在界面上
            if(multiple && ! disable_cascade){
                for (i = 0; i < values.length;  i++) {
                    if (values[i].children.length > 0 && values[i].state.selected) {//父节点是选择状态
                        for(j=0; j<values[i].children_d.length; j++){
                            node_parent_check_all[ values[i].children_d[j] ] = true;
                        }
                    }
                }
            }

            for (i = 0;i < values.length;  i++) {
                if (values[i].data && values[i].data["tree_type"]==="category") {
                    options += "<option selected='selected' value='" + values[i].id + "'>" + values[i].text + "</option>";
                    continue;
                }

                selected.push(values[i]);
                options += "<option selected='selected' value='" + values[i].id + "'>" + values[i].text + "</option>";
            }
            yd_ts_remove_all(selectId,  inputId)
            selectDom.append(options);
            // if(multiple){
            //     selectDom.append(options);
            // }else{
            //     selectDom.append(options);
            // }
            selectDom.find('option:selected').each(function (idx, el){
                r.push(format_item(selectId, $(el).prop('value'), $(el).text()));
            })
            $('#' + inputId).before( r ? r.join('') : "");
            // $("#"+inputId).blur();
            // $("#"+inputId).focus();

            if(callback && window[callback]){
                window[callback](selected, $("#" + treePanelId));
            }
        };

        $("#" + treePanelId).on('select_node.jstree', selectEvent);
        $("#" + treePanelId).on('deselect_node.jstree', selectEvent);
        if (url && _default){
            //如果输出的dom中已经有默认值了，那么tree打开的时候把这些默认值默认选择，由于是加载就有的，不是人工选择的，这是不触发change时间
            setTimeout(function () {
                $("#" + treePanelId).jstree(true).select_node (_default, true);
            },1000);

        }


        $("#"+inputId).keyup(function(){
            if(url){
                inputQuery = $(this).val();
                $('#' + treePanelId).jstree(true).refresh();
                inputQuery = null;
            }else{
                $('#'+treePanelId).jstree(true).search($(this).val());
            }
        });


        //4. bind trigger event

        $("#" + inputId).on("focus", function () {
            if (window["tree_select_popper"]) {
                $(window["tree_select_popper"].popper).hide();
                window["tree_select_popper"].destroy();
            }

            var reference = this;
            var popper = document.querySelector('#' + treePanelId);
            $(popper).show();
            var popperInstance = new Popper(reference, popper, {
                placement: "bottom-start",
                positionFixed: true,
                modifiers: {
                    preventOverflow: {enabled: false}
                }
            });
            window["tree_select_popper"] = popperInstance;
        });


    };

    //渲染所有的tree select
    $(".yd-tree-select").each(function (idx, el) {
        render(el);

    });

     YDJS.event_bind("click", "body", function (event) {
        if ($(event.toElement).hasClass("yd-tree-select-input") || $(event.toElement).parents(".yd-tree-select-panel").length>0)
            return true;//点击的是树本身
        if (window["tree_select_popper"]) {
            $(window["tree_select_popper"].popper).hide();
            window["tree_select_popper"].destroy();
        }
    });

}
