layui.define(['config', 'jquery'], function (exports) {
    let $ = layui.jquery;
    layui.link('/layuiadmin/plugins/codemirror/lib/codemirror.css');
    layui.link('/layuiadmin/plugins/codemirror/theme/monokai.css');
    load_js('/layuiadmin/plugins/codemirror/lib/codemirror.js', function () {
        load_js('/layuiadmin/plugins/codemirror/mode/javascript/javascript.js', function () {

            // myTextarea = document.getElementById("bottom_code");
            // let editor = CodeMirror.fromTextArea(myTextarea, {});

            let editor = [];

            $(".code_mirror").each(function (index, elem) {
                editor[index] = CodeMirror.fromTextArea(elem, {
                    theme: 'monokai',
                });
                editor[index].on("blur", function () {
                    elem.value = editor[index].getValue();
                });
            });
        });
    })
    exports('code_mirror', {});
});
