<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TeacherSubjectAssignment extends Model
{
    protected $fillable=['teacher_id','class_id','subject_id','academic_year_id','term_id','branch_id','weekly_hours','start_date','end_date','status'];
    protected function casts(): array{return ['start_date'=>'date','end_date'=>'date'];}
    public function teacher(){return $this->belongsTo(User::class,'teacher_id');} public function schoolClass(){return $this->belongsTo(SchoolClass::class,'class_id');} public function subject(){return $this->belongsTo(Subject::class);} public function academicYear(){return $this->belongsTo(AcademicYear::class);} public function term(){return $this->belongsTo(Term::class);} public function branch(){return $this->belongsTo(Branch::class);}
    public function scopeForBranch($q,?int $id){return $id?$q->where('branch_id',$id):$q;}
}
