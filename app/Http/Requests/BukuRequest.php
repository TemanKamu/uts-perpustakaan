<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BukuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('patch') ? 'sometimes' : 'required';

        return [
            'id_kategori' => [$required, 'required', 'integer', 'min:1', Rule::exists('tb_kategori', 'id_kategori')],
            'judul_buku' => [$required, 'required', 'string', 'max:255'],
            'nama_pengarang' => [$required, 'required', 'string', 'max:150'],
            'penerbit' => ['sometimes', 'nullable', 'string', 'max:150'],
            'tahun_terbit' => ['sometimes', 'nullable', 'integer', 'digits:4', 'min:1000', 'max:'.(now()->year + 1)],
            'cetakan' => ['sometimes', 'nullable', 'string', 'max:50'],
            'tempat_terbit' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tgl_terima' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'asal_buku' => ['sometimes', 'nullable', 'string', 'max:100'],
            'jumlah_buku' => ['sometimes', 'required', 'integer', 'min:0', 'max:4294967295'],
            'keterangan' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'id_buku' => ['prohibited'],
            'tgl_input' => ['prohibited'],
        ];
    }
}
