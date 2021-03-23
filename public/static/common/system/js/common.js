/*设置 cookie*/
var ls = window.localStorage;

function set_cookie(key, value, exp, path, domain, secure) {
    key = key;
    path = "/";
    var cookie_string = key + "=" + escape(value);
    if (exp) {
        cookie_string += "; expires=" + exp.toGMTString();
    }
    if (path)
        cookie_string += "; path=" + escape(path);
    if (domain)
        cookie_string += "; domain=" + escape(domain);
    if (secure)
        cookie_string += "; secure";
    document.cookie = cookie_string;
}

/*读取 cookie*/
function get_cookie(cookie_name) {
    var results = document.cookie.match('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
    if (results)
        return (unescape(results[2]));
    else
        return null;
}

function new_guid() {
    var guid = "";
    for (var i = 1; i <= 32; i++) {
        var n = Math.floor(Math.random() * 16.0).toString(16);
        guid += n;
        if ((i == 8) || (i == 12) || (i == 16) || (i == 20))
            guid += "-";
    }
    return guid;
}

/*删除 cookie*/
function del_cookie(cookie_name) {
    cookie_name = cookie_prefix + cookie_name;
    var cookie_date = new Date();
    //current date & time
    cookie_date.setTime(cookie_date.getTime() - 1);
    document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}

function set_ls(key, val) {
    console.log(window.localStorage);
    ls.setItem(key, val);
}

function get_ls(key) {
    localStorage.getItem(key);
}

/*设置要返回的URL*/
function set_return_url(url) {
    var return_url = get_cookie('return_url');
    if (return_url == null || url === null) {
        arr_return_url = [];
    } else {
        arr_return_url = return_url.split('$');
    }
    if (url == undefined || url == null) {
        url = document.location.pathname + location.search;
    }
    if (arr_return_url.slice(-1) != url) {
        arr_return_url.push(url);
    }
    set_cookie("return_url", arr_return_url.join('$'));
}

/*返回到上一页*/
function go_return_url() {
    var return_url = get_cookie('return_url');
    if (return_url == null) {
        return false;
    } else {
        arr_return_url = return_url.split('$');
    }

    go_url = arr_return_url.pop();
    if (go_url == document.location.pathname + location.search) {
        go_url = arr_return_url.pop();
    }
    console.log(go_url);
    set_cookie("return_url", arr_return_url.join('$'));
    if (go_url != undefined) {
        location.href = go_url;
    }
    event.stopPropagation();
    return false;
}

/* 删除左右两端的空格*/
function trim(str) {
    return str.replace(/(^\s*)|(\s*$)/g, "");
}

function get_attr(dom, attr) {
    if (typeof (dom) == 'object') {
        var d = dom;
    }
    if (typeof (dom) == 'string') {
        var d = document.getElementById(dom);
    }
    //获取该节点
    if ((d !== null) && (undefined !== d.attributes[attr])) {
        return d.attributes[attr].value;
    }

    //获取该原生属性的值。
    return null;
}

function set_attr(dom, attr, val) {
    if (typeof (dom) == 'object') {
        var d = dom;
    }
    if (typeof (dom) == 'string') {
        var d = document.getElementById(dom);
    }
    var node = document.createAttribute(attr);
    node.nodeValue = val;
    d.attributes.setNamedItem(node);
}

function click_top_menu(node) {
    url = get_attr(node, 'url');
    node = get_attr(node, 'node');
    set_cookie("top_menu", node);
    set_cookie("left_menu", "");
    set_cookie("current_node", "");
    ("http://" === url.substr(0, 7) || "#" === url.substr(0, 1)) ? window.open(url) : location.href = url;
}

function click_home_list(node) {
    node = get_attr(node, 'node');
    set_cookie("top_menu", node);

    return_url = get_attr(node, 'return_url');
    set_return_url(return_url);

    url = get_attr(node, 'url');
    location.href = url;
}

/* 获取日历背景颜色*/
function schedule_bg(j) {
    var myArray = new Array(5);
    myArray[0] = "#CCCCCC";
    myArray[1] = "#1ab394";
    myArray[2] = "#f8ac59";
    myArray[3] = "#ed5565";
    myArray[4] = "#FFCCCC ";
    return myArray[j - 1];
}

function push_info($msg) {
    var position;
    if ($msg.action.length) {
        $title = '<h3>[' + $msg.type + '] [' + $msg.action + ']</h3>';
    } else {
        $title = '<h3>[' + $msg.type + ']</h3>';
    }
    $content = '<b>' + $msg.title + '</b><br>' + $msg.content;

    if (is_mobile()) {
        position = "toast-top-full-width";
    } else {
        position = "toast-bottom-right";
    }
    toastr.options = {
        "closeButton": true,
        "positionClass": position,
        "timeOut": ws_push_time * 1000
    };
    toastr.info($content, $title);
}

/*联系人显示格式转换*/
function conv_address_item(name, data) {
    html = '<nobr><label>';
    html += '		<input class="ace" type="checkbox" name="addr_id" value="' + data + '"/>';
    html += '		<span class="lbl">' + name + '</span></label></nobr>';
    return html;
}

/*联系人显示格式转换*/
function conv_address_item_radio(name, data) {
    html = '<nobr><label>';
    html += '		<input text="' + name + '"class="ace" type="radio" name="addr_id" value="' + data + '"/>';
    html += '		<span class="lbl">' + name + '</span></label></nobr>';
    return html;
}

function conv_inputbox_item(name, data) {
    html = "<span data=\"" + data + "\" id=\"" + data + "\">";
    html += "<nobr><b  title=\"" + name + "\">" + name + "</b>";
    html += "<a class=\"del\" title=\"删除\"><i class=\"fa fa-times\"></i></a></nobr></span>";
    return html;
}

/* 在iframe里显示textarea的内容*/
function show_content() {
    $(".content_wrap").each(function () {
        iframe = $(this).find(".content_iframe").get(0).contentWindow;
        var div = document.createElement("div");
        div.className = "height";
        div.innerHTML = $(this).find(".content").val();
        iframe.document.body.appendChild(div);
        height = $(iframe.document.body).find("div.height").height();
        if (height < 100) {
            height = 100;
        }
        iframe.height = height;
        $(this).height(height + 35);
        $(iframe).height(height + 35);
    });
}

/*赋值*/

function set_val(name, val) {
    if (val == null) {
        val = '';
    }
    var d = document.getElementsByName(name);
    if (d !== null) {
        for (var i = 0; i < d.length; i++) {
            dom = d[i];
            var type = dom.type;
            console.log(type);
            switch (type) {
                case 'time':
                    dom.value = val;
                    break;
                case 'text':
                    dom.value = val;
                    break;
                case 'hidden':
                    dom.value = val;
                    break;
                case 'select-one':
                    var is_selected = false;
                    for (var k = 0; k < dom.options.length; k++) {
                        if (dom.options[k].value == val) {
                            dom.options[k].selected = true;
                            is_selected = true;
                            break;
                        }
                    }
                    if (!is_selected) {
                        dom.options[0].selected = true;
                    }
                    break;
                case 'radio':
                    if (dom.value == val) {
                        dom.checked = true;
                    }
                    break;
                case 'checkbox':
                    if (dom.value == val) {
                        dom.checked = true;
                    }
                    break;
                case 'textarea':
                    dom.value = val;
                    break;
                default:
            }
        }
    }
}

function show_udf_val($udf_data) {
    for (s in $udf_data) {
        set_val('udf_field_' + s, $udf_data[s]);
    }
}

/*联系人显示格式转换*/
function contact_conv(val) {
    var arr_temp = val.split(";");
    var html = "";
    for (key in arr_temp) {
        if (arr_temp[key] != '') {
            data = arr_temp[key].split("|")[1];
            id = arr_temp[key].split("|")[1];
            name = arr_temp[key].split("|")[0];
            title = arr_temp[key].split("|")[0];
            html += conv_inputbox_item(name, data);
            //html +=  '<span data="' + arr_temp[key].split("|")[1] + '" onmousedown="return false"><nobr>' + arr_temp[key].split("|")[0] + '<a class=\"del\" title=\"删除\"><i class=\"fa fa-times\"></i></a></nobr></span>';
        }
    }
    return html;
}

/* 判断是否是移动设备 */
function is_mobile() {
    return navigator.userAgent.match(/mobile/i);
}

/*联系人显示格式转换*/
function fix_url(url, vars) {
    var ss = url.split('?');
    url = ss[0] + "?";
    for (var i = 1; i < ss.length; i++) {
        url += ss[i] + "&";
    }
    if (ss.length > 0) {
        url = url.substring(0, url.length - 1);
    }
    if (vars != undefined) {
        for (s in vars) {
            url += '&' + s + '=' + vars[s];
            console.log(url);
        }
    }
    return url;
}

function t(val) {
    console.log(val);
}

function init_form_verify(form_id) {
    let form = document.getElementById(form_id);
    for (var i = 0; i < form.elements.length; i++) {
        var el = form.elements[i];
        el.addEventListener('blur', function (event) {
            check = get_attr(event.target, 'check');
            if (check != null) {
                if (!validate(el.value, check)) {
                    verify_msg = get_attr(event.target, 'info');
                    let error_box = getByClass(event.target, 'has-error');
                    console.log(error_box);

                    var div = document.createElement('div');
                    div.innerHTML = '<span class="has-error">' + verify_msg + '</span>';
                    event.target.parentNode.appendChild(div);
                }
            }
        })
    }
}

function getByClass(oParent, sClass) {
    var aResult = [];
    var aEle = oParent.getElementsByTagName('*');

    for (var i = 0; i < aEle.length; i++) {
        if (aEle[i].className == sClass) {
            aResult.push(aEle[i]);
        }
    }

    return aResult;
}

function check_form(form_id, callback) {
    let form = document.getElementById(form_id);

    for (let i = 0; i < form.elements.length; i++) {
        var el = form.elements[i];
        var type = el.getAttribute('type')

        if ("INPUT" == el.tagName && (type == 'text'||type == 'hidden')) {
            var check = get_attr(el, 'check');
            if (check != null) {
                if (!validate(el.value, check)) {
                    var ret = {};
                    ret.status = 0;
                    ret.info = get_attr(el, 'info');
                    ret.dom = el;
                    callback(ret);
                    return false;
                }
            }
        }

        if ("INPUT" == el.tagName && type == 'radio') {
            var check = get_attr(el, 'check');
            if (check != null) {
                var name = el.getAttribute('name');
                var radio_group = document.getElementsByName(name);
                var checked_count = 0;

                for (let i = 0; i < radio_group.length; i++) {
                    var radio = radio_group.item(i);
                    if (radio.checked) {
                        checked_count++;
                    }
                }

                if (checked_count === 0) {
                    var ret = {};
                    ret.status = 0;
                    ret.info = get_attr(el, 'info');
                    ret.dom = el;

                    callback(ret);
                    return false;
                }
            }
        }


        if ("SELECT" == el.tagName) {
            var check = get_attr(el, 'check');
            if (check != null) {
                if (el.selectedIndex == 0 && el.value == '') {
                    var ret = {};
                    ret.status = 0;
                    ret.msg = get_attr(el, 'info');
                    ret.dom = el;
                    callback(ret);
                    return false;
                }
            }
        }
    }

    last_submit = get_attr(form, 'last_submit');
    var now = new Date().getTime();
    if (last_submit == null) {
        set_attr(form, 'last_submit', now);
    } else {
        if (now - last_submit > 5000) {
            set_attr(form, 'last_submit', now);
            last_submit = get_attr(form, 'last_submit');
            console.log(last_submit);
        } else {
            return false;
        }
    }

    var ret = {};
    ret.status = 1;
    ret.msg = '通过验证';
    callback(ret);
}

/* 验证数据类型*/
function validate(data, data_type) {

    if (data_type.indexOf("|") > -1) {
        tmp = data_type.split("|");
        data_type = tmp[0];
        console.log(tmp);
        data2 = window.document.getElementById(tmp[1]).value;
    }

    switch (data_type) {
        case "required":
            if (data == "") {
                return false;
            } else {
                return true;
            }
            break;
        case "email":
            var reg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return reg.test(data);
            break;
        case "mobile":
            var reg = /(^1[3|4|5|7|8|9]\d{9}$)|(^09\d{8}$)/;
            return reg.test(data);
            break;
        case "number":
            var reg = /^[0-9]+\.{0,1}[0-9]{0,3}$/;
            return reg.test(data);
            break;
        case "idcard":
            var reg = /^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
            return reg.test(data);
            break;
        case "html":
            var reg = /<...>/;
            return reg.test(data);
            break;
    }
}

function conv_int_to_date(int) {
    var now = new Date(int * 1000);
    var year = now.getFullYear();
    var month = '0' + (now.getMonth() + 1);
    var date = '0' + now.getDate();
    var hour = '0' + now.getHours();
    var minute = '0' + now.getMinutes();
    var second = '0' + now.getSeconds();
    return year + "-" + month.slice(-2) + "-" + date.slice(-2) + " " + hour.slice(-2) + ":" + minute.slice(-2);
}

function format_date(format, currDate) {
    /*
     * eg:format="YYYY-MM-dd hh:mm:ss";
     */
    console.log(currDate);
    currDate = new Date(currDate * 1000);
    console.log(currDate);
    var o = {
        "M+": currDate.getMonth() + 1, // month
        "d+": currDate.getDate(), // day
        "h+": currDate.getHours(), // hour
        "m+": currDate.getMinutes(), // minute
        "s+": currDate.getSeconds(), // second
        "q+": Math.floor((currDate.getMonth() + 3) / 3), // quarter
        "S": currDate.getMilliseconds()
        // millisecond
    };
    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (currDate.getFullYear() + "").substr(4 - RegExp.$1.length));
    }

    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
}

