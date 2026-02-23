<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'post_id' => ['required', 'exists:posts,id'],
            'parent_id' => ['nullable', 'exists:comments,id'],
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_email' => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->parent_id && ! $validator->errors()->has('parent_id')) {
                    $parent = Comment::find($this->parent_id);

                    if ($parent && $parent->post_id !== (int) $this->post_id) {
                        $validator->errors()->add('parent_id', 'The parent comment must belong to the same post.');
                    }
                }
            },
        ];
    }
}
