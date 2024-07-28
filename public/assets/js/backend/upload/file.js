define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'upload/file/index' + location.search,
                    add_url: 'upload/file/add',
                    edit_url: 'upload/file/edit',
                    del_url: 'upload/file/del',
                    multi_url: 'upload/file/multi',
                    import_url: 'upload/file/import',
                    table: 'upload_file',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'upload_classify_name', title: __('Upload_classify_id'), operate: 'LIKE'},
                        {
                            field: 'local_url',
                            title: __('Local_url'),
                            operate: false,
                            formatter: function (value, row, index) {
                                //value：intro字段的值
                                //row：当前行所有字段的数据
                                //index：当前行索引
                                //示例：
                                var file_info_json = row.file_info_json
                                return file_info_json.full_name + '  <a href="' + file_info_json.full_url + '" target="_blank">下载</a><br>';;

                            }
                        },
                        {
                            field: 'status',
                            title: __('status'),
                            searchList: {"1": __('Normal'), '2': __('Hidden')},
                            formatter: Table.api.formatter.status
                        },
                        {field: 'operator', title: __('Operator'), operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
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
            },
            file: function (value, row, index) {
                Table.api.formatter.files.call(this, value, row, index);
            }
        }
    };
    return Controller;
});