function win_exp(obj) {
    for (s in obj) {
        window[s] = obj[s];
    }
};

function popup_close() {
    parent.layer.close(parent.layer.getFrameIndex(window.name));
}

/**
 * 异步加载依赖的javascript文件
 * src：script的路径
 * callback：当外部的javascript文件被load的时候，执行的回调
 */
function load_js(src, callback) {
    var srcArray = src.split("?")[0].split("/");
    var scr_src = srcArray[srcArray.length - 1];

    if (src.indexOf('layuiadmin') > -1) {
        src = '/static/common' + src;
    }
    // 判断要 添加的脚本是否存在如果存在则不继续添加了
    var scripts = document.getElementsByTagName("script");
    if (!!scripts && 0 != scripts.length) {
        for (var i = 0; i < scripts.length; i++) {
            if (-1 != scripts[i].src.indexOf(scr_src)) {
                callback();
                return true;
            }
        }
    }

    // 不存在需要的则添加
    var head = document.getElementsByTagName("head")[0];
    var script = document.createElement("script");
    script.setAttribute("type", "text/javascript");
    script.setAttribute("src", src);
    script.setAttribute("async", true);
    script.setAttribute("defer", true);
    head.appendChild(script);

    //fuck ie! duck type
    if (document.all) {
        script.onreadystatechange = function () {
            var state = this.readyState;
            if (state === 'loaded' || state === 'complete') {
                callback();
            }
        };
    } else {
        //firefox, chrome
        script.onload = function () {
            callback();
        };
    }
}

