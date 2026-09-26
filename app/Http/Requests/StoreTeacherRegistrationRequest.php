<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTeacherRegistrationRequest extends FormRequest
{
    public function authorize():bool{return $this->user()!==null;}
    public function rules():array{return ['full_name_kh'=>['required','string','max:255'],'full_name'=>['required','string','max:255'],'username'=>['required','string','max:50','unique:users,username'],'email'=>['nullable','email','unique:users,email'],'phone'=>['nullable','string','max:30'],'password'=>['required','string','min:8','confirmed'],'branch_id'=>['nullable','exists:branches,id'],'gender'=>['required','in:M,F,O'],'dob'=>['nullable','date'],'national_id'=>['nullable','string','max:100'],'specialization'=>['nullable','string','max:255'],'education_level'=>['nullable','string','max:100'],'major'=>['nullable','string','max:255'],'institution'=>['nullable','string','max:255'],'graduation_year'=>['nullable','integer','min:1950','max:2100'],'joining_date'=>['required','date'],'basic_salary'=>['nullable','numeric','gte:0'],'remark'=>['nullable','string'],'photo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048']];}
}
