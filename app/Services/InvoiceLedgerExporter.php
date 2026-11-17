<?php

namespace App\Services;

use App\Enum\PaymentMethod;
use App\Models\RevenueRecord;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Options\PageMargin;
use OpenSpout\Writer\XLSX\Options\PageOrientation;
use OpenSpout\Writer\XLSX\Options\PageSetup;
use OpenSpout\Writer\XLSX\Options\PaperSize;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports revenue records as the accounting "Bảng kê chi tiết hoá đơn đã sử dụng" worksheet.
 */
class InvoiceLedgerExporter
{
    private const FONT = 'Times New Roman';

    private const LAST_COLUMN_INDEX = 34;

    private const HEADER_ROW = 5;

    private const FIRST_DATA_ROW = 6;

    /**
     * Column headers, in the order of the accounting template (A to AI).
     *
     * @var list<string>
     */
    private const HEADERS = [
        'STT', 'Ký hiệu', 'Số hóa đơn', 'Ngày hóa đơn', 'Mã khách hàng', 'Tên khách hàng', 'Địa chỉ',
        'MST/CCCD chủ hộ', 'MSĐVCQHVNS', 'Người mua hàng', 'Hình thức TT', 'Loại tiền', 'Tỷ giá', 'Mã hàng',
        'Tên hàng', 'Quy cách', 'Ghi chú', 'ĐVT', 'Số lượng', 'Đơn giá', 'Thành tiền', 'Thành tiền quy đổi',
        'Tỷ lệ CK (%)', 'Tiền CK', 'Tiền chiết khấu quy đổi', 'Thuế suất (%)', 'Tiền thuế GTGT',
        'Tiền thuế GTGT quy đổi', 'Tổng tiền TT', 'Tổng tiền TT quy đổi', 'Mã NV bán hàng', 'Tên NV bán hàng',
        'Hàng KM', 'Trạng thái HĐ', 'Người lập HĐ',
    ];

    /**
     * Column widths of the template, as [width, first column, last column] (A = 1).
     *
     * @var list<array{0: float, 1: int, 2: int}>
     */
    private const COLUMN_WIDTHS = [
        [9.29, 1, 1], [20.71, 2, 5], [37.86, 6, 6], [20.71, 7, 21], [21.43, 22, 22], [20.71, 23, 24],
        [25.71, 25, 25], [20.71, 26, 26], [21.43, 27, 27], [28.57, 28, 28], [20.71, 29, 29], [25.71, 30, 30],
        [20.71, 31, 33], [29.29, 34, 34], [20.71, 35, 35],
    ];

    /**
     * Zero-based indexes of the columns summed on the "Tổng cộng" row.
     *
     * @var list<int>
     */
    private const TOTAL_COLUMNS = [18, 20, 21, 23, 24, 26, 27, 28, 29];

    /**
     * Zero-based indexes of numeric columns (right aligned, thousands separated).
     *
     * @var list<int>
     */
    private const NUMBER_COLUMNS = [18, 19, 20, 21, 22, 23, 24, 26, 27, 28, 29];

