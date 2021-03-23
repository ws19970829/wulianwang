layui.define(['layer', 'laydate'], function(exports) {
	window.$ = layui.jquery;
	var layer = layui.layer;
	window.jQuery = layui.jquery;
	var laydate = layui.laydate;

	var exp = {
		winopen : function(url, w, h) {
			url = fix_url(url);
			var index = layer.open({
				type : 2,
				title : false,
				area : [w + 'px', h + 'px'],
				shade : 0.6,
				closeBtn : 0,
				shadeClose : false,
				scrollbar : false,
				content : [url, 'no']
			});
			if (is_mobile()) {
				layer.full(index);
			}
		},
		send_form : function(from_id, post_url, return_url, callback) {
			check_form(from_id, function(ret) {
				if (ret.status) {
					if ($("#ajax").val() == 1) {
						var vars = $("#" + from_id).serialize();
						$.ajax({
							type : "POST",
							url : post_url,
							data : vars,
							dataType : "json",
							success : function(data) {
								if ( typeof (callback) == 'function') {
									callback(data);
								} else {
									if (data.status) {
										layer.msg(data.info, {
											time : 1200
										}, function() {
											if (return_url) {
												location.href = return_url;
											} else {
												location.reload(true);
											}
										});
									} else {
										layer.msg(data.info);
										return false;
									};
								}
							}
						});
					} else {
						//取消beforeunload事件
						if (return_url) {
							set_return_url(return_url);
						}
						$(window).unbind('beforeunload', null);
						$("#" + from_id).attr("action", post_url);
						$("#" + from_id).submit();
					}
				} else {
					layer.msg(ret.info);
					ret.dom.focus();
					return false;
				}
			});
		},
		/* ajax提交*/
		send_ajax : function(url, vars, callback) {
			return $.ajax({
				type : "POST",
				url : url,
				data : vars + "&ajax=1",
				dataType : "json",
				success : callback
			});
		},
		winprint : function() {
			setTimeout(function() {
				window.print();
			}, 300);
		}
	};
	win_exp(exp);

	send_ajax(badge_count_url, '', function(ret) {
		for (s in ret) {
			var badge_tpl = '<span class="layui-badge">{count}</span>';
			var vars = {};
			vars.count = ret[s];
			html = tpl_parse(badge_tpl, vars);
			$("a[node=" + s + "] .count").html(html);
			//alert(ret[s]);
		}
	});

	$(document).on('click touchstart', '.btn-preview', function() {
		var file_list = $(this).attr('file_list');
		var file_id = $(this).attr('file_id');
		if ($(this).parents('body').hasClass('popup')) {			
			parent.winopen(preview_url + "?file_list=" + file_list + '&file_id=' + file_id, 560, 470);
		} else {
			parent.winopen(preview_url + "?file_list=" + file_list + '&file_id=' + file_id, 560, 470);
		}
	});

	var $top_menu_link = $('a.top_menu_link');
	$top_menu_link.on('click touchstart', function() {
		url = $(this).attr("url");
		node = $(this).attr("node");
		set_cookie("top_menu", node);
		set_cookie("left_menu", "");
		set_cookie("current_node", "");
		("http://" === url.substr(0, 7) || "#" === url.substr(0, 1)) ? window.open(url) : location.href = url;
	});

	top_menu = get_cookie("top_menu");
	$(".x-nav .x-nav-item a.top_menu_link[node=" + top_menu + "]").addClass("active");

	$('.layui-side a').on('click touchstart', function() {

		var $this = $(this);
		var url = $this.attr("href");
		if (url.length > 0 && (url != "#")) {
			node = $this.attr("node");
			set_cookie("current_node", node);
		}
	});

	current_node = get_cookie("current_node");
	$(".layui-side a[node='" + current_node + "']").parent().addClass("layui-this");
	$(".layui-side a[node='" + current_node + "']").parents("li").each(function() {
		$(this).addClass("layui-nav-itemed");
		//alert($(this).html());
		//$(this).find('> ul').addClass("in");
		//breadcrumb = '<li>' + $(this).find("a:first .menu-text").text() + '</li>' + breadcrumb;
	});
	//$(".breadcrumb").append(breadcrumb);

	var $toogle_tom_menu = $('.toogle_tom_menu');
	$toogle_tom_menu.on('click', function() {
		if ($('.header .x-nav').hasClass('mobile-hidden')) {

			$('.header .x-nav').removeClass('mobile-hidden');
			$('.header').addClass('mobile_fixed');
		} else {
			$('.header .x-nav').addClass('mobile-hidden');
			$('.header').removeClass('mobile_fixed');
		}
	});

	$('#submit_search').on('click', function() {
		$('#form_search').submit();
	});

	$('#form_search #keyword').on('keydown', function(event) {
		if (event.keyCode == 13) {
			$('#form_search').submit();
		}
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

	$('.input-date-time').on('click', function() {
		laydate.render({
			elem : this,
			type: 'datetime',
			format : 'yyyy-MM-dd HH:mm',
			show : true //直接显示
		});
	});
	
	$('.input-date').on('click', function() {
		laydate.render({
			elem : this,
			show : true //直接显示
		});
	});

	$('.input-date-range').on('click', function() {
		laydate.render({
			elem : this,
			range : '~',
			format : 'yyyy-MM-dd',
			show : true //直接显示
		});
	});

	$('.toggle-select-all').on('click', function() {
		$this = $(this);
		var count = 0;
		var checkbox_name = $this.attr('data');
		is_checked = $this.attr('checked');
		if (is_checked) {
			$("input[type=checkbox][name='" + checkbox_name + "']").each(function() {
				this.checked = false;
				$(this).closest('.tbody').removeClass('selected');
			});
			$this.attr('checked', false);
		} else {
			$("input[type=checkbox][name='" + checkbox_name + "']").each(function() {
				this.checked = true;
				$(this).closest('.tbody').addClass('selected');
			});
			$this.attr('checked', true);
		}
	});

	function toggle_adv_search() {
		if ($("#adv_search").attr("class").indexOf("hidden") < 0) {
			$("#adv_search").addClass("hidden");
			$("#toggle_adv_search_icon").addClass("fa-chevron-down");
			$("#toggle_adv_search_icon").removeClass("fa-chevron-up");
		} else {
			$("#adv_search").removeClass("hidden");
			$("#toggle_adv_search_icon").addClass("fa-chevron-up");
			$("#toggle_adv_search_icon").removeClass("fa-chevron-down");
		}
	};

	$(".dropdown-toggle").on('click', function() {
		$next = $(this).next().toggle();
	});

	$(".dropdown-menu").on('mouseleave', function() {
		$(this).toggle();
	});

	$(document).on('click', '.input-box .address_list a.del', function() {
		$(this).parent().parent().remove();
	});

	$(document).on('click', '.process a.del', function() {
		$(this).parent().remove();
	});

	exports('global', {});
});
