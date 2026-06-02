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
        $content = $this->getDocumentContent('terms-and-condition.php');

        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'terms-and-condition',
                'title' => 'Điều khoản & điều kiện sử dụng',
                'intro' => 'Trách nhiệm của công ty và trách nhiệm của khách hàng.',
                'hero' => [
                    'kicker' => 'Điều khoản sử dụng',
                    'note' => 'Quy định quyền lợi, trách nhiệm và phạm vi áp dụng cho khách hàng & đối tác',
                ],
                'sections' => $this->parseSections(
                    $content,
                    '/^\d+\.\s+[^\n]+/m'
                ),
            ],
        ]);
    }

    public function faq(): Response
    {
        $content = $this->getDocumentContent('faq.php');

        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'faq',
                'title' => 'Câu hỏi thường gặp',
                'intro' => 'Giải đáp nhanh các câu hỏi phổ biến về đặt dịch vụ, thanh toán và hỗ trợ.',
                'hero' => [
                    'kicker' => 'Hỗ trợ nhanh',
                    'note' => 'Các câu hỏi thường gặp khi đặt dịch vụ và làm việc với Sự Kiện Tốt',
                ],
                'sections' => $this->parseSections(
                    $content,
                    '/^\d+\.\s+[^\n]+/m'
                ),
            ],
        ]);
    }

    public function partnerPolicies(): Response
    {
        $content = $this->getDocumentContent('chinh-sach-va-quy-dinh-doi-tac.php');

        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'chinh-sach-va-quy-dinh',
                'title' => 'Chính sách và quy định dành cho đối tác',
                'intro' => $this->cleanIntro(
                    $this->extractIntro($content, '/^\d+\.\s+[^\n]+/m')
                ),
                'hero' => [
                    'kicker' => 'Chính sách đối tác',
                    'note' => 'Quy định và chính sách hoạt động trên nền tảng Sự Kiện Tốt',
                ],
                'sections' => $this->parseSections(
                    $content,
                    '/^\d+\.\s+[^\n]+/m'
                ),
                'switchLink' => [
                    'label' => 'Xem chính sách cho Khách hàng',
                    'routeName' => 'static.client-policies',
                ],
            ],
        ]);
    }

    public function clientPolicies(): Response
    {
        $content = $this->getDocumentContent('chinh-sach-va-quy-dinh-khach-hang.php');

        return Inertia::render('static/StaticDocument', [
            'page' => [
                'slug' => 'chinh-sach-va-quy-dinh-khach-hang',
                'title' => 'Chính sách và quy định dành cho khách hàng',
                'intro' => $this->cleanIntro(
                    $this->extractIntro($content, '/^\d+\.\s+[^\n]+/m')
                ),
                'hero' => [
                    'kicker' => 'Chính sách khách hàng',
                    'note' => 'Quy định và chính sách sử dụng dịch vụ trên nền tảng Sự Kiện Tốt',
                ],
                'sections' => $this->parseSections(
                    $content,
                    '/^\d+\.\s+[^\n]+/m'
                ),
                'switchLink' => [
                    'label' => 'Xem chính sách cho Đối tác',
                    'routeName' => 'static.partner-policies',
                ],
            ],
        ]);
    }

    public function downloadApp(): Response
    {
        return Inertia::render('app/DownloadApp');
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


}
