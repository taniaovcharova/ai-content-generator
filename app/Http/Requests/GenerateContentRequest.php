<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic'  => ['required', 'string', 'max:500'],
            'tone'   => ['sometimes', 'string', 'in:professional,casual,creative'],
            'length' => ['sometimes', 'string', 'in:short,medium,long'],
        ];
    }
}