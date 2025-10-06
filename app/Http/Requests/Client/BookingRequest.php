<?php

namespace App\Http\Requests\Client;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'event_id' => ['required', 'exists:events,id'],
            'category_id' => ['required', 'exists:partner_categories,id'],
            'province_id' => ['required', 'exists:locations,id'],
            'ward_id' => ['required', 'exists:locations,id'],
            'location_detail' => ['required', 'string', 'min:5'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_date.required' => 'Vui lòng chọn ngày đặt lịch.',
            'order_date.date_format' => 'Ngày đặt lịch không đúng định dạng (Y-m-d).',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.date_format' => 'Giờ bắt đầu không đúng định dạng (H:i).',
            'end_time.required' => 'Vui lòng chọn giờ kết thúc.',
            'end_time.date_format' => 'Giờ kết thúc không đúng định dạng (H:i).',
            'event_id.required' => 'Vui lòng chọn sự kiện.',
            'event_id.exists' => 'Sự kiện không tồn tại.',
            'province_id.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_id.exists' => 'Tỉnh/thành phố không tồn tại.',
            'ward_id.required' => 'Vui lòng chọn phường/xã.',
            'ward_id.exists' => 'Phường/xã không tồn tại.',
            'location_detail.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'location_detail.string' => 'Địa chỉ chi tiết phải là chuỗi ký tự.',
            'location_detail.min' => 'Địa chỉ chi tiết phải có ít nhất 5 ký tự.',
            'note.string' => 'Ghi chú phải là chuỗi ký tự.',
        ];
    }

    /**
     * optional validation
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $date = $this->input('order_date');
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if (!$date || !$startTime || !$endTime) {
                return;
            }

            try {
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startTime);
                $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $endTime);
            } catch (\Exception $e) {
                $validator->errors()->add('start_time', 'Không thể xác định ngày/giờ.');
                return;
            }

            if ($startDateTime->lessThan(now())) {
                $validator->errors()->add('order_date', 'Thời gian tổ chức sự kiện phải là thời gian tới.');
            }

            if ($startDateTime->greaterThanOrEqualTo($endDateTime)) {
                $validator->errors()->add('start_time', 'Giờ bắt đầu phải nhỏ hơn giờ kết thúc.');
            }

            if ($startDateTime->diffInMinutes($endDateTime) < 30) {
                $validator->errors()->add('end_time', 'Thời gian tổ chức sự kiện phải ít nhất 30 phút.');
            }
        });
    }
}
