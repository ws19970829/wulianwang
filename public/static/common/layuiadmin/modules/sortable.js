layui.define([], function (exports) {
    function init(el, options) {
        load_js('/layuiadmin/plugins/sortable/Sortable.min.js', function () {
            new Sortable(el, options);
        })
    }

    exports('sortable', {
        init: init
    });
});
