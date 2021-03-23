layui.define(['jquery'], function (exports) {
    $ = layui.jquery;

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

        load_js('/static/layui/plugins/plupload/plupload.full.min.js', function () {
            $('.uploader').each(function () {
                var uploader;
                var $target;
                var $id;

                $target = $(this);
                $id = $target.attr('add_file_id');
                $btn_id = 'btn_' + $id;
                $uploader_id = 'uploader_' + $id;

                settings = {
                    runtimes: 'html5,flash',
                    browse_button: $btn_id, // you can pass in id...
                    container: document.getElementById($uploader_id),
                    url: upload_url,
                    chunk_size: '400kb',
                    flash_swf_url: '/static/layui/plugins/plupload/Moxie.swf',
                    filters: {
                        max_file_size: '2000mb'
                    }
                };

                uploader = new plupload.Uploader(settings);
                //uploaders[$id] = uploader;

                uploader.bind("PostInit", function (up) {
                    if ($(".uploader .tbody").length > 0) {
                        $(".uploader .tbody .loading").css("width", "100%");
                        $(".uploader .thead").show();
                        $(".uploader .tbody").each(function () {
                            id = $(this).attr("filename");
                            filename = $(this).attr("filename");
                            size = $(this).attr("size");
                            file = new plupload.File(id, filename, size);
                            file.status = plupload.DONE;
                            count = uploader.files.length;
                            uploader.files[count] = file;
                        });
                    }
                });

                uploader.init();

                uploader.bind('FilesAdded', function (up, files) {
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
                    up.start();
                });

                uploader.bind("UploadProgress", function (up, file) {
                    $("#" + file.id).find("a.del").hide();
                    $("#" + file.id).find('.loading').css("width", file.percent + "%");
                });

                uploader.bind('FileUploaded', function (up, file, info) {

                    var myObject = eval('(' + info.response + ')');

                    if (myObject.status) {
                        if ($("#add_file_" + $id).length != 0) {
                            $("#add_file_" + $id).val($("#add_file_" + $id).val() + myObject.sid + ";");
                        }
                        $("#" + file.id).attr("add_file", myObject.sid);

                        $new_upload = $("#uploader_" + $id + " .file_list").attr("new_upload");
                        $("#uploader_" + $id + " .file_list").attr("new_upload", $new_upload + myObject.sid + ";");

                        $("#" + file.id).find("a.del").show();
                    } else {
                        layer.msg(myObject.info, function () {
                            $("#" + file.id).remove();
                        });
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
            });
        });
    }

    $(document).on("click", ".uploader a.del", function () {
        $obj = $(this).parents("li");
        $uploader = $(this).parents('.uploader');
        layer.msg('确定要删除吗?', {
            time: 0,
            btn: ['确定', '取消'],
            yes: function (index) {

                $current_del_file = $obj.attr("add_file");
                $(".add_file", $uploader).val($(".add_file", $uploader).val().replace($current_del_file + ";", ""));

                $new_upload = $(".file_list", $uploader).attr("new_upload");
                $(".file_list", $uploader).attr("new_upload", $new_upload.replace($current_del_file + ";", ""));

                $obj.remove();
                layer.close(index);
            },
            no: function (index) {
                layer.close(index);
            }
        });
    });

    exports('uploader', {});
});
