<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>WebUploader演示</title>
    <link rel="stylesheet" type="text/css" href="/static/webuploader/css/webuploader.css" />
    <link rel="stylesheet" type="text/css" href="/static/webuploader/css/style.css" />

</head>
<body style="background: #ffffff">
<div id="wrapper">
    <div id="container">
        <!--头部，相册选择和格式选择-->

        <div id="uploader">
            <div class="queueList">
                <div id="dndArea" class="placeholder">
                    <div id="filePicker"></div>
                    <p>请按照导入模板的excel文件格式进行上传</p>
                </div>
            </div>
            <div class="statusBar" style="display:none;">
                <div class="progress">
                    <span class="text">0%</span>
                    <span class="percentage"></span>
                </div><div class="info"></div>
                <div class="btns">
                    <div id="filePicker2"></div><div class="uploadBtn">开始上传</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="margin-left: 20px;">
    <div class="webuploader-pick" onclick="go_back()">返回订单列表</div>
    <div class="webuploader-pick" onclick="download()">下载导入模板</div>
</div>

<script type="text/javascript" src="/static/webuploader/js/jquery.js"></script>
<script type="text/javascript" src="/static/webuploader/js/webuploader.js"></script>
<script type="text/javascript" src="/static/webuploader/js/upload.js"></script>

<script>
    function download() {
        window.location.href='/发货导入模板.xlsx';
    }

    function go_back(){
        window.history.back(-1);
    }
</script>
</body>
</html>
