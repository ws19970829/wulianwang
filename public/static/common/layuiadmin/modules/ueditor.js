layui.define(function (exports) {
    let $ = layui.jquery;
    window.editor = {};

    load_js(layui.cache.base + '../plugins/ueditor/ueditor.config.js', function () {
        load_js(layui.cache.base + '../plugins/ueditor/ueditor.all.js', function () {
            load_js(layui.cache.base + '../plugins/ueditor/lang/zh-cn/zh-cn.js', function () {
                $(".ueditor").each(function () {
                    let $id = $(this).attr('data');
                    editor[$id] = UE.getEditor($id);
                });
            });
        });
    });

    exports('ueditor', {});
});
