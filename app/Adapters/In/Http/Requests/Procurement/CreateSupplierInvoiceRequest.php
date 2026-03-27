<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $autoReceive = $this->input('auto_receive');

        $this->merge([
            'nama_pt_pengirim' => $this->trimOrNull($this->input('nama_pt_pengirim')),
            'tanggal_pengiriman' => $this->trimOrNull($this->input('tanggal_pengiriman')),
            'tanggal_terima' => $this->trimOrNull($this->input('tanggal_terima')),
            'auto_receive' => is_bool($autoReceive) ? $autoReceive : $this->toNullableBool($autoReceive),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama_pt_pengirim' => ['required', 'string'],
            'tanggal_pengiriman' => ['required', 'date_format:Y-m-d'],
            'auto_receive' => ['nullable', 'boolean'],
            'tanggal_terima' => ['nullable', 'date_format:Y-m-d'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'string'],
            'lines.*.qty_pcs' => ['required', 'integer', 'min:1'],
            'lines.*.line_total_rupiah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(fn (Validator $v) => $this->validateDates($v));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_pt_pengirim.required' => 'Nama PT pengirim wajib diisi.',
            'nama_pt_pengirim.string' => 'Nama PT pengirim harus berupa teks.',
            'tanggal_pengiriman.required' => 'Tanggal pengiriman wajib diisi.',
            'tanggal_pengiriman.date_format' => 'Tanggal pengiriman harus menggunakan format YYYY-MM-DD.',
            'auto_receive.boolean' => 'Auto receive tidak valid.',
            'tanggal_terima.date_format' => 'Tanggal terima harus menggunakan format YYYY-MM-DD.',
            'lines.required' => 'Rincian supplier invoice wajib diisi.',
            'lines.array' => 'Rincian supplier invoice tidak valid.',
            'lines.min' => 'Rincian supplier invoice minimal harus memiliki 1 baris.',
            'lines.*.product_id.required' => 'Produk pada rincian wajib dipilih.',
            'lines.*.product_id.string' => 'Produk pada rincian tidak valid.',
            'lines.*.qty_pcs.required' => 'Jumlah pada rincian wajib diisi.',
            'lines.*.qty_pcs.integer' => 'Jumlah pada rincian harus berupa bilangan bulat.',
            'lines.*.qty_pcs.min' => 'Jumlah pada rincian minimal 1.',
            'lines.*.line_total_rupiah.required' => 'Total rincian wajib diisi.',
            'lines.*.line_total_rupiah.integer' => 'Total rincian harus berupa bilangan bulat.',
            'lines.*.line_total_rupiah.min' => 'Total rincian minimal 1 rupiah.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_pt_pengirim' => 'nama PT pengirim',
            'tanggal_pengiriman' => 'tanggal pengiriman',
            'auto_receive' => 'auto receive',
            'tanggal_terima' => 'tanggal terima',
            'lines' => 'rincian supplier invoice',
            'lines.*.product_id' => 'produk pada rincian',
            'lines.*.qty_pcs' => 'jumlah pada rincian',
            'lines.*.line_total_rupiah' => 'total rincian',
        ];
    }

    private function validateDates(Validator $validator): void
    {
        $autoReceive = $this->input('auto_receive');
        $tanggalTerima = $this->input('tanggal_terima');

        if ($autoReceive === true && $tanggalTerima !== null && (string) $tanggalTerima < (string) $this->input('tanggal_pengiriman')) {
            $validator->errors()->add(
                'tanggal_terima',
                'Tanggal terima tidak boleh lebih awal dari tanggal pengiriman.'
            );
        }
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toNullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value === 1 || $value === '1' || $value === true || $value === 'true') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === false || $value === 'false') {
            return false;
        }

        return null;
    }
}
