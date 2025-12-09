<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LayerUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'nama_layer' => ['required', 'string', 'max:255'],
            'deskripsi'  => ['nullable', 'string'],
            'map_id'     => ['required', 'exists:maps,id'],

            'features'          => ['nullable', 'array'],
            'features.*.id'     => ['required', 'integer', 'exists:map_features,id'],
            'features.*.name'   => ['nullable', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string'],

            // ⚠️ yang tadinya "required" sebaiknya dibuat "nullable"
            'features.*.geometry'      => ['nullable', 'string'],
            'features.*.geometry_type' => ['nullable', 'in:marker,circle,polygon,polyline'],

            'features.*.stroke_color' => ['nullable', 'string'],
            'features.*.fill_color'   => ['nullable', 'string'],
            'features.*.weight'       => ['nullable', 'numeric'],
            'features.*.opacity'      => ['nullable', 'numeric'],
            'features.*.radius'       => ['nullable', 'numeric'],
            'features.*.icon_url'     => ['nullable', 'string'],

            'features.*.image'        => ['nullable', 'image', 'max:2048'],
            'features.*.remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
