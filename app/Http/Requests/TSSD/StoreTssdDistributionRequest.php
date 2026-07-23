<?php

namespace App\Http\Requests\TSSD;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTssdDistributionRequest extends FormRequest
{
    /**
     * Determine whether the authenticated user may submit the request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Convert the JSON string submitted by the hidden input into a PHP array
     * before Laravel runs validation.
     */
    protected function prepareForValidation(): void
    {
        $distributions = $this->input('distributions');

        if (is_string($distributions)) {
            $decodedDistributions = json_decode(
                $distributions,
                true
            );

            $this->merge([
                'distributions' => json_last_error() === JSON_ERROR_NONE
                    && is_array($decodedDistributions)
                        ? $decodedDistributions
                        : null,
            ]);
        }

        if ($this->has('remarks')) {
            $remarks = trim(
                (string) $this->input('remarks')
            );

            $this->merge([
                'remarks' => $remarks !== ''
                    ? $remarks
                    : null,
            ]);
        }
    }

    /**
     * Validation rules for a TSSD distribution batch.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'purchase_order_id' => [
                'required',
                'integer',
                'exists:purchase_orders,id',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'distributions' => [
                'required',
                'array',
                'min:1',
            ],

            'distributions.*.province_id' => [
                'required',
                'integer',
                'distinct',
                'exists:provinces,id',
            ],

            'distributions.*.scheduled_delivery_date' => [
                'required',
                'date',
            ],

            'distributions.*.place_of_delivery' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'distributions.*.long_sleeve_medium' => [
                'required',
                'integer',
                'min:0',
            ],

            'distributions.*.long_sleeve_large' => [
                'required',
                'integer',
                'min:0',
            ],

            'distributions.*.bucket_hat' => [
                'required',
                'integer',
                'min:0',
            ],

            'distributions.*.rubber_boots_us9' => [
                'required',
                'integer',
                'min:0',
            ],

            'distributions.*.rubber_boots_us10' => [
                'required',
                'integer',
                'min:0',
            ],

            'distributions.*.hand_gloves' => [
                'required',
                'integer',
                'min:0',
            ],

            'distributions.*.mask' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Add validation that requires at least one PPE quantity for each province.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $distributions = $this->input(
                'distributions',
                []
            );

            if (!is_array($distributions)) {
                return;
            }

            foreach ($distributions as $index => $distribution) {
                if (!is_array($distribution)) {
                    continue;
                }

                $totalQuantity =
                    (int) ($distribution['long_sleeve_medium'] ?? 0)
                    + (int) ($distribution['long_sleeve_large'] ?? 0)
                    + (int) ($distribution['bucket_hat'] ?? 0)
                    + (int) ($distribution['rubber_boots_us9'] ?? 0)
                    + (int) ($distribution['rubber_boots_us10'] ?? 0)
                    + (int) ($distribution['hand_gloves'] ?? 0)
                    + (int) ($distribution['mask'] ?? 0);

                if ($totalQuantity <= 0) {
                    $validator
                        ->errors()
                        ->add(
                            "distributions.{$index}",
                            'Each province must have at least one PPE item assigned.'
                        );
                }
            }
        });
    }

    /**
     * Return JSON validation errors to the JavaScript request instead of
     * redirecting back and returning the complete HTML create page.
     */
    protected function failedValidation(
        Validator $validator
    ): void {
        if (
            $this->expectsJson()
            || $this->ajax()
        ) {
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

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'purchase_order_id.required' =>
                'Please select a Purchase Order.',

            'purchase_order_id.exists' =>
                'The selected Purchase Order does not exist.',

            'distributions.required' =>
                'Please assign PPE to at least one province.',

            'distributions.array' =>
                'The provincial distribution information is invalid.',

            'distributions.min' =>
                'Please assign PPE to at least one province.',

            'distributions.*.province_id.required' =>
                'Every allocation must have a province.',

            'distributions.*.province_id.distinct' =>
                'A province may only be assigned once per batch.',

            'distributions.*.province_id.exists' =>
                'One of the selected provinces does not exist.',

            'distributions.*.scheduled_delivery_date.required' =>
                'Every province must have a delivery date.',

            'distributions.*.scheduled_delivery_date.date' =>
                'One of the delivery dates is invalid.',

            'distributions.*.long_sleeve_medium.required' =>
                'The Long Sleeve Medium quantity is required.',

            'distributions.*.long_sleeve_large.required' =>
                'The Long Sleeve Large quantity is required.',

            'distributions.*.bucket_hat.required' =>
                'The Bucket Hat quantity is required.',

            'distributions.*.rubber_boots_us9.required' =>
                'The Rubber Boots US9 quantity is required.',

            'distributions.*.rubber_boots_us10.required' =>
                'The Rubber Boots US10 quantity is required.',

            'distributions.*.hand_gloves.required' =>
                'The Hand Gloves quantity is required.',

            'distributions.*.mask.required' =>
                'The Mask quantity is required.',

            'distributions.*.*.integer' =>
                'All PPE quantities must be whole numbers.',

            'distributions.*.*.min' =>
                'PPE quantities cannot be negative.',
        ];
    }

    /**
     * Human-readable field labels.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'purchase_order_id' => 'Purchase Order',
            'distributions' => 'provincial distributions',
            'distributions.*.province_id' => 'province',
            'distributions.*.scheduled_delivery_date' => 'delivery date',
            'distributions.*.place_of_delivery' => 'place of delivery',
            'distributions.*.long_sleeve_medium' => 'Long Sleeve Medium',
            'distributions.*.long_sleeve_large' => 'Long Sleeve Large',
            'distributions.*.bucket_hat' => 'Bucket Hat',
            'distributions.*.rubber_boots_us9' => 'Rubber Boots US9',
            'distributions.*.rubber_boots_us10' => 'Rubber Boots US10',
            'distributions.*.hand_gloves' => 'Hand Gloves',
            'distributions.*.mask' => 'Mask',
        ];
    }
}