<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\In>>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'pic_id' => ['nullable', 'integer', 'exists:users,id'],
            'end_user_id' => ['nullable', 'integer', 'exists:end_users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:100'],
            'deadline_scope' => ['nullable', Rule::in(['overdue', 'upcoming'])],
        ];
    }
}
