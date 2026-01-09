var tpmsg = {

    //成功弹出层
    success: function (message, url) {
        layer.msg(message, {icon: 6, time: 2000}, function (index) {
            layer.close(index);
            //window.location.href=url;
            layui.table.reload("LAY-user-back-manage");
        });
    },

    // 错误弹出层
    error: function (message) {
        layer.msg(message, {icon: 5, time: 2000}, function (index) {
            layer.close(index);
        });
    },

    // 确认弹出层
    confirm: function (id, url, title, param) {
        if (title == undefined) {
            title = delete_confirm;
        }
        layer.confirm(title, {icon: 3, title: tips,btn: [determine,cancel]}, function (index) {
            layui.$.post(url, {'id': id, param: param}, function (res) {
                if (res.code == 1000) {
                    layer.msg(res.msg, {icon: 1, time: 1500, shade: 0.1},function(){
                        location.reload();
                    });
                    //layer.msg(res.msg, {offset: '15px',icon: 1,time: 1000 });
                    // layui.table.reload("LAY-user-back-manage");
                    
                } else {
                    layer.msg(res.msg, {icon: 0, time: 3000, shade: 0.1});
                }
            });
            layer.close(index);
        })
    },

    //状态
    status: function (id, url, obj) {
        layui.$.post(url, {id: id}, function (data) {
            if (data.code == 1000) {
                layer.msg(data.msg, {icon: 2, time: 1500, shade: 0.1, });
                layui.$(obj).addClass('layui-btn-danger').html((data.msg || close));
            } else {
                layer.msg(data.msg, {icon: 1, time: 1500, shade: 0.1, });
                layui.$(obj).removeClass('layui-btn-danger').html((data.msg || open));
            }
        });
        return true;
    },

    //编辑
    addEdit: function (url, area, title = message,offset='auto',is_refresh=1) {
        if (area == undefined) {
            area = ['40%', '40%'];
        }     
        layer.open({
            type: 2, //1.文章 2.URL
            area: area,
            maxmin: true,
            content: url,
            btn: [determine,cancel ],
            shadeClose: true,
            resize: false,
            offset:offset,
            title: title,
            yes: function (e, t) {
                //获取弹出层对象
                var l = window["layui-layer-iframe" + e],
                        r = "LAY-user-back-submit",
                        n = t.find("iframe").contents().find("#" + r);

                //监听 弹出层 - 表单提交
                l.layui.form.on("submit(" + r + ")",
                        function (formObj) {
                            //formObj.field;//表单字段集合
                            //提交 Post 成功后，静态更新表格中的数据
                            if($('.layui-layer-btn a').hasClass("layui-btn-disabled")){
                                return false;
                            }
                            $('.layui-layer-btn a').addClass("layui-btn-disabled");
                            layui.$.post(url, formObj.field, function (res) {
                                $('.layui-layer-btn a').removeClass("layui-btn-disabled");
                                if (res.code == 1000) {                                    
                                    if(is_refresh==1){
                                        layer.msg(res.msg, {offset: '15px', icon: 1, time: 1000});
                                        layui.table.reload("LAY-user-back-manage");
                                    }else{
                                        layer.msg(res.msg, {offset: '15px', icon: 1, time: 1000},function(){
                                            location.reload();
                                        });                                        
                                    }
                                    layer.close(e);
                                } else {
                                    layer.msg(res.msg, {offset: '15px', icon: 2, time: 1000});
                                }
                            });
                        });
                n.trigger("click")
            },
            success: function (e, t) {
            }
        });
    },

    //批量删除
    batchdel: function (url, param) {
        var checkStatus = layui.table.checkStatus('LAY-user-back-manage')
                , checkData = checkStatus.data; //得到选中的数据

        if (checkData.length === 0) {
            return layer.msg(choose_data);
        }
        layer.confirm(delete_confirm,{title: tips,btn: [determine,cancel]}, function (index) {
            var idStr = '';
            for (var i = 0; i < checkData.length; i++) {
                idStr += checkData[i].id + ',';
            }
            //执行 Ajax 后重载
            layui.admin.req({
                url: url,
                data: {idstr: idStr, param: param},
                done: function (res) {
                    // layui.table.reload("LAY-user-back-manage");
                    location.reload();
                    layer.msg(res.msg);
                }
            });

        });
    },

    //批量状态改变
    batchStateChange: function (url, type = 1) {
        var checkStatus = layui.table.checkStatus('LAY-user-back-manage')
                , checkData = checkStatus.data; //得到选中的数据

        if (checkData.length === 0) {
            return layer.msg(choose_data);
        }
        layer.confirm(confirm_operate, function (index) {
            var idStr = '';
            for (var i = 0; i < checkData.length; i++) {
                idStr += checkData[i].id + ',';
            }
            //执行 Ajax 后重载
            layui.admin.req({
                url: url,
                data: {idstr: idStr, type: type},
                done: function (res) {
                    layui.table.reload("LAY-user-back-manage");
                }
            });
            layer.msg(operate_success);
        });
    },
    //获取数据详情
    DataInfo: function (url, area, title,callback_fun=''){
        layer.open({
            type: 2,
            title: title,
            area: area,
            btnAlign: 'c',
            maxmin: true,
            content: url,
            btn: [],
            yes: function (e, t) {
                layer.close(e);
            },end:function () {
//                location.reload();
            }
        });
    }
};