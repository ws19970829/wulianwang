/**

 @Name：layuiAdmin 主页示例
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：GPL-2

 */


layui.define(function (exports) {

    //八卦新闻
    layui.use(['carousel', 'echarts'], function () {
        let $ = layui.$;
        let carousel = layui.carousel;
        let echarts = layui.echarts;

        let echartsApp = [];
        let options = [
            {
                title: {
                    subtext: '完全实况球员数据',
                    textStyle: {
                        fontSize: 14
                    }
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    x: 'left',
                    data: ['罗纳尔多', '舍普琴科']
                },
                polar: [
                    {
                        indicator: [
                            {text: '进攻', max: 100},
                            {text: '防守', max: 100},
                            {text: '体能', max: 100},
                            {text: '速度', max: 100},
                            {text: '力量', max: 100},
                            {text: '技巧', max: 100}
                        ],
                        radius: 130
                    }
                ],
                series: [
                    {
                        type: 'radar',
                        center: ['50%', '50%'],
                        itemStyle: {
                            normal: {
                                areaStyle: {
                                    type: 'default'
                                }
                            }
                        },
                        data: [
                            {value: [97, 42, 88, 94, 90, 86], name: '舍普琴科'},
                            {value: [97, 32, 74, 95, 88, 92], name: '罗纳尔多'}
                        ]
                    }
                ]
            }
        ];

        let elemDataView = $('#LAY-index-x').children('div');
        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(options[index]);
            window.onresize = echartsApp[index].resize;
        };
        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    //八卦新闻
    layui.use(['carousel', 'echarts'], function () {
        let $ = layui.$;
        let carousel = layui.carousel;
        let echarts = layui.echarts;

        let echartsApp = [];
        let options = [
            {
                title: {
                    subtext: '完全实况球员数据',
                    textStyle: {
                        fontSize: 14
                    }
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    x: 'left',
                    data: ['罗纳尔多', '舍普琴科']
                },
                polar: [
                    {
                        indicator: [
                            {text: '进攻', max: 100},
                            {text: '防守', max: 100},
                            {text: '体能', max: 100},
                            {text: '速度', max: 100},
                            {text: '力量', max: 100},
                            {text: '技巧', max: 100}
                        ],
                        radius: 130
                    }
                ],
                series: [
                    {
                        type: 'radar',
                        center: ['50%', '50%'],
                        itemStyle: {
                            normal: {
                                areaStyle: {
                                    type: 'default'
                                }
                            }
                        },
                        data: [
                            {value: [97, 42, 88, 94, 90, 86], name: '舍普琴科'},
                            {value: [97, 32, 74, 95, 88, 92], name: '罗纳尔多'}
                        ]
                    }
                ]
            }
        ];
        let elemDataView = $('#LAY-index-pageone').children('div');
        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(options[index]);
            window.onresize = echartsApp[index].resize;
        };
        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    //访问量
    layui.use(['carousel', 'echarts'], function () {
        var $ = layui.$
            , carousel = layui.carousel
            , echarts = layui.echarts;

        var echartsApp = [], options = [
            {
                tooltip: {
                    trigger: 'axis'
                },
                calculable: true,
                legend: {
                    data: ['访问量', '下载量', '平均访问量']
                },

                xAxis: [
                    {
                        type: 'category',
                        data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '访问量',
                        axisLabel: {
                            formatter: '{value} 万'
                        }
                    },
                    {
                        type: 'value',
                        name: '下载量',
                        axisLabel: {
                            formatter: '{value} 万'
                        }
                    }
                ],
                series: [
                    {
                        name: '访问量',
                        type: 'line',
                        data: [900, 850, 950, 1000, 1100, 1050, 1000, 1150, 1250, 1370, 1250, 1100]
                    },
                    {
                        name: '下载量',
                        type: 'line',
                        yAxisIndex: 1,
                        data: [850, 850, 800, 950, 1000, 950, 950, 1150, 1100, 1240, 1000, 950]
                    },
                    {
                        name: '平均访问量',
                        type: 'line',
                        data: [870, 850, 850, 950, 1050, 1000, 980, 1150, 1000, 1300, 1150, 1000]
                    }
                ]
            }
        ]
            , elemDataView = $('#LAY-index-pagetwo').children('div')
            , renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(options[index]);
            window.onresize = echartsApp[index].resize;
        };
        //没找到DOM，终止执行
        if (!elemDataView[0]) return;
        renderDataView(0);

    });


    // 环型
    layui.use(['carousel', 'echarts'], function () {
        let $ = layui.$;
        let carousel = layui.carousel;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            tooltip: {
                trigger: 'item',
                formatter: "{a} <br/>{b}: {c} ({d}%)"
            },
            series: [
                {
                    name: '地区分布',
                    type: 'pie',
                    radius: ['50%', '70%'],
                    avoidLabelOverlap: false,
                    label: {
                        normal: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            show: true,
                            textStyle: {
                                fontSize: '30',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data: [
                        {value: 335, name: '直接访问'},
                        {value: 310, name: '邮件营销'},
                        {value: 234, name: '联盟广告'},
                        {value: 135, name: '视频广告'},
                        {value: 1548, name: '搜索引擎'}
                    ]
                }
            ]
        };

        let elemDataView = $('#LAY-index-pie').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };


        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    // 条形
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
            },
            series: [
                {
                    name: '直接访问',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [320, 302, 301, 334, 390, 330, 320]
                },
                {
                    name: '邮件营销',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [120, 132, 101, 134, 90, 230, 210]
                },
                {
                    name: '联盟广告',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [220, 182, 191, 234, 290, 330, 310]
                },
                {
                    name: '视频广告',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [150, 212, 201, 154, 190, 330, 410]
                },
                {
                    name: '搜索引擎',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [820, 832, 901, 934, 1290, 1330, 1320]
                }
            ]
        };

        let elemDataView = $('#LAY-index-bar').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    // 条形
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: ['青岛啤酒', '南车四方股份', '软控', '中石化', '新希望', '澳柯玛', '青岛港', '海尔', '海信', '上汽通用五菱']
            },
            series: [
                {
                    name: '直接访问',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [2, 7, 23, 34, 45, 58, 63, 156, 198, 301]
                }
            ]
        };

        let elemDataView = $('#LAY-index-bar-1').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    // 条形
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            legend: {
                x: 'right',
                data: ['增员', '减员', '调基'],
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
            },
            yAxis: {
                type: 'value'
            },
            series: [
                {
                    name: '增员',
                    type: 'bar',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [120, 132, 101, 134, 90, 230, 210]
                },
                {
                    name: '减员',
                    type: 'bar',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [220, 182, 191, 234, 290, 330, 310]
                },
                {
                    name: '调基',
                    type: 'bar',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [150, 212, 201, 154, 190, 330, 410]
                }
            ]
        };


        let elemDataView = $('#LAY-index-bar-4').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    // 条形
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: ['青岛啤酒', '南车四方股份', '软控', '中石化', '新希望', '澳柯玛', '青岛港', '海尔', '海信', '上汽通用五菱']
            },
            series: [
                {
                    name: '直接访问',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [12, 17, 223, 234, 345, 358, 443, 556, 598, 601]
                }
            ]
        };

        let elemDataView = $('#LAY-index-bar-2').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    // 条形
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: ['青岛啤酒', '南车四方股份', '软控', '中石化', '新希望', '澳柯玛', '青岛港', '海尔', '海信', '上汽通用五菱']
            },
            series: [
                {
                    name: '直接访问',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    data: [112, 117, 223, 234, 445, 558, 643, 756, 898, 901]
                }
            ]
        };

        let elemDataView = $('#LAY-index-bar-3').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    // 条形
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            tooltip: {
                trigger: 'axis'
            },
            calculable: true,
            legend: {
                data: ['入职人员', '离职人员']
            },

            xAxis: [
                {
                    type: 'category',
                    data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
                }
            ],
            yAxis: [
                {
                    type: 'value',
                    name: '人数',
                    axisLabel: {
                        formatter: '{value}'
                    }
                }
            ],
            series: [
                {
                    name: '入职人员',
                    type: 'line',
                    data: [12, 35, 50, 58, 85, 66, 36, 28, 64, 60, 50, 48]
                },
                {
                    name: '离职人员',
                    type: 'line',
                    data: [22, 25, 54, 48, 55, 36, 76, 68, 34, 50, 59, 38]
                }
            ]
        }

        let elemDataView = $('#LAY-index-line-1').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    // DEMO-1
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            title: {
                text: '今日流量趋势',
                x: 'center',
                textStyle: {
                    fontSize: 14
                }
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['', '']
            },
            xAxis: [{
                type: 'category',
                boundaryGap: false,
                data: ['06:00', '06:30', '07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00', '22:30', '23:00', '23:30']
            }],
            yAxis: [{
                type: 'value'
            }],
            series: [{
                name: 'PV',
                type: 'line',
                smooth: true,
                itemStyle: {normal: {areaStyle: {type: 'default'}}},
                data: [111, 222, 333, 444, 555, 666, 3333, 33333, 55555, 66666, 33333, 3333, 6666, 11888, 26666, 38888, 56666, 42222, 39999, 28888, 17777, 9666, 6555, 5555, 3333, 2222, 3111, 6999, 5888, 2777, 1666, 999, 888, 777]
            }, {
                name: 'UV',
                type: 'line',
                smooth: true,
                itemStyle: {normal: {areaStyle: {type: 'default'}}},
                data: [11, 22, 33, 44, 55, 66, 333, 3333, 5555, 12666, 3333, 333, 666, 1188, 2666, 3888, 6666, 4222, 3999, 2888, 1777, 966, 655, 555, 333, 222, 311, 699, 588, 277, 166, 99, 88, 77]
            }]
        };

        elemDataView = $('#LAY-index-demo-1').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    // DEMO-2
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        //访客浏览器分布
        let option = {
            title: {
                text: '招聘渠道来源',
                x: 'center',
                textStyle: {
                    fontSize: 14
                }
            },
            tooltip: {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                x: 'left',
                data: ['智联', '合作伙伴', '招聘会', '灵工云', '其它']
            },
            series: [{
                name: '访问来源',
                type: 'pie',
                radius: '55%',
                center: ['50%', '50%'],
                data: [
                    {value: 535, name: '智联'},
                    {value: 2052, name: '灵工云'},
                    {value: 1610, name: '合作伙伴'},
                    {value: 3200, name: '招聘会'},
                    {value: 1700, name: '其它'}
                ]
            }]
        };

        elemDataView = $('#LAY-index-demo-2').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    // DEMO-3
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        //访客浏览器分布
        let option = {
            title: {
                text: '最近一周面试用户量',
                x: 'center',
                textStyle: {
                    fontSize: 14
                }
            },
            tooltip: { //提示框
                trigger: 'axis',
                formatter: "{b}<br>新增用户：{c}"
            },
            xAxis: [{ //X轴
                type: 'category',
                data: ['11-07', '11-08', '11-09', '11-10', '11-11', '11-12', '11-13']
            }],
            yAxis: [{  //Y轴
                type: 'value'
            }],
            series: [{ //内容
                type: 'line',
                data: [50, 180, 78, 30, 150, 270, 58],
            }]
        };

        elemDataView = $('#LAY-index-demo-3').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    // DEMO-4
    layui.use(['echarts'], function () {
        let $ = layui.$;
        let echarts = layui.echarts;
        let echartsApp = [];

        let option = {
            title: {
                text: '招聘人员数量',
                x: 'center',
                textStyle: {
                    fontSize: 14
                }
            },
            tooltip: { //提示框
                trigger: 'axis',
                formatter: "{b}<br>新增用户：{c}"
            },
            xAxis: [{ //X轴
                type: 'category',
                data: ['11-07', '11-08', '11-09', '11-10', '11-11', '11-12', '11-13']
            }],
            yAxis: [{  //Y轴
                type: 'value'
            }],
            series: [{ //内容
                type: 'bar',
                data: [200, 300, 400, 610, 150, 270, 380],
            }]
        };

        elemDataView = $('#LAY-index-demo-4').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(option);
            window.onresize = echartsApp[index].resize;
        };

        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    //地图
    layui.use(['carousel', 'echarts'], function () {
        let $ = layui.$;
        let carousel = layui.carousel;
        let echarts = layui.echarts;
        let echartsApp = [];
        let options = [
            {
                title: {
                    text: '全国的求职用户',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'item'
                },
                dataRange: {
                    orient: 'horizontal',
                    min: 0,
                    max: 60000,
                    text: ['高', '低'],
                    splitNumber: 0
                },
                series: [
                    {
                        name: '全国的求职用户',
                        type: 'map',
                        mapType: 'china',
                        selectedMode: 'multiple',
                        itemStyle: {
                            normal: {label: {show: true}},
                            emphasis: {label: {show: true}}
                        },
                        data: [
                            {name: '西藏', value: 60},
                            {name: '青海', value: 167},
                            {name: '宁夏', value: 210},
                            {name: '海南', value: 252},
                            {name: '甘肃', value: 502},
                            {name: '贵州', value: 570},
                            {name: '新疆', value: 661},
                            {name: '云南', value: 8890},
                            {name: '重庆', value: 10010},
                            {name: '吉林', value: 5056},
                            {name: '山西', value: 2123},
                            {name: '天津', value: 9130},
                            {name: '江西', value: 10170},
                            {name: '广西', value: 6172},
                            {name: '陕西', value: 9251},
                            {name: '黑龙江', value: 5125},
                            {name: '内蒙古', value: 1435},
                            {name: '安徽', value: 9530},
                            {name: '北京', value: 51919},
                            {name: '福建', value: 3756},
                            {name: '上海', value: 59190},
                            {name: '湖北', value: 37109},
                            {name: '湖南', value: 8966},
                            {name: '四川', value: 31020},
                            {name: '辽宁', value: 7222},
                            {name: '河北', value: 3451},
                            {name: '河南', value: 9693},
                            {name: '浙江', value: 62310},
                            {name: '山东', value: 39231},
                            {name: '江苏', value: 35911},
                            {name: '广东', value: 55891}
                        ]
                    }
                ]
            }
        ];

        let elemDataView = $('#LAY-index-pagethree').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(options[index]);
            window.onresize = echartsApp[index].resize;
        };


        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });

    //地图
    layui.use(['carousel', 'echarts'], function () {
        let $ = layui.$;
        let carousel = layui.carousel;
        let echarts = layui.echarts;
        let echartsApp = [];
        let options = [
            {
                title: {
                    text: '社保商保服务区域',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'item'
                },
                dataRange: {
                    orient: 'horizontal',
                    min: 0,
                    max: 60000,
                    text: ['高', '低'],
                    splitNumber: 0
                },
                series: [
                    {
                        name: '社保商保服务区域',
                        type: 'map',
                        mapType: 'china',
                        selectedMode: 'multiple',
                        itemStyle: {
                            normal: {label: {show: true}},
                            emphasis: {label: {show: true}}
                        },
                        data: [
                            {name: '西藏', value: 60},
                            {name: '青海', value: 167},
                            {name: '宁夏', value: 210},
                            {name: '海南', value: 252},
                            {name: '甘肃', value: 502},
                            {name: '贵州', value: 570},
                            {name: '新疆', value: 661},
                            {name: '云南', value: 8890},
                            {name: '重庆', value: 10010},
                            {name: '吉林', value: 5056},
                            {name: '山西', value: 2123},
                            {name: '天津', value: 9130},
                            {name: '江西', value: 10170},
                            {name: '广西', value: 6172},
                            {name: '陕西', value: 9251},
                            {name: '黑龙江', value: 5125},
                            {name: '内蒙古', value: 1435},
                            {name: '安徽', value: 9530},
                            {name: '北京', value: 51919},
                            {name: '福建', value: 3756},
                            {name: '上海', value: 59190},
                            {name: '湖北', value: 37109},
                            {name: '湖南', value: 8966},
                            {name: '四川', value: 31020},
                            {name: '辽宁', value: 7222},
                            {name: '河北', value: 3451},
                            {name: '河南', value: 9693},
                            {name: '浙江', value: 62310},
                            {name: '山东', value: 39231},
                            {name: '江苏', value: 35911},
                            {name: '广东', value: 55891}
                        ]
                    }
                ]
            }
        ];

        let elemDataView = $('#LAY-index-pagethree-1').children('div');

        let renderDataView = function (index) {
            echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
            echartsApp[index].setOption(options[index]);
            window.onresize = echartsApp[index].resize;
        };


        //没找到DOM，终止执行
        if (!elemDataView[0]) return;

        renderDataView(0);
    });


    exports('sample', {})
});