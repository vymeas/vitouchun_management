<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTeacherSubjectRequest extends FormRequest
{
    public function authorize():bool{return $this->user()!==null;}
    public function rules():array{return ['teacher_id'=>['required','exists:users,id'],'class_id'=>['required','exists:school_classes,id'],'subject_id'=>['required','exists:subjects,id'],'academic_year_id'=>['required','exists:academic_years,id'],'term_id'=>['nullable','exists:terms,id'],'weekly_hours'=>['required','integer','min:0','max:60'],'start_date'=>['nullable','date'],'end_date'=>['nullable','date','after_or_equal:start_date']];}
}
