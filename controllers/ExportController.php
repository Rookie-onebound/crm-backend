<?php
/**
 * Excel 导出控制器
 */
class ExportController
{
    /** GET /api/export/customers */
    public function customers(): void
    {
        $model = new Customer();
        $data = $model->getAllForExport();

        $columns = [
            'customer_name'      => '客户名称',
            'company_name'       => '公司名称',
            'phone'              => '电话',
            'email'              => '邮箱',
            'source'             => '来源',
            'register_ip'        => '注册IP',
            'level'              => '客户等级',
            'consume_amount'     => '消费金额',
            'intention_level'    => '意向程度',
            'last_contact_time'  => '最近联系时间',
            'notes'              => '备注',
            'industry'           => '行业',
            'assigned_to'        => '负责人',
            'created_at'         => '创建时间',
        ];

        $exporter = new ExcelExportService();
        $exporter->export('客户列表_' . date('YmdHis'), $data, $columns);
    }

    /** GET /api/export/prices */
    public function prices(): void
    {
        $model = new ApiProduct();
        $data = $model->getAllForExport();

        $columns = [
            'platform'       => '平台',
            'api_name'       => 'API名称',
            'api_path'       => 'API路径',
            'price'          => '当前价格',
            'old_price'      => '原价',
            'currency'       => '币种',
            'billing_unit'   => '计费单位',
            'status'         => '状态',
            'change_percent' => '变动百分比',
            'effective_date' => '生效日期',
            'description'    => '描述',
        ];

        $exporter = new ExcelExportService();
        $exporter->export('价格动态_' . date('YmdHis'), $data, $columns);
    }
}
