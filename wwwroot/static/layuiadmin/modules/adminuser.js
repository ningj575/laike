/** layuiAdmin.std-v1.0.0 LPPL License By http://www.layui.com/admin/ */
;
layui.define(["table", "form"],
    function(e) {
        var t = $ = layui.$,
            i = layui.table;
        layui.form;
		i.render({
			elem:'#LAY-user-back-manage',
            url: 'index',
            page:true,
            limit:50,
            cols: [[{
                    type: "checkbox",
                    fixed: "left"
                },
                {
                    field: "id",
                    width: 80,
                    title: "ID",
                    sort: !0
                },
                {
                    field: "admin_name",
                    title: "管理员名称",
                    minWidth: 100
                },
                {
                    field: "title",
                    title: "管理员角色"
                },
                {
                    field: "loginnum",
                    title: "登录次数"
                },
                {
                    field: "last_login_ip",
                    title: "上次登录ip"
                },
                {
                    field: "last_login_time",
                    title: "上次登录时间"
                },
                {
                    field: "real_name",
                    title: "真实姓名",
                },
                {
                    field: "status",
                    title: "状态",
                    templet:"#buttonTpl"
                },
                {
                    title: "操作",
                    width: 150,
                    align: "center",
                    fixed: "right",
                    toolbar: "#table-useradmin-admin"
                }]]
		}),
		i.on("tool(LAY-user-back-manage)",
            function(e) {
                e.data;
                if ("del" === e.event) layer.confirm("确定删除此管理员？",
					function(t) {
                        //执行 Ajax 后重载
                        layui.admin.req({
                            url: 'userDel',
                            data:{id:e.data.id},
                            done:function(res){
                                console.log(res);
                            }
                        });
                        e.del();
                        layer.close(t);
					});
                else if ("edit" === e.event) {
                    t(e.tr);
                    layer.open({
                        type: 2,
                        title: "编辑管理员",
                        content: "userEdit?id="+e.data.id,
                        area: ["500px", "400px"],
                        maxmin: true,
                        btn: ["确定", "取消"],
                        yes: function(e, t) {
                            var l = window["layui-layer-iframe" + e],
                                r = "LAY-user-back-submit",
                                n = t.find("iframe").contents().find("#" + r);
                            l.layui.form.on("submit(" + r + ")",
                                function(t) {

                                    t.field;//表单字段集合

                                    //提交 Post 成功后，静态更新表格中的数据
                                    $.post('userEdit',{info:t.field},function (res) {
                                        layer.msg('登入成功', {
                                            offset: '15px'
                                            ,icon: 1
                                            ,time: 1000
                                        });
                                    });

                                    i.reload("LAY-user-back-manage"),
                                        layer.close(e)
                                }),
                                n.trigger("click")
                        },
                        success: function(e, t) {

                        }
                    })
                }
            }),
        e("adminuser", {})
});
