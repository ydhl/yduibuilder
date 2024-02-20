;

/**
 * 把yd-tree-select的select渲染成树形下拉框
 * @returns {undefined}
 */
function yd_dynamic_select_render() {

    var render = function (el) {
        if ( $(el).attr("yd-component") ) return;

        $(el).attr("yd-component","yd-dynamic-select");

        var args = {language: "zh-CN"};

        args["palaceholder"] = $(el).attr("data-placeholder");
        args["width"] = "100%";
        // args["allowClear"]=true;
        args["debug"]=true;
        if ($(el).attr("data-allow-create")) {
            args["tags"] = true;
        }

        var id = $(el).attr("id");
        if( ! id){
            id = YDJS.uuid(16,16,"dynamic_select");
        }
        $(el).attr("id",id);

        var labelName = $(el).attr("data-label") || "text";
        var searchName = $(el).attr("data-query-name") || "query";
        var getArgs = $(el).attr("data-query-callback");
        var callback = $(el).attr("data-callback");
        var url = $(el).attr("data-url");

        var template = $(el).attr("data-template") || "{{" + labelName + "}}";

        var matches = template.match(/{{[_A-Za-z0-9]+}}/g);
        for (var i = 0; i < matches.length; i++) {
            var word = matches[i].replace(/{|}/g, "");
            var reg = new RegExp(matches[i], "g");
            template = template.replace(reg, '"+repo.' + word + '+"');
        }
        template = '"' + template + '"';

        if (window[ $(el).attr("data-datas") ]) {
            args["data"] = window[ $(el).attr("data-datas") ];

        } else if ($(el).attr("data-url")) {

            args["ajax"] = {
                url: typeof (url) == "string" ? url : url(),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var args;
                    if (!getArgs) {
                        args = {};
                    } else {
                        args = typeof (getArgs) == "object" ? getArgs : window[getArgs](el);
                    }
                    args[searchName] = params.term;
                    args["page"] = params.page;

                    return args;
                },
                processResults: function (data, page) {
                    return {
                        results: data
                    };
                },
                cache: true
            };
        }

        args["escapeMarkup"] = function (markup) {
            return markup;
        };
        args["minimumInputLength"] = parseInt( $(el).attr("data-input-length")) || 0;
        args["templateSelection"] = function (repo) {
            return repo[labelName];
        };

        args["templateResult"] = function (repo) {
            if (repo.loading)
                return repo.text;
            var markup = eval(template);
            return markup;
        };


// console.log(args);
        $("#"+id).select2(args);

        //select2:select 才能通过e.params.data 获取数据
        $(el).on('select2:select', function (evt) {
            if (!callback)
                return;
            var data = evt.params ? evt.params.data : null;
            if (window[callback]) {
                window[callback](data, evt);
            }
        });

        $(el).on('select2:clear', function (evt) {
            if (!callback)
                return;

            if (window[callback]) {
                window[callback](null, evt);
            }
        });

    };


    //渲染
    $(".yd-dynamic-select").each(function (idx, el) {
        render(el);

    });
}