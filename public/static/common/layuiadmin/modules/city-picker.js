layui.define(function (exports) {
    let $ = layui.jquery;

    layui.link(layui.cache.base + '../plugins/city-picker/city-picker.css');
    load_js(layui.cache.base + '../plugins/city-picker/city-picker.data.js', function () {
        load_js(layui.cache.base + '../plugins/city-picker/city-picker.js', function () {
                $(".city-picker").citypicker();
        });
    });

    exports('city-picker', {});
});
