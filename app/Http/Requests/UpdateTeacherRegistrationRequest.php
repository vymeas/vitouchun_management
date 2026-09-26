<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateTeacherRegistrationRequest extends StoreTeacherRegistrationRequest
{
    public function rules():array
    {
        $rules=parent::rules(); $user=$this->route('teacher'); $rules['username']=['required','string','max:50','unique:users,username,'.($user?->id ?? 0)]; $rules['email']=['nullable','email','unique:users,email,'.($user?->id ?? 0)]; $rules['password']=['nullable','string','min:8','confirmed']; return $rules;
    }
}
