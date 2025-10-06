<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Preview - Partner Bill Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mx-auto max-w-6xl">
            <h1 class="mb-8 text-3xl font-bold text-gray-800">Mail Preview - Partner Bill Notifications</h1>

            @if (empty($previews))
                <div class="mb-4 rounded border border-yellow-400 bg-yellow-100 px-4 py-3 text-yellow-700">
                    <strong>Thông báo:</strong> Không có đơn hàng nào để preview. Hãy tạo một số PartnerBill để xem preview email.
                </div>
            @else
                <div class="mb-6 rounded border border-blue-400 bg-blue-100 px-4 py-3 text-blue-700">
                    <strong>Hướng dẫn:</strong> Đây là trang preview email chỉ dành cho môi trường development.
                    Click vào các link bên dưới để xem preview của từng loại email.
                </div>

                <div class="grid gap-6">
                    @foreach ($previews as $preview)
                        <div class="rounded-lg bg-white p-6 shadow-md">
                            <div class="mb-4 border-b border-gray-200 pb-4">
                                <h2 class="text-xl font-semibold text-gray-800">{{ $preview['bill_code'] }}</h2>
                                <div class="mt-2 text-sm text-gray-600">
                                    <span class="mr-4 inline-block">
                                        <strong>Client:</strong> {{ $preview['client_name'] }}
                                    </span>
                                    <span class="mr-4 inline-block">
                                        <strong>Partner:</strong> {{ $preview['partner_name'] }}
                                    </span>
                                    <span class="{{ $preview['status'] === 'PAID' ? 'bg-green-100 text-green-800' : ($preview['status'] === 'CANCELLED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }} inline-block rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ $preview['status'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <!-- Order Received Emails -->
                                <div class="space-y-2">
                                    <h3 class="text-sm font-medium uppercase tracking-wide text-gray-700">Đơn đã được nhận</h3>
                                    <div class="space-y-1">
                                        <div class="flex gap-1">
                                            <a class="block w-full rounded border border-blue-200 bg-blue-50 px-2 py-1 text-left text-xs text-blue-700 transition-colors hover:bg-blue-100" href="{{ $preview['links']['received_client_vi'] }}" target="_blank">
                                                📧 Client (VI)
                                            </a>
                                            <a class="block w-full rounded border border-blue-200 bg-blue-50 px-2 py-1 text-left text-xs text-blue-700 transition-colors hover:bg-blue-100" href="{{ $preview['links']['received_client_en'] }}" target="_blank">
                                                📧 Client (EN)
                                            </a>
                                        </div>
                                        <div class="flex gap-1">
                                            <a class="block w-full rounded border border-blue-200 bg-blue-50 px-2 py-1 text-left text-xs text-blue-700 transition-colors hover:bg-blue-100" href="{{ $preview['links']['received_partner_vi'] }}" target="_blank">
                                                📧 Partner (VI)
                                            </a>
                                            <a class="block w-full rounded border border-blue-200 bg-blue-50 px-2 py-1 text-left text-xs text-blue-700 transition-colors hover:bg-blue-100" href="{{ $preview['links']['received_partner_en'] }}" target="_blank">
                                                📧 Partner (EN)
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Confirmed Emails -->
                                <div class="space-y-2">
                                    <h3 class="text-sm font-medium uppercase tracking-wide text-gray-700">Đơn đã được xác nhận</h3>
                                    <div class="space-y-1">
                                        <div class="flex gap-1">
                                            <a class="block w-full rounded border border-green-200 bg-green-50 px-2 py-1 text-left text-xs text-green-700 transition-colors hover:bg-green-100" href="{{ $preview['links']['confirmed_client_vi'] }}" target="_blank">
                                                ✅ Client (VI)
                                            </a>
                                            <a class="block w-full rounded border border-green-200 bg-green-50 px-2 py-1 text-left text-xs text-green-700 transition-colors hover:bg-green-100" href="{{ $preview['links']['confirmed_client_en'] }}" target="_blank">
                                                ✅ Client (EN)
                                            </a>
                                        </div>
                                        <div class="flex gap-1">
                                            <a class="block w-full rounded border border-green-200 bg-green-50 px-2 py-1 text-left text-xs text-green-700 transition-colors hover:bg-green-100" href="{{ $preview['links']['confirmed_partner_vi'] }}" target="_blank">
                                                ✅ Partner (VI)
                                            </a>
                                            <a class="block w-full rounded border border-green-200 bg-green-50 px-2 py-1 text-left text-xs text-green-700 transition-colors hover:bg-green-100" href="{{ $preview['links']['confirmed_partner_en'] }}" target="_blank">
                                                ✅ Partner (EN)
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reminder Emails -->
                                <div class="space-y-2">
                                    <h3 class="text-sm font-medium uppercase tracking-wide text-gray-700">Nhắc nhở sắp đến giờ</h3>
                                    <div class="space-y-1">
                                        <div class="flex gap-1">
                                            <a class="block w-full rounded border border-orange-200 bg-orange-50 px-2 py-1 text-left text-xs text-orange-700 transition-colors hover:bg-orange-100" href="{{ $preview['links']['reminder_client_vi'] }}" target="_blank">
                                                ⏰ Client (VI)
                                            </a>
                                            <a class="block w-full rounded border border-orange-200 bg-orange-50 px-2 py-1 text-left text-xs text-orange-700 transition-colors hover:bg-orange-100" href="{{ $preview['links']['reminder_client_en'] }}" target="_blank">
                                                ⏰ Client (EN)
                                            </a>
                                        </div>
                                        <div class="flex gap-1">
                                            <a class="block w-full rounded border border-orange-200 bg-orange-50 px-2 py-1 text-left text-xs text-orange-700 transition-colors hover:bg-orange-100" href="{{ $preview['links']['reminder_partner_vi'] }}" target="_blank">
                                                ⏰ Partner (VI)
                                            </a>
                                            <a class="block w-full rounded border border-orange-200 bg-orange-50 px-2 py-1 text-left text-xs text-orange-700 transition-colors hover:bg-orange-100" href="{{ $preview['links']['reminder_partner_en'] }}" target="_blank">
                                                ⏰ Partner (EN)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</body>

</html>
