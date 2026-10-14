<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];

    private const MAX_MONEY = 9999999999999.99;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'end_user_id' => ['required', 'exists:end_users,id'],
            'pic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'project_name' => ['required', 'string', 'max:255'],
            'project_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_MONEY],
            'po_number' => ['nullable', 'string', 'max:255'],
            'quotation_number' => ['nullable', 'string', 'max:255'],
            'internal_cost' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_MONEY],
            'status' => ['required', Rule::in(self::STATUSES)],
            'deadline_date' => ['nullable', 'date'],
            'submission_token' => ['required', 'uuid'],
        ];
    }
}
