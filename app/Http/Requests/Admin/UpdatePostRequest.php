<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\File;

class UpdatePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => [File::image()->max(5 * 1024)],
            'cover_index' => ['nullable', 'integer', 'min:0'],
            'existing_photos' => ['nullable', 'array'],
            'existing_photos.*' => ['integer', 'exists:post_photos,id'],
            'cover_photo_id' => ['nullable', 'integer'],
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'travel_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', new Enum(PostCategory::class)],
            'status' => ['required', new Enum(PostStatus::class)],
        ];
    }
}
