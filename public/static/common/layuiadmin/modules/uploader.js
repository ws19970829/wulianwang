layui.define(['config', 'jquery', 'upload'], function (exports) {
    $ = layui.jquery;
    config = layui.config;
    let upload = layui.upload;

    let is_cloud = config.is_cloud;
    let qiniu_uptoken_url = config.qiniu_uptoken_url;
    let qiniu_domain = config.qiniu_domain;

    if (is_cloud) {
        load_js('/layuiadmin/plugins/plupload/plupload.full.min.js', function () {
            load_js('/layuiadmin/plugins/plupload/qiniu.min.js', function () {

                // 单图上传

                $('.image-upload').each(function (index) {
                    let $image_upload = $(this);

                    let file_upload_btn_id = 'image-upload-btn-' + index;
                    let file_upload_box_id = 'image-upload-' + index;

                    $image_upload.attr('id', file_upload_box_id);
                    $image_upload.find('.image-upload-btn').attr('id', file_upload_btn_id);

                    var uploader = Qiniu.uploader({
                        save_key: true,
                        disable_statistics_report: true,
                        runtimes: 'html5,flash,html4',
                        browse_button: file_upload_btn_id,
                        container: document.getElementById(file_upload_box_id),
                        max_file_size: '100mb',
                        flash_swf_url: '/layuiadmin/plugins/plupload/Moxie.swf',
                        chunk_size: '4mb',
                        multi_selection: false,
                        uptoken_url: qiniu_uptoken_url,
                        domain: qiniu_domain,
                        get_new_uptoken: false,
                        auto_start: true,
                        log_level: 0,
                        init: {
                            'BeforeChunkUpload': function (up, file) {
                                console.log("before chunk upload:", file.name);
                            },
                            'FilesAdded': function (up, files) {
                                for (let i in files) {
                                    $image_upload.addClass('active');
                                    let loading = '<div class="loading"><i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i></div>';
                                    $image_upload.find('.image').html(loading);
                                }
                            },
                            'FileUploaded': function (up, file, res) {
                                if (res.status === 200) {
                                    info = JSON.parse(res.response);

                                    $.post('/api/qiniu/js_upload', info, function (res) {

                                        if (res.code) {
                                            $image_upload.addClass('active');
                                            $image_upload.find('.image-url').val(res.data.url);

                                            if ($image_upload.hasClass('video')) {
                                                html = '<a class="del"><i class="iconfont td-close" ></i></a><img class="video" src="' + res.data.url + '.jpg">';
                                            } else {
                                                html = '<a class="del"><i class="iconfont td-close" ></i></a><img src="' + res.data.url + '">';
                                            }

                                            $image_upload.find('.image').html(html);
                                            $image_upload.find('div:last').addClass('hidden');
                                        }
                                    });
                                } else {
                                    layer.msg(info.msg, function () {
                                        $("#" + file.id).remove();
                                    });
                                }
                            },
                            'Error': function (up, err, errTip) {
                                $('table').show();
                                var progress = new FileProgress(err.file, 'fsUploadProgress');
                                progress.setError();
                                progress.setStatus(errTip);
                            }
                        }
                    });

                });

                // 多图上传
                $('.image-upload-multiple').each(function (index) {
                    let $image_upload = $(this);

                    let file_upload_btn_id = 'image-upload-multiple-btn' + index;
                    let file_upload_box_id = 'image-upload-multiple-' + index;
                    let file_id = 'image-file' + index;

                    $image_upload.attr('id', file_upload_box_id);
                    $image_upload.find('.image-upload-btn').attr('id', file_upload_btn_id);

                    var uploader = Qiniu.uploader({
                        save_key: true,
                        disable_statistics_report: true,
                        runtimes: 'html5,flash,html4',
                        browse_button: file_upload_btn_id,
                        container: document.getElementById(file_upload_box_id),
                        max_file_size: '100mb',
                        flash_swf_url: '/layuiadmin/plugins/plupload/Moxie.swf',
                        chunk_size: '4mb',
                        multi_selection: true,
                        uptoken_url: qiniu_uptoken_url,
                        domain: qiniu_domain,
                        get_new_uptoken: false,
                        auto_start: true,
                        log_level: 0,
                        init: {
                            'FilesAdded': function (up, files) {
                                for (let i in files) {
                                    let file_id = files[i].id;
                                    let loading = '<div class="image" id="' + file_id + '"><div class="loading"><i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i></div></div>';
                                    $image_upload.find('.image-list').append(loading);
                                }
                            },
                            'FileUploaded': function (up, file, res) {
                                if (res.status === 200) {
                                    info = JSON.parse(res.response);
                                    let file_id = file.id;
                                    $.post('/api/qiniu/js_upload', info, function (res) {
                                        if (res.code) {

                                            $image_upload.find('#' + file_id).html('<a class="del"><i class="iconfont td-close" "></i></a><img src="' + res.data.url + '">');

                                            let img_list = [];
                                            $image_upload.find('.image-list img').each(function () {
                                                img_list.push($(this).attr('src'));
                                            });

                                            $image_upload.find('.image-url').val(img_list.join('|'));
                                        }
                                    });
                                } else {
                                    layer.msg(info.msg, function () {
                                        $("#" + file.id).remove();
                                    });
                                }
                            },
                            'Error': function (up, err, errTip) {
                                $('table').show();
                                var progress = new FileProgress(err.file, 'fsUploadProgress');
                                progress.setError();
                                progress.setStatus(errTip);
                            }
                        }
                    });

                });

                $('.file-upload').each(function (index) {
                    let $this = $(this);

                    let file_upload_btn_id = 'file-upload-btn-' + index;
                    let file_upload_box_id = 'file-upload-' + index;

                    $this.attr('id', file_upload_box_id);
                    $this.find('.file-upload-btn').attr('id', file_upload_btn_id);

                    var uploader = Qiniu.uploader({
                        disable_statistics_report: true,
                        runtimes: 'html5,flash,html4',
                        browse_button: file_upload_btn_id,
                        container: document.getElementById(file_upload_box_id),
                        max_file_size: '100mb',
                        flash_swf_url: '/layuiadmin/plugins/plupload/Moxie.swf',
                        chunk_size: '4mb',
                        multi_selection: !(moxie.core.utils.Env.OS.toLowerCase() === "ios"),
                        uptoken_url: qiniu_uptoken_url,
                        domain: qiniu_domain,
                        get_new_uptoken: false,
                        auto_start: true,
                        log_level: 0,
                        init: {
                            'BeforeChunkUpload': function (up, file) {
                                console.log("before chunk upload:", file.name);
                            },
                            'FilesAdded': function (up, files) {
                                for (let i in files) {
                                    let file_name = files[i].name;
                                    let ext = file_name.substring(file_name.lastIndexOf(".") + 1);
                                    let file_icon = get_icon(ext);

                                    let tpl = '<div class="file" id="{file_id}"><div class="file-info"><span class="file-icon">{file_icon}</span><span class="file-name">{file_name}</span></div><div class="file-close"></div></div>';

                                    let vars = {};
                                    vars.file_id = files[i].id;
                                    vars.file_icon = file_icon;
                                    vars.file_size = plupload.formatSize(files[i].size);
                                    vars.file_name = files[i].name;

                                    let html = tpl_parse(tpl, vars);

                                    $('#' + file_upload_box_id + ' .file-list').append(html);
                                    $("#" + files[i].id).append('<div class="file-uploading"></div>');
                                }
                            },
                            'UploadProgress': function (up, file) {
                                $file_id = $("#" + file.id);
                                $file_id.find("file-close").hide();
                                $file_id.find('.file-uploading').css("width", file.percent + "%");
                            },
                            'UploadComplete': function () {
                                $('#success').show();
                            },
                            'FileUploaded': function (up, file, res) {
                                console.log('111', res);
                                if (res.status === 200) {
                                    info = JSON.parse(res.response);

                                    $.post('/api/qiniu/js_upload', info, function (res) {
                                        $file_id = $("#" + file.id);
                                        $file_id.attr("sid", res.data.sid);
                                        //$file_id.find(".file-close").show();
                                        $file_id.find('.file-uploading').remove();

                                        let file_url = [];
                                        $('#' + file_upload_box_id + ' .file').each(function () {
                                            file_url.push($(this).attr('sid'));
                                        });

                                        $('#' + file_upload_box_id + ' .file-url').val(file_url.join('|'));
                                    });
                                } else {
                                    layer.msg(info.msg, function () {
                                        $("#" + file.id).remove();
                                    });
                                }
                            },
                            'Error': function (up, err, errTip) {
                                $('table').show();
                                var progress = new FileProgress(err.file, 'fsUploadProgress');
                                progress.setError();
                                progress.setStatus(errTip);
                            }
                        }
                    });
                });
            });
        });
    } else {

        // 单图上传
        upload.render({
            elem: '.image-upload .image-upload-btn',
            url: 'upload',
            choose: function (obj) {
                //将每次选择的文件追加到文件队列
                let item = this.item;
                let $image_upload = $(item).closest('.image-upload');
                obj.preview(function (index, file, result) {
                    $image_upload.addClass('active');
                    let loading = '<div class="loading"><i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i></div>';
                    $(item).parent().find('.image').html(loading);
                });
            }
            , done: function (res, index, upload) {
                let item = this.item;
                // console.log(item); //获取当前触发上传的元素，layui 2.1.0 新增
                let $image_upload = $(item).closest('.image-upload');
                if (res.code) {
                    $image_upload.addClass('active');
                    $image_upload.find('.image-url').val(res.data.url);

                    $image_upload.find('.image').html('<a class="del"><i class="iconfont td-close" aria-hidden="true"></i></a><img src="' + res.data.url + '">');
                }
            }
        })

        // 单图上传
        upload.render({
            elem: '.image-upload-multiple .image-upload-btn',
            url: 'upload',
            multiple: true,
            choose: function (obj) {
                //将每次选择的文件追加到文件队列
                let item = this.item;
                let $image_upload = $(item).closest('.image-upload-multiple');
                //预读本地文件，如果是多文件，则会遍历。(不支持ie8/9)
                obj.preview(function (index, file, result) {

                    let loading = '<div class="image" id="' + index + '"><div class="loading"><i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i></div></div>';

                    $image_upload.find('.image-list').append(loading);
                });
            }
            , done: function (res, index, upload) {
                let item = this.item;
                // console.log(item); //获取当前触发上传的元素，layui 2.1.0 新增
                let $image_upload = $(item).closest('.image-upload-multiple');
                if (res.code) {
                    $image_upload.find('#' + index).html('<a class="del"><i class="iconfont td-close" "></i></a><img src="' + res.data.url + '">');

                    let img_list = [];
                    $image_upload.find('.image-list img').each(function () {
                        img_list.push($(this).attr('src'));
                    });

                    $image_upload.find('.image-url').val(img_list.join('|'));

                }
            }
        })

        load_js('/layuiadmin/plugins/plupload/plupload.full.min.js', function () {
            $('.file-upload').each(function (index) {
                let $this = $(this);

                let file_upload_btn_id = 'file-upload-btn-' + index;
                let file_upload_box_id = 'file-upload-' + index;

                $this.attr('id', file_upload_box_id);
                $this.find('.file-upload-btn').attr('id', file_upload_btn_id);

                settings = {
                    runtimes: 'html5,flash',
                    browse_button: file_upload_btn_id, // you can pass in id...
                    container: document.getElementById(file_upload_box_id),
                    url: 'upload',
                    chunk_size: '400kb',
                    flash_swf_url: '/layuiadmin/plugins/plupload/Moxie.swf',
                    filters: {
                        max_file_size: '2000mb'
                    }
                };

                let uploader = new plupload.Uploader(settings);

                uploader.init();

                uploader.bind("PostInit", function (up) {

                });

                uploader.bind('FilesAdded', function (up, files) {
                    for (let i in files) {
                        let file_name = files[i].name;
                        let ext = file_name.substring(file_name.lastIndexOf(".") + 1);
                        let file_icon = get_icon(ext);

                        let tpl = '<div class="file" id="{file_id}"><div class="file-info"><span class="file-icon">{file_icon}</span><span class="file-name">{file_name}</span></div><div class="file-close"></div></div>';

                        let vars = {};
                        vars.file_id = files[i].id;
                        vars.file_icon = file_icon;
                        vars.file_size = plupload.formatSize(files[i].size);
                        vars.file_name = files[i].name;

                        let html = tpl_parse(tpl, vars);

                        $('#' + file_upload_box_id + ' .file-list').append(html);
                        $("#" + files[i].id).append('<div class="file-uploading"></div>');
                    }
                    up.start();
                });

                uploader.bind("UploadProgress", function (up, file) {
                    $file_id = $("#" + file.id);
                    $file_id.find("file-close").hide();
                    $file_id.find('.file-uploading').css("width", file.percent + "%");
                });

                uploader.bind('FileUploaded', function (up, file, res) {
                    if (res.status === 200) {
                        info = JSON.parse(res.response);
                        $file_id = $("#" + file.id);

                        $file_id.attr("sid", info.data.sid);
                        $file_id.find(".file-close").show();
                        $file_id.find('.file-uploading').remove();

                        let file_url = [];
                        $('#' + file_upload_box_id + ' .file').each(function () {
                            file_url.push($(this).attr('sid'));
                        });

                        $('#' + file_upload_box_id + ' .file-url').val(file_url.join('|'));
                    } else {
                        layer.msg(res.msg, function () {
                            $("#" + file.id).remove();
                        });
                    }
                });

            });
        });
    }


    function get_icon(ext) {
        let file_ext = {};
        file_ext.doc = '<i class="iconfont td-file-word"></i>';
        file_ext.docx = '<i class="iconfont td-file-word"></i>';
        file_ext.ppt = '<i class="iconfont td-file-ppt"></i>';
        file_ext.pptx = '<i class="iconfont td-file-ppt"></i>';
        file_ext.xls = '<i class="iconfont td-file-excel"></i>';
        file_ext.xlsx = '<i class="iconfont td-file-excel"></i>';
        file_ext.jpg = '<i class="iconfont td-file-image"></i>';
        file_ext.png = '<i class="iconfont td-file-image"></i>';
        file_ext.gif = '<i class="iconfont td-file-image"></i>';
        file_ext.pdf = '<i class="iconfont td-file-pdf"></i>';
        file_ext.zip = '<i class="iconfont td-file-zip"></i>';
        file_ext.rar = '<i class="iconfont td-file-zip"></i>';
        file_ext.mp3 = '<i class="iconfont td-file-audio"></i>';
        file_ext.wma = '<i class="iconfont td-file-audio"></i>';
        file_ext.avi = '<i class="iconfont td-file-video"></i>';
        file_ext.wmv = '<i class="iconfont td-file-video"></i>';
        file_ext.mp4 = '<i class="iconfont td-file-video"></i>';

        if (file_ext[ext] !== undefined) {
            return file_ext[ext];
        } else {
            return '<i class="iconfont td-file-other"></i>';
        }
    };

    $(document).on("click", ".file-upload .file-close", function () {
        let $file_upload = $(this).closest('.file-upload');

        $(this).closest('.file').remove();

        let file_url = [];
        $($file_upload).find('.file').each(function () {
            file_url.push($(this).attr('sid'));
        });

        $($file_upload).find('.file-url').val(file_url.join('|'));
    });

    $(document).on("click", ".file-upload .file-name", function () {
        let $file = $(this).closest('.file');
        window.open('download?sid=' + $file.attr('sid'), 'new');
    })

    exports('uploader', {});
});
