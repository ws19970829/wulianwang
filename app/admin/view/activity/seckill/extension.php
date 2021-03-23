{extend name="public/container"}
{block name="content"}
<div class="ibox-content order-info">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading" style="text-align: center;">
                <a href="/{$img}" download="{$name}.png">
                    <img src="/{$img}" width="350" />
                </a>
            </div>

            <div class="panel-body">
                <div class="row show-grid">
                    <span class="input-group-addon">推广链接：</span>
                    <input class="layui-input" readonly value="{$url}">
                </div>
            </div>
        </div>
    </div>

</div>

<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
<script src="{__FRAME_PATH}js/jquery.min.js"></script>

<!--<script src="/static/js/clipboard.min.js"></script>-->

<script>
//
//    function copy_text(){
//        var clipboard = new ClipboardJS('.btn', {
//            text: function() {
//                return 'to be or not to be22222';
//            }
//        });
//
//        clipboard.on('success', function(e) {
//            console.log(e);
//        });
//
//        clipboard.on('error', function(e) {
//            console.log(e);
//        });
//    }

</script>
{/block}
{block name="script"}

{/block}
