<?php

namespace App\Http\Requests\TSSD;

use App\Models\Item;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTssdDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $distributions = $this->input('distributions');

        if (is_string($distributions)) {
            $decoded = json_decode($distributions, true);

            $this->merge([
                'distributions' => json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                    ? $decoded
                    : null,
            ]);
        }

        if ($this->has('print_total_amount')) {
            $this->merge([
                'print_total_amount' => $this->normalizeAmount(
                    $this->input('print_total_amount')
                ),
            ]);
        }

        if ($this->has('remarks')) {
            $remarks = trim((string) $this->input('remarks'));
            $this->merge(['remarks' => $remarks !== '' ? $remarks : null]);
        }
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => ['required', 'integer', 'exists:purchase_orders,id'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'nefa_title' => ['required', 'string', 'max:1000'],
            'print_total_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'print_margin_top' => ['required', 'numeric', 'min:0', 'max:50'],
            'print_margin_right' => ['required', 'numeric', 'min:0', 'max:50'],
            'print_margin_bottom' => ['required', 'numeric', 'min:27', 'max:70'],
            'print_margin_left' => ['required', 'numeric', 'min:0', 'max:50'],

            'distributions' => ['required', 'array', 'min:1'],
            'distributions.*.province_id' => ['required', 'integer', 'distinct', 'exists:provinces,id'],
            'distributions.*.scheduled_delivery_date' => ['required', 'date'],
            'distributions.*.place_of_delivery' => ['nullable', 'string', 'max:1000'],
            'distributions.*.remarks' => ['nullable', 'string', 'max:2000'],
            'distributions.*.items' => ['required', 'array', 'min:1'],
            'distributions.*.items.*' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $distributions = $this->input('distributions', []);

            if (!is_array($distributions)) {
                return;
            }

            $submittedItemIds = [];

            foreach ($distributions as $index => $distribution) {
                if (!is_array($distribution)) {
                    continue;
                }

                $items = $distribution['items'] ?? [];

                if (!is_array($items)) {
                    continue;
                }

                $total = 0;

                foreach ($items as $itemId => $quantity) {
                    if (filter_var($itemId, FILTER_VALIDATE_INT) === false || (int) $itemId <= 0) {
                        $validator->errors()->add(
                            "distributions.{$index}.items",
                            'One submitted PPE item identifier is invalid.'
                        );
                        continue;
                    }

                    $submittedItemIds[] = (int) $itemId;
                    $total += max(0, (int) $quantity);
                }

                if ($total <= 0) {
                    $validator->errors()->add(
                        "distributions.{$index}.items",
                        'Each province must have at least one PPE item assigned.'
                    );
                }
            }

            $submittedItemIds = array_values(array_unique($submittedItemIds));

            if ($submittedItemIds === []) {
                return;
            }

            $activeItemIds = Item::query()
                ->whereIn('id', $submittedItemIds)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $invalidItemIds = array_diff($submittedItemIds, $activeItemIds);

            if ($invalidItemIds !== []) {
                $validator->errors()->add(
                    'distributions',
                    'One or more PPE items are disabled, removed, or no longer available. Refresh the page and try again.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Please correct the distribution information.',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    public function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Please select a Purchase Order.',
            'purchase_order_id.exists' => 'The selected Purchase Order does not exist.',
            'nefa_title.required' => 'The NEFA project title for the request letter is required.',
            'print_total_amount.required' => 'The printed total amount is required.',
            'print_margin_bottom.min' => 'The bottom margin must be at least 27 mm to protect the footer.',
            'distributions.required' => 'Please assign PPE to at least one province.',
            'distributions.min' => 'Please assign PPE to at least one province.',
            'distributions.*.province_id.required' => 'Every allocation must have a province.',
            'distributions.*.province_id.distinct' => 'A province may only be assigned once per batch.',
            'distributions.*.scheduled_delivery_date.required' => 'Every province must have a delivery date.',
            'distributions.*.items.required' => 'Every province must contain PPE quantity inputs.',
            'distributions.*.items.array' => 'The submitted PPE quantities are invalid.',
            'distributions.*.items.*.integer' => 'All PPE quantities must be whole numbers.',
            'distributions.*.items.*.min' => 'PPE quantities cannot be negative.',
        ];
    }

    public function attributes(): array
    {
        return [
            'purchase_order_id' => 'Purchase Order',
            'nefa_title' => 'NEFA project title',
            'print_total_amount' => 'printed total amount',
            'print_margin_top' => 'top print margin',
            'print_margin_right' => 'right print margin',
            'print_margin_bottom' => 'bottom print margin',
            'print_margin_left' => 'left print margin',
            'distributions' => 'provincial distributions',
            'distributions.*.province_id' => 'province',
            'distributions.*.scheduled_delivery_date' => 'delivery date',
            'distributions.*.items' => 'PPE quantities',
        ];
    }

    private function normalizeAmount(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return str_replace([',', '₱', 'P', 'p', ' '], '', trim($value));
    }
}
