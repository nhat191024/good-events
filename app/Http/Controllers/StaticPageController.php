<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StaticPageController extends Controller
{
    public function privacyPolicy(): Response
    {
        $content = $this->getDocumentContent('privacy-policy.php');

        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'privacy-policy',
                'title' => 'Chính sách bảo mật dữ liệu cá nhân',
                'intro' => $this->cleanIntro(
                    $this->extractIntro($content, '/(PHẦN\s+[A-Z]\s+–\s+[^\n]+)/u')
                ),
                'hero' => [
                    'kicker' => 'Bảo vệ dữ liệu',
                    'note' => 'Minh bạch trong thu thập, sử dụng và lưu trữ thông tin cá nhân',
                ],
                'sections' => $this->parseSections(
                    $content,
                    '/(PHẦN\s+[A-Z]\s+–\s+[^\n]+)/u'
                ),
            ],
        ]);
    }

    public function shippingPolicy(): Response
    {
        $content = $this->getDocumentContent('shipping-policy-and-payment.php');

        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'shipping-policy-and-payment-methods',
                'title' => 'Chính sách vận chuyển & phương thức thanh toán',
                'intro' => $this->cleanIntro(
                    $this->extractIntro($content, '/^\d+\.\s+[^\n]+/m')
                ),
                'hero' => [
                    'kicker' => 'Giao nhận & thanh toán',
                    'note' => 'Phạm vi giao hàng, quy trình tiếp nhận và hình thức thanh toán áp dụng',
                ],
                'sections' => $this->parseSections(
                    $content,
                    '/^\d+\.\s+[^\n]+/m'
                ),
            ],
        ]);
    }

    public function termsAndConditions(): Response
    {
        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'terms-and-condition',
                'title' => 'Điều khoản & điều kiện sử dụng',
                'intro' => 'Các điều khoản cơ bản khi sử dụng nền tảng Sự Kiện Tốt và đặt dịch vụ.',
                'hero' => [
                    'kicker' => 'Điều khoản sử dụng',
                    'note' => 'Quy định quyền lợi, trách nhiệm và phạm vi áp dụng cho khách hàng & đối tác',
                ],
                'sections' => $this->buildTermsSections(),
            ],
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'faq',
                'title' => 'Câu hỏi thường gặp',
                'intro' => 'Giải đáp nhanh các câu hỏi phổ biến về đặt dịch vụ, thanh toán và hỗ trợ.',
                'hero' => [
                    'kicker' => 'Hỗ trợ nhanh',
                    'note' => 'Các câu hỏi thường gặp khi đặt dịch vụ và làm việc với Sự Kiện Tốt',
                ],
                'sections' => $this->buildFaqSections(),
            ],
        ]);
    }

    private function parseSections(string $content, string $headingPattern): array
    {
        $content = $this->normalizeContent($content);

        if (!preg_match_all($headingPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $sections = [];
        $headings = $matches[0];

        foreach ($headings as $index => $heading) {
            [$label, $offset] = $heading;
            $start = $offset + strlen($label);
            $end = $headings[$index + 1][1] ?? strlen($content);
            $body = trim(substr($content, $start, $end - $start));

            $sections[] = [
                'id' => Str::slug($label),
                'title' => trim($label),
                'summary' => null,
                'blocks' => $this->splitIntoBlocks($body),
            ];
        }

        return $sections;
    }

    private function splitIntoBlocks(string $body): array
    {
        $lines = array_values(array_filter(array_map(
            static function ($line) {
                $cleaned = str_replace('---', '', trim($line));
                return trim($cleaned);
            },
            preg_split('/\r?\n/', $body)
        )));

        $blocks = [];
        $currentList = [];

        $flushList = static function () use (&$currentList, &$blocks): void {
            if (!empty($currentList)) {
                $blocks[] = [
                    'type' => 'list',
                    'items' => $currentList,
                ];
                $currentList = [];
            }
        };

        foreach ($lines as $line) {
            if ($line === '---') {
                continue;
            }

            $isListLine = str_starts_with($line, '•') || str_starts_with($line, '- ');
            $isSubheading = preg_match('/^\d+(\.\d+)?\./', $line) === 1 && !$isListLine;

            if ($isListLine) {
                $currentList[] = ltrim($line, "•- \t");
                continue;
            }

            $flushList();

            if ($isSubheading) {
                $blocks[] = [
                    'type' => 'subheading',
                    'text' => $line,
                ];
                continue;
            }

            $blocks[] = [
                'type' => 'paragraph',
                'text' => $line,
            ];
        }

        $flushList();

        return $blocks;
    }

    private function extractIntro(string $content, string $firstHeadingPattern): ?string
    {
        $content = $this->normalizeContent($content);

        if (preg_match($firstHeadingPattern, $content, $match, PREG_OFFSET_CAPTURE)) {
            return trim(substr($content, 0, $match[0][1]));
        }

        return null;
    }

    private function normalizeContent(string $content): string
    {
        return str_replace("\r", '', $content);
    }

    private function getDocumentContent(string $fileName): string
    {
        $path = resource_path("static/{$fileName}");

        return $this->normalizeContent(require $path);
    }

    private function cleanIntro(?string $intro): ?string
    {
        if ($intro === null) {
            return null;
        }

        $withoutSeparators = preg_replace('/-{3,}/', '', $intro);

        return $withoutSeparators === null ? trim($intro) : trim($withoutSeparators);
    }

    private function buildTermsSections(): array
    {
        return [
            $this->section(
                '1. Phạm vi áp dụng',
                [
                    $this->paragraph('Điều khoản này áp dụng cho tất cả khách hàng, cộng tác viên và đối tác khi truy cập, đặt dịch vụ hoặc hợp tác với Sự Kiện Tốt.'),
                    $this->paragraph('Khi tiếp tục sử dụng nền tảng, bạn đồng ý tuân thủ toàn bộ điều khoản và chính sách liên quan.'),
                ]
            ),
            $this->section(
                '2. Quyền và trách nhiệm của khách hàng',
                [
                    $this->listBlock([
                        'Cung cấp thông tin chính xác, cập nhật khi đặt dịch vụ.',
                        'Thanh toán đúng hạn theo phương thức đã lựa chọn.',
                        'Tôn trọng thời gian, phạm vi công việc và quy trình phối hợp với đội ngũ triển khai.',
                        'Thông báo sớm nếu cần thay đổi, hoãn hoặc hủy dịch vụ.',
                    ]),
                ]
            ),
            $this->section(
                '3. Quyền và trách nhiệm của Sự Kiện Tốt',
                [
                    $this->listBlock([
                        'Cung cấp dịch vụ theo mô tả, đúng phạm vi thỏa thuận.',
                        'Thông báo kịp thời khi có thay đổi phát sinh, đề xuất giải pháp thay thế phù hợp.',
                        'Bảo mật thông tin khách hàng và tuân thủ các chính sách dữ liệu hiện hành.',
                        'Hỗ trợ hậu mãi và xử lý khiếu nại theo quy trình nội bộ.',
                    ]),
                ]
            ),
            $this->section(
                '4. Quy định đặt dịch vụ',
                [
                    $this->paragraph('Đơn đặt dịch vụ chỉ được xác nhận khi khách hàng hoàn tất việc cung cấp thông tin và thanh toán theo yêu cầu.'),
                    $this->listBlock([
                        'Lịch trình, địa điểm, hạng mục công việc sẽ được chốt qua email hoặc ứng dụng.',
                        'Các yêu cầu phát sinh thêm sẽ được báo giá riêng và thực hiện sau khi hai bên đồng thuận.',
                        'Thời gian giữ lịch tạm thời: 48 giờ kể từ khi báo giá (trừ khi có thỏa thuận khác).',
                    ]),
                ]
            ),
            $this->section(
                '5. Chính sách hủy hoặc đổi lịch',
                [
                    $this->paragraph('Khách hàng cần thông báo bằng văn bản hoặc qua kênh hỗ trợ chính thức.'),
                    $this->listBlock([
                        'Hủy trước 7 ngày: hoàn 100% hoặc bảo lưu giá trị thanh toán.',
                        'Hủy trước 3-6 ngày: hoàn/bảo lưu 50% giá trị.',
                        'Hủy dưới 72 giờ: áp dụng phí hủy lên tới 100% do đã bố trí nhân sự và vật tư.',
                        'Đổi lịch miễn phí 1 lần (nếu còn lịch trống) trong vòng 30 ngày kể từ ngày dự kiến ban đầu.',
                    ]),
                ]
            ),
            $this->section(
                '6. Sở hữu trí tuệ & nội dung',
                [
                    $this->paragraph('Mọi hình ảnh, tài liệu, nhãn hiệu và nội dung trên nền tảng thuộc quyền sở hữu của Sự Kiện Tốt hoặc đã được cấp phép sử dụng.'),
                    $this->paragraph('Không được sao chép, khai thác thương mại hoặc sử dụng trái phép nếu chưa có chấp thuận bằng văn bản.'),
                ]
            ),
            $this->section(
                '7. Giới hạn trách nhiệm',
                [
                    $this->listBlock([
                        'Sự Kiện Tốt không chịu trách nhiệm cho thiệt hại gián tiếp phát sinh từ việc sử dụng dịch vụ.',
                        'Trường hợp bất khả kháng (thiên tai, dịch bệnh, yêu cầu từ cơ quan nhà nước...), hai bên sẽ cùng trao đổi để điều chỉnh lịch trình hợp lý.',
                        'Mức bồi thường tối đa, nếu có, không vượt quá giá trị hợp đồng đã thanh toán.',
                    ]),
                ]
            ),
            $this->section(
                '8. Liên hệ & giải quyết khiếu nại',
                [
                    $this->paragraph('Mọi thắc mắc hoặc khiếu nại vui lòng liên hệ qua Hotline, Email hoặc trung tâm hỗ trợ trong ứng dụng.'),
                    $this->paragraph('Chúng tôi phản hồi trong vòng 3-5 ngày làm việc và ưu tiên giải pháp đôi bên cùng có lợi.'),
                ]
            ),
        ];
    }

    private function buildFaqSections(): array
    {
        return [
            $this->section(
                '1. Tôi cần bao lâu để đặt được nhân sự?',
                [
                    $this->paragraph('Thông thường, bạn chỉ cần 30 giây để gửi yêu cầu. Nhân sự sẽ được xác nhận trong vòng 1-3 giờ làm việc (tuỳ lịch trống).'),
                ]
            ),
            $this->section(
                '2. Tôi có thể đổi lịch sau khi đã đặt không?',
                [
                    $this->paragraph('Có. Bạn có thể đổi lịch tối thiểu 72 giờ trước thời gian diễn ra. Chúng tôi sẽ kiểm tra lịch trống và xác nhận lại.'),
                ]
            ),
            $this->section(
                '3. Các phương thức thanh toán hỗ trợ là gì?',
                [
                    $this->listBlock([
                        'Thanh toán tiền mặt khi nhận dịch vụ (COD) theo chính sách giao nhận.',
                        'Chuyển khoản ngân hàng theo thông tin chính thức của Sự Kiện Tốt.',
                        'Thanh toán trực tuyến qua cổng hỗ trợ (nếu có thông báo).',
                    ]),
                ]
            ),
            $this->section(
                '4. Chính sách hoàn hủy được áp dụng như thế nào?',
                [
                    $this->paragraph('Tuỳ thời điểm thông báo, phí hủy sẽ khác nhau (xem mục Chính sách hủy/đổi lịch trong Điều khoản). Chúng tôi ưu tiên bảo lưu lịch hoặc chuyển đổi dịch vụ tương đương.'),
                ]
            ),
            $this->section(
                '5. Tôi cần hỗ trợ gấp thì liên hệ ở đâu?',
                [
                    $this->paragraph('Bạn có thể gọi Hotline 0393 719 095 hoặc gửi email tới sukientot9095@gmail.com. Đội ngũ hỗ trợ sẽ phản hồi sớm nhất có thể.'),
                ]
            ),
        ];
    }

    private function section(string $title, array $blocks, ?string $summary = null): array
    {
        return [
            'id' => Str::slug($title),
            'title' => $title,
            'summary' => $summary,
            'blocks' => $blocks,
        ];
    }

    private function paragraph(string $text): array
    {
        return [
            'type' => 'paragraph',
            'text' => $text,
        ];
    }

    private function listBlock(array $items, ?string $title = null): array
    {
        return [
            'type' => 'list',
            'title' => $title,
            'items' => $items,
        ];
    }
}
