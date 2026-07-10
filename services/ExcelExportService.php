<?php
/**
 * Excel 导出服务（基于 PhpSpreadsheet 3.x）
 *
 * 依赖: composer require phpoffice/phpspreadsheet
 */
class ExcelExportService
{
    /**
     * 生成并下载 Excel 文件
     *
     * @param string $filename 文件名（不含扩展名）
     * @param array  $data     数据行（二维关联数组）
     * @param array  $columns  列映射 [字段名 => 列标题]
     */
    public function export(string $filename, array $data, array $columns): void
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $this->exportCsv($filename, $data, $columns);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $coordinate = '\PhpOffice\PhpSpreadsheet\Cell\Coordinate';
        $fillClass  = '\PhpOffice\PhpSpreadsheet\Style\Fill';
        $borderClass = '\PhpOffice\PhpSpreadsheet\Style\Border';

        // 设置表头
        $colIndex = 1;
        foreach ($columns as $title) {
            $cellRef = $coordinate::stringFromColumnIndex($colIndex) . '1';
            $sheet->setCellValue($cellRef, $title);
            $colIndex++;
        }

        // 表头样式
        $lastCol = count($columns);
        $headerRange = $coordinate::stringFromColumnIndex(1) . '1:' . $coordinate::stringFromColumnIndex($lastCol) . '1';
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
            'fill' => [
                'fillType' => $fillClass::FILL_SOLID,
                'startColor' => ['rgb' => 'F1F5F9'],
            ],
            'borders' => [
                'bottom' => ['borderStyle' => $borderClass::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
            ],
        ];
        $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

        // 填充数据
        $rowIndex = 2;
        foreach ($data as $row) {
            $colIndex = 1;
            foreach (array_keys($columns) as $field) {
                $value = $row[$field] ?? '';
                $cellRef = $coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                // 处理数组字段（如 tags）
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                // 格式化金额
                if (in_array($field, ['consume_amount', 'quote_amount', 'price', 'old_price'])) {
                    $cell = $sheet->getCell($cellRef);
                    $cell->setValue((float) $value);
                    if ($value > 0) {
                        $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                } else {
                    $sheet->setCellValue($cellRef, $value);
                }
                $colIndex++;
            }
            $rowIndex++;
        }

        // 自动列宽
        for ($col = 1; $col <= $lastCol; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // 输出下载
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /** CSV 降级导出（无需 PhpSpreadsheet） */
    private function exportCsv(string $filename, array $data, array $columns): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, array_values($columns));

        foreach ($data as $row) {
            $line = [];
            foreach (array_keys($columns) as $field) {
                $value = $row[$field] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $line[] = $value;
            }
            fputcsv($output, $line);
        }
        fclose($output);
        exit;
    }
}
