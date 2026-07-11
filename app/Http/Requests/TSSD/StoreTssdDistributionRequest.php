<?php

namespace App\Http\Requests\TSSD;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreTssdDistributionRequest extends FormRequest
{
    /**
     * Only TSSD users may submit distribution batches.
     */
    public function authorize(): bool
    {
        return $this->user()?->isTssd() === true;
    }

    /**
     * Convert the JSON distribution field from the current Blade form
     * into a normal PHP array before validation.
     */
    protected function prepareForValidation(): void
    {
        $distributions = $this->input('distributions');

        if (is_string($distributions)) {
            $decoded = json_decode($distributions, true);

            $distributions = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : null;
        }

        $this->merge([
            'distributions' => $distributions,
        ]);
    }

    /**
     * Validation rules.
     *
     * These field names intentionally match the JavaScript currently used in
     * resources/views/tssd/distribution/create.blade.php.
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

            'delivery_date' => [
                'required',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
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
     * Validate business-level input conditions that cannot be expressed
     * cleanly through individual field rules.
     */
    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {
                $distributions =
                    $this->input(
                        'distributions',
                        []
                    );

                if (!is_array($distributions)) {
                    return;
                }

                $wholeBatchTotal = 0;

                foreach (
                    $distributions as $index
                    => $distribution
                ) {
                    if (!is_array($distribution)) {
                        continue;
                    }

                    $provinceTotal = collect([
                        $distribution[
                            'long_sleeve_medium'
                        ] ?? 0,

                        $distribution[
                            'long_sleeve_large'
                        ] ?? 0,

                        $distribution[
                            'bucket_hat'
                        ] ?? 0,

                        $distribution[
                            'rubber_boots_us9'
                        ] ?? 0,

                        $distribution[
                            'rubber_boots_us10'
                        ] ?? 0,

                        $distribution[
                            'hand_gloves'
                        ] ?? 0,

                        $distribution[
                            'mask'
                        ] ?? 0,
                    ])
                        ->map(
                            fn($value): int =>
                            (int) $value
                        )
                        ->sum();

                    $wholeBatchTotal +=
                        $provinceTotal;

                    if ($provinceTotal <= 0) {
                        $validator->errors()->add(
                            "distributions.{$index}",
                            'At least one PPE quantity must be greater than zero for each selected province.'
                        );
                    }
                }

                if ($wholeBatchTotal <= 0) {
                    $validator->errors()->add(
                        'distributions',
                        'The distribution batch must contain at least one PPE item.'
                    );
                }
            }
        );
    }

    /**
     * Friendly validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Please select a Purchase Order.',

            'purchase_order_id.exists' => 'The selected Purchase Order does not exist.',

            'delivery_date.required' => 'Please provide the scheduled delivery date.',

            'distributions.required' => 'Please assign PPE to at least one province.',

            'distributions.array' => 'The submitted distribution data is invalid.',

            'distributions.min' => 'Please assign PPE to at least one province.',

            'distributions.*.province_id.required' => 'Every distribution entry must have a province.',

            'distributions.*.province_id.distinct' => 'A province cannot be added more than once to the same distribution batch.',

            'distributions.*.province_id.exists' => 'One of the selected provinces does not exist.',

            'distributions.*.long_sleeve_medium.min' => 'Long Sleeve Medium quantity cannot be negative.',

            'distributions.*.long_sleeve_large.min' => 'Long Sleeve Large quantity cannot be negative.',

            'distributions.*.bucket_hat.min' => 'Bucket Hat quantity cannot be negative.',

            'distributions.*.rubber_boots_us9.min' => 'Rubber Boots US9 quantity cannot be negative.',

            'distributions.*.rubber_boots_us10.min' => 'Rubber Boots US10 quantity cannot be negative.',

            'distributions.*.hand_gloves.min' => 'Hand Gloves quantity cannot be negative.',

            'distributions.*.mask.min' => 'Mask quantity cannot be negative.',
        ];
    }

    /**
     * Keep JSON requests readable by returning Laravel's normal 422 response.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new ValidationException($validator);
    }
}
