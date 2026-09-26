<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTeacherAssignmentRequest extends FormRequest
{
    public function authorize():bool{return $this->user()!==null;}
    public function rules():array{return ['teacher_id'=>['required','exists:users,id'],'class_id'=>['required','exists:school_classes,id'],'academic_year_id'=>['required','exists:academic_years,id'],'role'=>['required','in:class_teacher,assistant_teacher,subject_teacher'],'start_date'=>['nullable','date'],'end_date'=>['nullable','date','after_or_equal:start_date'],'remark'=>['nullable','string']];}
}
