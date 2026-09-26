<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['name_kh'=>['required','string','max:255'],'name_en'=>['nullable','string','max:255'],'gender'=>['required','in:M,F,O'],'dob'=>['nullable','date'],'phone'=>['nullable','string','max:30'],'email'=>['nullable','email','max:255'],'address'=>['nullable','string'],'emergency_contact'=>['nullable','string','max:255'],'emergency_phone'=>['nullable','string','max:30'],'branch_id'=>['nullable','exists:branches,id'],'department_id'=>['nullable','exists:departments,id'],'position_id'=>['nullable','exists:positions,id'],'joining_date'=>['required','date'],'employment_type'=>['required','in:full_time,part_time,contract,temporary,intern'],'basic_salary'=>['required','numeric','gte:0'],'status'=>['required','in:active,inactive,on_leave,suspended,resigned,terminated,retired'],'remark'=>['nullable','string'],'photo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048']]; }
}
