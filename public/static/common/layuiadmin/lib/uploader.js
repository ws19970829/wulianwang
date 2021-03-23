layui.define(['jquery'], function (exports) {
    $ = layui.jquery;

    let is_cloud = false;

    if (is_cloud) {
        load_js('/static/layui/plugins/plupload/plupload.full.min.js', function () {
            load_js('/static/layui/plugins/plupload/qiniu.min.js', function () {
                $('.uploader').each(function () {
                    var $target;
                    var $id;

                    $target = $(this);
                    $id = $target.attr('add_file_id');
                    $btn_id = 'btn_' + $id;
                    $uploader_id = 'uploader_' + $id;

                    var uploader = Qiniu.uploader({
                        disable_statistics_report: false,
                        runtimes: 'html5,flash,html4',
                        browse_button: $btn_id,
                        container: document.getElementById($uploader_id),
                        max_file_size: '1000mb',
                        flash_swf_url: '/static/layui/plugins/plupload/Moxie.swf',
                        chunk_size: '4mb',
                        multi_selection: !(moxie.core.utils.Env.OS.toLowerCase() === "ios"),
                        uptoken_url: qiniu_uptoken_url,
                        domain: qiniu_domain,
                        get_new_uptoken: false,
                        auto_start: true,
                        log_level: 5,
                        init: {
                            'BeforeChunkUpload': function (up, file) {
                                console.log("before chunk upload:", file.name);
                            },
                            'FilesAdded': function (up, files) {
                                for (var i in files) {
                                    file_name = files[i].name;
                                    var ext = file_name.substring(file_name.lastIndexOf(".") + 1);
                                    var file_icon = get_icon(ext);
                                    html = '<li class="tbody" id="' + files[i].id + '">\n';
                                    html += '<div class="loading"></div>\n';
                                    html += '<div class="data autocut">\n';
                                    html += '<span class="icon"><i class="' + file_icon + '"></i></span>\n';
                                    html += '<span class="del text-center"><a class="link del">删除</a></span>\n';
                                    html += '<span class="size text-right">' + plupload.formatSize(files[i].size) + '</span>';
                                    html += '<span class="auto autocut file-name"><a>' + files[i].name + '</a></span>';
                                    html += '</li>';
                                    html += '</div>\n';
                                    $('#uploader_' + $id + ' .file_list').append(html);
                                }
                            },
                            'UploadProgress': function (up, file) {
                                $("#" + file.id).find("a.del").hide();
                                $("#" + file.id).find('.loading').css("width", file.percent + "%");
                            },
                            'UploadComplete': function () {
                                $('#success').show();
                            },
                            'FileUploaded': function (up, file, info) {
                                myObject = eval('(' + info.response + ')');
                                if (info.status) {
                                    $.post('/api/qiniu/js_upload', myObject, function (res) {
                                        if ($("#add_file_" + $id).length != 0) {
                                            $("#add_file_" + $id).val($("#add_file_" + $id).val() + res.sid + ";");
                                        }
                                        $("#" + file.id).attr("add_file", myObject.sid);

                                        $new_upload = $("#uploader_" + $id + " .file_list").attr("new_upload");
                                        $("#uploader_" + $id + " .file_list").attr("new_upload", $new_upload + res.sid + ";");

                                        $("#" + file.id).find("a.del").show();
                                    })
                                } else {
                                    layer.msg(myObject.msg, function () {
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
                            // ,
                            // 'Key': function(up, file) {
                            //     var key = "";
                            //     // do something with key
                            //     return key
                            // }
                        }
                    });

                    function get_icon($ext) {
                        var file_ext = {};
                        file_ext.doc = 'fa fa-file-word-o';
                        file_ext.docx = 'fa fa-file-word-o';
                        file_ext.ppt = 'fa fa-file-powerpoint-o';
                        file_ext.pptx = 'fa fa-file-powerpoint-o';
                        file_ext.xls = 'fa fa-file-excel-o';
                        file_ext.xlsx = 'fa fa-file-excel-o';
                        file_ext.jpg = 'fa fa-file-image-o';
                        file_ext.png = 'fa fa-file-image-o';
                        file_ext.gif = 'fa fa-file-image-o';
                        file_ext.pdf = 'fa fa-file-pdf-o';
                        file_ext.zip = 'fa fa-file-archive-o';
                        file_ext.rar = 'fa fa-file-archive-o';
                        file_ext.mp3 = 'fa fa-file-audio-o';
                        file_ext.wma = 'fa fa-file-audio-o';

                        if (file_ext[$ext] !== undefined) {
                            return file_ext[$ext];
                        } else {
                            return 'fa fa-file-o';
                        }
                        ;
                    };

                    //uploader.init();
                    uploader.bind('BeforeUpload', function () {
                        console.log("hello man, i am going to upload a file");
                    });
                    uploader.bind('FileUploaded', function () {
                        console.log('hello man,a file is uploaded');
                    });

                });
            });
        });
    } else {

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
                    $("#" + file.id).find("file-close").hide();
                    $("#" + file.id).find('.file-uploading').css("width", file.percent + "%");
                });

                uploader.bind('FileUploaded', function (up, file, res) {
                    if (res.status == 200) {
                        info = JSON.parse(res.response);
                        $("#" + file.id).attr("sid", info.data.sid);
                        $("#" + file.id).find(".file-close").show();
                        $("#" + file.id).find('.file-uploading').remove();

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
            });
        });
    }

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

    exports('lib/uploader', {});
});
