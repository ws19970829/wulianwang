/**

 @Name：layuiAdmin iframe版全局配置
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL（layui付费产品协议）

 */

layui.define(function (exports) {
    let config = {
        is_cloud: true,
        qiniu_uptoken_url: '/api/qiniu/up_token',
        qiniu_domain: 'http://qiniu.safarigo.com/'
    }
    exports('config', config)
});
