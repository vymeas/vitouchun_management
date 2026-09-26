<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTeacherScheduleRequest extends FormRequest
{
    public function authorize():bool{return $this->user()!==null;}
    public function rules():array{return ['teacher_id'=>['required','exists:users,id'],'class_id'=>['required','exists:school_classes,id'],'subject_id'=>['required','exists:subjects,id'],'academic_year_id'=>['required','exists:academic_years,id'],'term_id'=>['nullable','exists:terms,id'],'day_of_week'=>['required','in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],'start_time'=>['required','date_format:H:i'],'end_time'=>['required','date_format:H:i','after:start_time'],'room'=>['nullable','string','max:50']];}
}
