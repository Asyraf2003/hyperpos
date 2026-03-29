<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreTransactionWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => $this->normalizeNote($this->input('note')),
            'items' => $this->normalizeItems($this->input('items')),
            'inline_payment' => $this->normalizeInlinePayment($this->input('inline_payment')),
        ]);
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'array'],
            'note.customer_name' => ['required', 'string'],
            'note.customer_phone' => ['nullable', 'string'],
            'note.transaction_date' => ['required', 'date_format:Y-m-d'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.entry_mode' => ['required', 'string', 'in:product,service'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.part_source' => ['nullable', 'string', 'in:none,store_stock,customer_owned,external_purchase'],

            'items.*.service' => ['nullable', 'array'],
            'items.*.service.name' => ['nullable', 'string'],
            'items.*.service.price_rupiah' => ['nullable', 'integer', 'min:1'],
            'items.*.service.notes' => ['nullable', 'string'],

            'items.*.product_lines' => ['nullable', 'array'],
            'items.*.product_lines.0.product_id' => ['nullable', 'string'],
            'items.*.product_lines.0.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.product_lines.0.unit_price_rupiah' => ['nullable', 'integer', 'min:1'],

            'items.*.external_purchase_lines' => ['nullable', 'array'],
            'items.*.external_purchase_lines.0.label' => ['nullable', 'string'],
            'items.*.external_purchase_lines.0.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.external_purchase_lines.0.unit_cost_rupiah' => ['nullable', 'integer', 'min:1'],

            'inline_payment' => ['required', 'array'],
            'inline_payment.decision' => ['required', 'string', 'in:skip,pay_full,pay_partial'],
            'inline_payment.payment_method' => ['nullable', 'string', 'in:cash,transfer'],
            'inline_payment.paid_at' => ['nullable', 'date_format:Y-m-d'],
            'inline_payment.amount_paid_rupiah' => ['nullable', 'integer', 'min:1'],
            'inline_payment.amount_received_rupiah' => ['nullable', 'integer', 'min:1'],
            'inline_payment.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items', []);

            if (! is_array($items) || $items === []) {
                return;
            }

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    $validator->errors()->add("items.$index", 'Format item workspace tidak valid.');
                    continue;
                }

                $entryMode = (string) ($item['entry_mode'] ?? '');
                $partSource = (string) ($item['part_source'] ?? 'none');

                if ($entryMode === 'product') {
                    $this->validateProductItem($validator, $index, $item);
                    continue;
                }

                if ($entryMode === 'service') {
                    $this->validateServiceItem($validator, $index, $item, $partSource);
                    continue;
                }

                $validator->errors()->add("items.$index.entry_mode", 'Tipe item workspace tidak valid.');
            }
        });
    }

    private function validateProductItem(Validator $validator, int $index, array $item): void
    {
        $line = $this->firstArrayItem($item['product_lines'] ?? []);

        if ($this->blank($line['product_id'] ?? null)) {
            $validator->errors()->add("items.$index.product_lines.0.product_id", 'Product wajib dipilih.');
        }

        if ($this->integerValue($line['qty'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.product_lines.0.qty", 'Qty produk wajib lebih dari 0.');
        }

        if ($this->integerValue($line['unit_price_rupiah'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.product_lines.0.unit_price_rupiah", 'Harga satuan produk wajib lebih dari 0.');
        }
    }

    private function validateServiceItem(Validator $validator, int $index, array $item, string $partSource): void
    {
        $service = is_array($item['service'] ?? null) ? $item['service'] : [];

        if ($this->blank($service['name'] ?? null)) {
            $validator->errors()->add("items.$index.service.name", 'Nama servis wajib diisi.');
        }

        if ($this->integerValue($service['price_rupiah'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.service.price_rupiah", 'Harga servis wajib lebih dari 0.');
        }

        if (! in_array($partSource, ['none', 'store_stock', 'customer_owned', 'external_purchase'], true)) {
            $validator->errors()->add("items.$index.part_source", 'Sumber part servis tidak valid.');
            return;
        }

        if ($partSource === 'store_stock') {
            $line = $this->firstArrayItem($item['product_lines'] ?? []);

            if ($this->blank($line['product_id'] ?? null)) {
                $validator->errors()->add("items.$index.product_lines.0.product_id", 'Part stok toko wajib dipilih.');
            }

            if ($this->integerValue($line['qty'] ?? null) <= 0) {
                $validator->errors()->add("items.$index.product_lines.0.qty", 'Qty part stok toko wajib lebih dari 0.');
            }

            if ($this->integerValue($line['unit_price_rupiah'] ?? null) <= 0) {
                $validator->errors()->add("items.$index.product_lines.0.unit_price_rupiah", 'Harga part stok toko wajib lebih dari 0.');
            }
        }

        if ($partSource === 'external_purchase') {
            $line = $this->firstArrayItem($item['external_purchase_lines'] ?? []);

            if ($this->blank($line['label'] ?? null)) {
                $validator->errors()->add("items.$index.external_purchase_lines.0.label", 'Label pembelian luar wajib diisi.');
            }

            if ($this->integerValue($line['qty'] ?? null) <= 0) {
                $validator->errors()->add("items.$index.external_purchase_lines.0.qty", 'Qty pembelian luar wajib lebih dari 0.');
            }

            if ($this->integerValue($line['unit_cost_rupiah'] ?? null) <= 0) {
                $validator->errors()->add("items.$index.external_purchase_lines.0.unit_cost_rupiah", 'Biaya satuan pembelian luar wajib lebih dari 0.');
            }
        }
    }

    private function normalizeNote(mixed $value): array
    {
        $note = is_array($value) ? $value : [];

        return [
            'customer_name' => $this->trimOrNull($note['customer_name'] ?? null),
            'customer_phone' => $this->trimOrNull($note['customer_phone'] ?? null),
            'transaction_date' => $this->trimOrNull($note['transaction_date'] ?? null),
        ];
    }

    private function normalizeItems(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productLine = $this->normalizeProductLine($this->firstArrayItem($item['product_lines'] ?? []));
            $externalLine = $this->normalizeExternalLine($this->firstArrayItem($item['external_purchase_lines'] ?? []));
            $service = is_array($item['service'] ?? null) ? $item['service'] : [];

            $normalized[] = [
                'entry_mode' => $this->trimOrNull($item['entry_mode'] ?? null),
                'description' => $this->trimOrNull($item['description'] ?? null),
                'part_source' => $this->trimOrNull($item['part_source'] ?? null),
                'service' => [
                    'name' => $this->trimOrNull($service['name'] ?? null),
                    'price_rupiah' => $this->integerOrNull($service['price_rupiah'] ?? null),
                    'notes' => $this->trimOrNull($service['notes'] ?? null),
                ],
                'product_lines' => [$productLine],
                'external_purchase_lines' => [$externalLine],
            ];
        }

        return $normalized;
    }

    private function normalizeInlinePayment(mixed $value): array
    {
        $payment = is_array($value) ? $value : [];

        return [
            'decision' => $this->trimOrNull($payment['decision'] ?? 'skip') ?? 'skip',
            'payment_method' => $this->trimOrNull($payment['payment_method'] ?? null),
            'paid_at' => $this->trimOrNull($payment['paid_at'] ?? null),
            'amount_paid_rupiah' => $this->integerOrNull($payment['amount_paid_rupiah'] ?? null),
            'amount_received_rupiah' => $this->integerOrNull($payment['amount_received_rupiah'] ?? null),
            'notes' => $this->trimOrNull($payment['notes'] ?? null),
        ];
    }

    private function normalizeProductLine(array $line): array
    {
        return [
            'product_id' => $this->trimOrNull($line['product_id'] ?? null),
            'qty' => $this->integerOrNull($line['qty'] ?? null),
            'unit_price_rupiah' => $this->integerOrNull($line['unit_price_rupiah'] ?? null),
        ];
    }

    private function normalizeExternalLine(array $line): array
    {
        return [
            'label' => $this->trimOrNull($line['label'] ?? null),
            'qty' => $this->integerOrNull($line['qty'] ?? null),
            'unit_cost_rupiah' => $this->integerOrNull($line['unit_cost_rupiah'] ?? null),
        ];
    }

    private function firstArrayItem(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $first = array_values($value)[0] ?? [];

        return is_array($first) ? $first : [];
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function integerOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $value);

        if (! is_string($cleaned) || $cleaned === '') {
            return null;
        }

        return (int) $cleaned;
    }

    private function integerValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }

    private function blank(mixed $value): bool
    {
        return ! is_string($value) || trim($value) === '';
    }
}