    /**
     * Stream the filtered revenue records as an XLSX download.
     *
     * @param  Builder<RevenueRecord>  $query
     */
    public function download(Builder $query, string $fileName): StreamedResponse
    {
        $records = (clone $query)->reorder()->orderBy('paid_at')->orderBy('record_key');
        $rowCount = (clone $records)->toBase()->getCountForPagination();
        $period = $this->periodLabel(clone $query);

        return response()->streamDownload(function () use ($records, $rowCount, $period): void {
            $this->write('php://output', $records, $rowCount, $period);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Write the worksheet to the given path.
     *
     * @param  Builder<RevenueRecord>  $records
     */
    public function write(string $path, Builder $records, int $rowCount, string $period): void
    {
        $totalRow = self::FIRST_DATA_ROW + $rowCount;
        $writer = new Writer($this->options($totalRow));
        $writer->openToFile($path);

        $this->writeTitleBlock($writer, $period);

        $totals = array_fill_keys(self::TOTAL_COLUMNS, 0);
        $sequence = 0;

        foreach ($records->cursor() as $record) {
            $values = $this->mapRecord($record, ++$sequence);
            $writer->addRow($this->dataRow($values));

            foreach (self::TOTAL_COLUMNS as $column) {
                $totals[$column] += (float) ($values[$column] ?? 0);
            }
        }

        $writer->addRow($this->totalRow($totals));
        $this->writeSignatureBlock($writer);

        $writer->close();
    }

    /**
     * Map one revenue record onto the 35 template columns. Fields we do not hold stay empty.
     *
     * @return array<int, string|int|float|null>
     */
    public function mapRecord(RevenueRecord $record, int $sequence): array
    {
        $isAsset = $record->source === RevenueRecord::SOURCE_ASSET;
        $vatRate = (int) $record->vat_rate;

        return [
            0 => $sequence,
            1 => null,
            2 => $record->invoice_number,
            3 => $record->paid_at?->format('d/m/Y'),
            4 => $record->buyer_id,
            5 => $record->buyer_name,
            6 => null,
            7 => null,
            8 => null,
            9 => $record->buyer_name,
            10 => $this->paymentMethodLabel($record->payment_method),
            11 => 'VND',
            12 => 1,
            13 => $isAsset ? 'FP'.$record->product_id : 'NAPVI',
            14 => $record->description,
            15 => null,
            16 => $record->reference_code,
            17 => $isAsset ? 'File' : 'Lần',
            18 => 1,
            19 => (int) $record->subtotal,
            20 => (int) $record->subtotal,
            21 => (int) $record->subtotal,
            22 => null,
            23 => 0,
            24 => 0,
            25 => $vatRate.'%',
            26 => (int) $record->vat_amount,
            27 => (int) $record->vat_amount,
            28 => (int) $record->total,
            29 => (int) $record->total,
            30 => null,
            31 => null,
            32 => null,
            33 => 'Hoá đơn mới',
            34 => 'Hệ thống',
        ];
    }

    /**
     * The "Tháng M năm Y" line, or a date range when the data spans several months.
     *
     * @param  Builder<RevenueRecord>  $query
     */
    private function periodLabel(Builder $query): string
    {
        $bounds = $query->reorder()->toBase()->selectRaw('MIN(paid_at) as first_paid_at, MAX(paid_at) as last_paid_at')->first();

        if ($bounds === null || $bounds->first_paid_at === null) {
            return '';
        }

        $first = $this->toDate($bounds->first_paid_at);
        $last = $this->toDate($bounds->last_paid_at);

        if ($first->format('Y-m') === $last->format('Y-m')) {
            return 'Tháng '.$first->month.' năm '.$first->year;
        }

        return 'Từ ngày '.$first->format('d/m/Y').' đến ngày '.$last->format('d/m/Y');
    }

    private function toDate(string $value): CarbonInterface
    {
        return now()->parse($value);
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match (PaymentMethod::tryFrom((string) $paymentMethod)) {
            PaymentMethod::QR_TRANSFER => 'Chuyển khoản',
            default => (string) $paymentMethod,
        };
    }

    private function options(int $totalRow): Options
    {
        $options = new Options;
        $options->DEFAULT_ROW_STYLE = $this->style(fontSize: 10);
        $options->setPageSetup(new PageSetup(PageOrientation::LANDSCAPE, PaperSize::A4));
        $options->setPageMargin(new PageMargin(top: 0.59, right: 0.39, bottom: 0.39, left: 0.39, header: 0.5, footer: 0.5));

        foreach (self::COLUMN_WIDTHS as [$width, $start, $end]) {
            $options->setColumnWidthForRange($width, $start, $end);
        }

        foreach ([1, 2, 3] as $row) {
            $options->mergeCells(0, $row, self::LAST_COLUMN_INDEX, $row);
        }

        // Signature block: label, caption and name rows span B:C (Người lập biểu) and L:M (Giám đốc).
        foreach (range($totalRow + 2, $totalRow + 8) as $row) {
            $options->mergeCells(1, $row, 2, $row);
            $options->mergeCells(11, $row, 12, $row);
        }

        return $options;
    }

    private function writeTitleBlock(Writer $writer, string $period): void
    {
        $title = $this->style(fontSize: 16, bold: true, align: CellAlignment::CENTER);
        $subtitle = $this->style(fontSize: 12, bold: true, italic: true, align: CellAlignment::CENTER);

        $writer->addRow($this->fullWidthRow(null, $title, 20.25));
        $writer->addRow($this->fullWidthRow('BẢNG KÊ CHI TIẾT HOÁ ĐƠN ĐÃ SỬ DỤNG', $title, 20.25));
        $writer->addRow($this->fullWidthRow($period, $subtitle, 20.1));
        $writer->addRow((new Row([]))->setHeight(20.25));

        $headerStyle = $this->style(
            fontSize: 10,
            bold: true,
            align: CellAlignment::CENTER,
            border: true,
            background: 'D8D8D8',
        );

        $writer->addRow(
            (new Row(array_map(fn (string $header): Cell => Cell::fromValue($header, $headerStyle), self::HEADERS)))
                ->setHeight(20.1)
        );
    }

    /**
     * @param  array<int, string|int|float|null>  $values
     */
    private function dataRow(array $values): Row
    {
        $text = $this->style(fontSize: 10, border: true);
        $center = $this->style(fontSize: 10, align: CellAlignment::CENTER, border: true);
        $number = $this->style(fontSize: 10, align: CellAlignment::RIGHT, border: true, format: '#,##0');
        $rate = $this->style(fontSize: 10, align: CellAlignment::RIGHT, border: true, format: '#,##0.00');

        $cells = [];

        foreach (self::HEADERS as $index => $header) {
            $value = $values[$index] ?? null;

            $style = match (true) {
                $index === 0 => $center,
                $index === 12 => $rate,
                in_array($index, self::NUMBER_COLUMNS, true) => $number,
                default => $text,
            };

            $cells[] = Cell::fromValue($value, $style);
        }

        return (new Row($cells))->setHeight(36);
    }

    /**
     * @param  array<int, float|int>  $totals
     */
    private function totalRow(array $totals): Row
    {
        $label = $this->style(fontSize: 10, bold: true, border: true);
        $number = $this->style(fontSize: 10, bold: true, align: CellAlignment::RIGHT, border: true, format: '#,##0');

        $cells = [];

        foreach (self::HEADERS as $index => $header) {
            $cells[] = match (true) {
                $index === 0 => Cell::fromValue('Tổng cộng', $label),
                array_key_exists($index, $totals) => Cell::fromValue($totals[$index], $number),
                default => Cell::fromValue(null, $label),
            };
        }

        return (new Row($cells))->setHeight(37.5);
    }

    private function writeSignatureBlock(Writer $writer): void
    {
        $label = $this->style(fontSize: 12, bold: true, align: CellAlignment::CENTER);
        $caption = $this->style(fontSize: 12, align: CellAlignment::CENTER);

        $writer->addRow(new Row([]));
        $writer->addRow(new Row([]));
        $writer->addRow($this->signatureRow('Người lập biểu', 'Giám đốc', $label));
        $writer->addRow($this->signatureRow('(Ký, họ tên)', '(Ký, họ tên, đóng dấu)', $caption));

        foreach (range(1, 4) as $ignored) {
            $writer->addRow(new Row([]));
        }
    }

    private function signatureRow(string $left, string $right, Style $style): Row
    {
        $cells = array_fill(0, 13, Cell::fromValue(null));
        $cells[1] = Cell::fromValue($left, $style);
        $cells[11] = Cell::fromValue($right, $style);

        return (new Row($cells))->setHeight(20.1);
    }

    private function fullWidthRow(?string $value, Style $style, float $height): Row
    {
        $cells = array_fill(0, self::LAST_COLUMN_INDEX + 1, Cell::fromValue(null, $style));
        $cells[0] = Cell::fromValue($value, $style);

        return (new Row($cells))->setHeight($height);
    }

    private function style(
        int $fontSize,
        bool $bold = false,
        bool $italic = false,
        ?string $align = null,
        bool $border = false,
        ?string $background = null,
        ?string $format = null,
    ): Style {
        $style = (new Style)
            ->setFontName(self::FONT)
            ->setFontSize($fontSize)
            ->setFontColor(Color::BLACK)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();

        if ($bold) {
            $style->setFontBold();
        }

        if ($italic) {
            $style->setFontItalic();
        }

        if ($align !== null) {
            $style->setCellAlignment($align);
        }

        if ($border) {
            $part = fn (string $name): BorderPart => new BorderPart($name, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID);
            $style->setBorder(new Border($part(Border::LEFT), $part(Border::RIGHT), $part(Border::TOP), $part(Border::BOTTOM)));
        }

        if ($background !== null) {
            $style->setBackgroundColor($background);
        }

        if ($format !== null) {
            $style->setFormat($format);
        }

        return $style;
    }
}
