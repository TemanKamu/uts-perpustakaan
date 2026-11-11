<?php

namespace App\Http\Requests;

use App\Models\Kategori;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique('tb_kategori', 'nama_kategori');
        $kategori = $this->route('kategori');

        // ID pengecualian berasal dari model hasil route binding, bukan body.
        if ($kategori instanceof Kategori) {
            $unique->ignore($kategori->getKey(), 'id_kategori');
        }

        return [
            'nama_kategori' => [
                $this->isMethod('patch') ? 'sometimes' : 'required',
                'required', 'string', 'max:100', $unique,
            ],
            'id_kategori' => ['prohibited'],
        ];
    }
}
