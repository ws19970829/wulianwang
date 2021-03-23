/**

 @Name：layuiAdmin 公共业务
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL

 */

layui.define(['config', 'form', 'layer', 'laydate', 'upload', 'jquery', 'element'], function(exports) {
    let $ = layui.$;
    let config = layui.config;
    let layer = layui.layer;
    let laydate = layui.laydate;
    let form = layui.form;

    var fn = {
        popup_open: function(url, w, h) {
            var index = layer.open({
                skin: 'td-popup',
                type: 2,
                title: '1',
                area: [w + 'px', h + 'px'],
                shade: 0.6,
                closeBtn: 1,
                shadeClose: true,
                scrollbar: false,
                content: [url, 'no']
            });
            if (is_mobile()) {
                layer.full(index);
            }
        },
        popup_full: function(url) {
            var index = layer.open({
                type: 2,
                title: '1',
                area: ['100%', '100%'],
                shade: 0.6,
                closeBtn: 1,
                shadeClose: true,
                scrollbar: false,
                content: [url, 'no']
            });
            layer.full(index);
        },
        send_form: function(from_id, post_url, callback) {
            check_form(from_id, function(res) {
                if (res.status) {
                    var vars = $("#" + from_id).serialize();
                    $.ajax({
                        type: "POST",
                        url: post_url,
                        data: vars,
                        dataType: "json",
                        success: function(data) {
                            if (typeof(callback) === 'function') {
                                callback(data);
                                return false;
                            }
                        }
                    });
                } else {
                    layer.msg(res.info);
                    res.dom.focus();
                    return false;
                }
            });
            return false;
        },
        init_form: function() {
            $("[td-checked]").each(function() {
                let $this = $(this);
                let checked = $this.attr('td-checked').split('|');
                if ($.inArray($this.val(), checked) !== -1) {
                    $this.attr('checked', 'checked');
                }
            });

            $("[td-selected]").each(function() {
                let $this = $(this);
                let selected = $this.attr('td-selected');
                $this.val(selected);
            });

            form.render();

            $(".image-upload").each(function() {
                let $this = $(this);
                let image_url = $this.find('.image-url').val();

                let html = '';
                if ($this.hasClass('video')) {
                    html = '<a class="del"><i class="iconfont td-close" ></i></a><img class="video" src="' + image_url + '.jpg">';
                } else {
                    html = '<a class="del"><i class="iconfont td-close" ></i></a><img src="' + image_url + '">';
                }

                if (image_url.length > 0) {
                    $this.addClass('active');
                    $this.find('.image').html(html);
                }
            });

            $(".image-upload-multiple").each(function() {
                let $this = $(this);
                let image_list = $this.find('.image-url').val().split('|');
                for (let i in image_list) {
                    if (image_list[i].length > 0) {
                        let html = '<div class="image"><a class="del"><i class="iconfont td-close" ></i></a><img src="' + image_list[i] + '"></div>';
                        $this.find('.image-list').append(html);
                    }
                }
            });

            $(".file-upload").each(function() {
                let $this = $(this);
                let file_url = $this.find('.file-url').val();
                let url = 'file_info';

                let tpl = '<div class="file" sid="{sid}">' +
                    '       <div class="file-info">' +
                    '           <span class="file-icon">{icon}</span>' +
                    '           <span class="file-name">{name}</span>' +
                    '       </div>' +
                    '       <div class="file-close"></div>' +
                    '</div>';

                $.post(url, {
                    sid: file_url
                }, function(res) {
                    if (res.code) {
                        for (let i in res.data) {
                            let html = tpl_parse(tpl, res.data[i]);
                            $this.find('.file-list').append(html);
                        }
                    }
                });
            });

        },
        serialize_form: function(form_id) {
            var obj = {};
            var a = $(form_id).serializeArray();
            $.each(a, function() {
                if (obj[this.name] !== undefined) {
                    if (!obj[this.name].push) {
                        obj[this.name] = [obj[this.name]];
                    }
                    obj[this.name].push(this.value || '');
                } else {
                    obj[this.name] = this.value || '';
                }
            });
            return obj;
        },
        toggle_adv_search: function() {
            $adv_search = $("#adv_search");
            $toggle_icon = $("#toggle_adv_search_icon");
            if ($adv_search.attr("class").indexOf("hidden") < 0) {
                $adv_search.addClass("hidden");
                $toggle_icon.addClass("fa-chevron-down");
                $toggle_icon.removeClass("fa-chevron-up");
            } else {
                $adv_search.removeClass("hidden");
                $toggle_icon.addClass("fa-chevron-up");
                $toggle_icon.removeClass("fa-chevron-down");
            }
        }
    }

    fn.init_form();

    win_exp(fn);

    $('.input-date-time').on('click', function() {

        let lay_key = $(this).attr('lay-key');

        if (lay_key === undefined) {
            laydate.render({
                elem: this,
                type: 'datetime',
                format: 'yyyy-MM-dd HH:mm',
                show: true //直接显示
            });
        }
    });

    $('.input-date').on('click', function() {
        let lay_key = $(this).attr('lay-key');

        if (lay_key === undefined) {
            laydate.render({
                elem: this,
                show: true //直接显示
            });
        }
    });

    $('.input-month').on('click', function() {
        let lay_key = $(this).attr('lay-key');

        if (lay_key === undefined) {
            laydate.render({
                elem: this,
                show: true,
                type: 'month'
            });
        }
    });

    $('.input-date-range').on('click', function() {
        let lay_key = $(this).attr('lay-key');
        if (lay_key === undefined) {
            laydate.render({
                elem: this,
                range: '~',
                format: 'yyyy-MM-dd',
                show: true //直接显示
            });
        }
    });

    $(document).on('click', '.image img', function() {
        $this = $(this);
        html = $this.prop('outerHTML');

        if ($this.hasClass('video')) {
            image_url = $this.attr('src');
            vidoe_url = image_url.substring(0, image_url.length - 3) + 'mp4';
            let html = '<video src="' + vidoe_url + '" controls="controls"></video>';
            $image_preview = $("#image_preview");
            $image_preview.removeClass('hidden');
            $image_preview.html(html);
        } else {
            $image_preview = $("#image_preview");
            $image_preview.removeClass('hidden');
            $image_preview.html(html);
        }

        layer.open({
            isOutAnim: false,
            anim: 5,
            type: 1,
            title: false,
            closeBtn: 0,
            scrollbar: false,
            area: ['100%', '100%'],
            skin: 'layui-layer-nobg image-preview', //没有背景色
            shadeClose: true,
            content: $image_preview
        });
    });

    $(document).on('click', '.image-upload a.del', function() {
        let $image_upload = $(this).closest('.image-upload');
        $image_upload.find('.image-url').val('');
        $image_upload.find('.image').html('');
        $image_upload.removeClass('active');
    });

    $(document).on('click', '.image-upload-multiple a.del', function() {
        let $image_upload = $(this).closest('.image-upload-multiple');

        $(this).closest('.image').remove();

        let img_list = [];
        $image_upload.find('.image-list img').each(function() {
            img_list.push($(this).attr('src'));
        });

        $image_upload.find('.image-url').val(img_list.join('|'));

    });


    $('#image_preview').on('click', function() {
        $image_preview.addClass('hidden');
        layer.closeAll();
    });

    $('#toggle_adv_search').on('click', function() {
        toggle_adv_search();
    });

    $('#close_adv_search').on('click', function() {
        toggle_adv_search();
    });

    $('#submit_adv_search').on('click', function() {
        $('#form_adv_search').submit();
    });

    $('.layui-side a').on('click touchstart', function() {
        var $this = $(this);
        var url = $this.attr("href");
        if (url.length > 0 && (url !== "#")) {
            node = $this.attr("node");
            set_cookie("current_node", node);

            if (url.indexOf('http') !== -1) {
                window.open(url, 'new');
            } else {
                window.open(url, '_self');
            }
        }
    });

    current_node = get_cookie("current_node");
    $current_node = $(".layui-side a[node='" + current_node + "']");
    $current_node.parent().addClass("layui-this");
    $current_node.parents("li").each(function() {
        $(this).addClass("layui-nav-itemed");
    });

    //对外暴露的接口
    exports('common', {});
});