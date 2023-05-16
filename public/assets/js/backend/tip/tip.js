define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'tip/tip/index' + location.search,
                    add_url: 'tip/tip/add',
                    edit_url: 'tip/tip/edit',
                    del_url: 'tip/tip/del',
                    multi_url: 'tip/tip/multi',
                    import_url: 'tip/tip/import',
                    table: 'tip',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'parent_id', title: __('Parent_id')},
                        {field: 'images', title: __('Images'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'tip_type', title: __('Tip_type')},
                        {field: 'solveswitch', title: __('Solveswitch'), table: table, formatter: Table.api.formatter.toggle},
                        {field: 'hiddswitch', title: __('Hiddswitch'), table: table, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
