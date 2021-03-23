/**

 @Name：layuiAdmin iframe版全局配置
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL（layui付费产品协议）

 */

layui.define(['table', 'form', 'sortable'], function (exports) {
    let table = layui.table;
    let $ = layui.jquery;
    let where = {};
    let form = layui.form;
    let sortable = layui.sortable;

    // 表格排序
    let sortable_init = function () {
        let lay_table_tbody = $("#data_table").parent().find('.layui-table-view tbody').get(0);
        sortable.init(lay_table_tbody, {
            handle: '.handle',
            onEnd: function () {
                let id = [];
                let sort = [];
                $('.handle').each(
                    function () {
                        let $this = $(this);
                        id.push($this.attr('data-id'));
                        sort.push($this.attr('data-sort'));
                    }
                );
                let vars = {id: id, sort: sort};
                $.post('sortable', vars, function (res) {
                    layer.msg(res.msg, {time: 1200})
                });
            },
        });
    }

    form.on('switch(is_valid)', function (obj) {
        let data = {};
        data.pk = $(obj.elem).attr('data-pk');//主键
        data.id = $(obj.elem).attr('data-id');//主键值
        data.field = $(obj.elem).attr('name');//修改的字段
        if (obj.elem.checked) {
            $.post('set_valid', data, function (res) {
                if (res.code) {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                    });
                } else {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                        obj.elem.checked = false;
                        form.render();
                    });
                }
            });
        } else {
            $.post('set_invalid', data, function (res) {
                if (res.code) {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                    });
                } else {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                        obj.elem.checked = true;
                        form.render();
                    });
                }
            });
        }
    });

    let fn = {
        // 简单搜索
        form_search: function () {
            where = serialize_form('#form_search');
            table.reload('data_table', {
                where: where
            });
        },
        // 高级搜索
        adv_search: function () {
            where = serialize_form('#form_adv_search');
            where.page = 1;
            table.reload('data_table', {
                where: where
            });
        },
        reload: function () {
            table.reload('data_table', {
                where: where
            });
        },
        // 设置可用
        set_valid: function (data, tr) {
            $.post('set_valid', data, function (res) {
                if (res.code) {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                        let $btn = $("a[lay-event='set_valid']", tr);

                        $btn.attr('lay-event', 'set_invalid');
                        $btn.html('禁用');
                        $btn.addClass('layui-btn-danger');

                    });
                } else {
                    layer.msg(res.msg);
                }
            });
        },
        // 设置禁用
        set_invalid: function (data, tr) {
            $.post('set_invalid', data, function (res) {
                if (res.code) {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {

                        $btn = $("a[lay-event='set_invalid']", tr);
                        $btn.attr('lay-event', 'set_valid');
                        $btn.html('启用');
                        $btn.removeClass('layui-btn-danger');

                        console.log($("a[lay-event='set_invalid']").html());

                    });
                } else {
                    layer.msg(res.msg);
                }
            });
        },
        sort_up: function (data) {
            $.post('sort_up', data, function (res) {
                if (res.code) {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                        reload();
                    });
                } else {
                    layer.msg(res.msg);
                }
            });
        },
        sort_down: function (data) {
            $.post('sort_down', data, function (res) {
                if (res.code) {
                    layer.msg(res.msg, {
                        time: 1200
                    }, function () {
                        reload();
                    });
                } else {
                    layer.msg(res.msg);
                }
            });
        },
    }

    win_exp(fn);

    let page = {
        limits: [10, 20, 50, 100, 1000],
        layout: ['prev', 'page', 'next', 'skip', 'limit', 'count'],
    };

    let response = {
        statusCode: 1 //规定成功的状态码，默认：0
    };

    let loading = false;

    let url = location.href;

    let elem = '#data_table';

    let cols = [];

    let config = {
        loading: loading,
        autoSort: false,
        elem: elem,
        response: response,
        cols: cols,
        page: page,
        url: url,
        done: sortable_init,
    }

    function render() {
        table.render(config)
    }

    table.on('sort(data_table)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        console.log(obj.field); //当前排序的字段名
        console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
        console.log(this); //当前排序的 th 对象

        //尽管我们的 table 自带排序功能，但并没有请求服务端。
        //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
        if (obj.type === null) {
            sort = null;
        } else {
            sort = obj.field + ' ' + obj.type;
        }
        where._sort = sort;
        table.reload('data_table', {
            where: where
        });
        //layer.msg('服务端排序。order by ' + obj.field + ' ' + obj.type);
    });

    table.on('tool(data_table)', function (obj) {
        let data = obj.data;
        //获得当前行数据
        let layEvent = obj.event;
        //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
        let tr = obj.tr;
        //获得当前行 tr 的DOM对象

        window[layEvent](data, tr);
    });

    exports('data_table', {
        url: url,
        config: config,
        render: render,
    })
})
    