function load_css(href, callback) {
    var doc = window.document;
    var that = this,
        link = doc.createElement('link');
    var head = doc.getElementsByTagName('head')[0];
    var app = href.replace(/\.|\//g, '');
    var id = link.id = 'layuicss-' + app,
        timeout = 0;

    link.rel = 'stylesheet';
    link.href = href + (true ? '?v=' + new Date().getTime() : '');
    link.media = 'all';

    if (!doc.getElementById(id)) {
        head.appendChild(link);
    }

    console.log(typeof callback);
    if (typeof callback !== 'function')
        return;

    //轮询css是否加载完毕
    (function poll() {
        if (++timeout > 3 * 1000 / 100) {
            return error(href + ' timeout');
        }
        ;
        console.log(that);
        parseInt(that.getStyle(doc.getElementById(id), 'width')) === 1989 ? function () {
            callback();
        }() : setTimeout(poll, 100);
    }());
};

function tpl_parse(tpl, vars) {
    for (let key in vars) {
        let pattern = new RegExp("({" + key + "})", "g");
        tpl = tpl.replace(pattern, vars[key]);
    }
    return tpl;
}

function pushHistory() {
    var state = {
        title: "title",
        url: "#"
    };
    window.history.pushState(state, "title", "#");
}

function format_time(time) {
    temp_str = '0000' + time;
    return temp_str.substr(-4, 2) + ":" + temp_str.substr(-2, 2);
}

function reunit(val) {
    var unit = 'B';
    if (val >= 1024) {
        val = Math.round(val / 1024 * 10) / 10;
        unit = 'K';
    }
    if (val >= 1024) {
        val = Math.round(val / 1024 * 10) / 10;
        unit = 'M';
    }
    if (val >= 1024) {
        val = Math.round(val / 1024 * 10) / 10;
        unit = 'G';
    }
    if (val >= 1024) {
        val = Math.round(val / 1024 * 10) / 10;
        unit = 'T';
    }
    return val + unit;
}