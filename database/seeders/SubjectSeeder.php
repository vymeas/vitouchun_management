<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $preschoolParents = ['ភាសាខ្មែរ', 'គណិតវិទ្យា', 'វិទ្យាសាស្ត្រ', 'សិក្សាសង្គម', 'គំនូរ', 'ចម្រៀង-របាំ', 'អប់រំកាយ', 'វិន័យ-សីលធម៌'];
        $components = ['រៀនអាន', 'រៀនសរសេរ', 'ការស្ដាប់'];
        $primarySubjects = ['រៀនអាន', 'សរសេរតាមអាន', 'តែងសេចក្ដី', 'មេសូត្រ', 'រឿងនិទាន', 'វេយ្យាករណ៍៍', 'អក្សរផ្ចង់', 'នព្វន្តមាត្រាប្រព័ន្ធ', 'វិទ្យាសាស្រ្ត', 'ប្រវត្តិវិទ្យា', 'ភូមិវិទ្យា', 'ពលរដ្ឋ', 'គំនូរ', 'ចម្រៀង-របាំ', 'អង់គ្លេស', 'កុំព្យូទ័រ', 'អប់រំកាយ', 'វិន័យ-សីលធម៌'];

        foreach (Branch::query()->get() as $branch) {
            DB::transaction(function () use ($branch, $preschoolParents, $components, $primarySubjects) {
                $number = $this->nextNumber($branch->id);
                $preschoolGrades = $this->gradesForLevel($branch->id, 'preschool');
                $primaryGrades = $this->gradesForLevel($branch->id, 'primary');
                $parents = [];

                foreach ($preschoolParents as $name) {
                    $parents[$name] = $this->saveSubject($branch->id, $name, 'preschool', null, $number, $preschoolGrades);
                }

                foreach (['ភាសាខ្មែរ', 'គណិតវិទ្យា'] as $parentName) {
                    foreach ($components as $name) {
                        $this->saveSubject($branch->id, $name, 'preschool', $parents[$parentName]->id, $number, $preschoolGrades);
                    }
                }

                foreach ($primarySubjects as $name) {
                    $this->saveSubject($branch->id, $name, 'primary', null, $number, $primaryGrades);
                }
            });
        }
    }

    private function saveSubject(int $branchId, string $name, string $level, ?int $parentId, int &$number, array $grades): Subject
    {
        $subject = Subject::firstOrNew([
            'branch_id' => $branchId,
            'name_kh' => $name,
            'level' => $level,
            'parent_subject_id' => $parentId,
        ]);

        if (!$subject->exists) {
            $subject->code = sprintf('SUB%03d', $number++);
        }

        $subject->fill([
            'name_en' => $subject->name_en,
            'credit' => 0,
            'type' => 'core',
            'subject_type' => null,
            'display_order' => $number,
            'status' => 'active',
        ])->save();

        if ($grades) {
            $subject->grades()->syncWithoutDetaching(collect($grades)->mapWithKeys(fn ($gradeId, $order) => [$gradeId => ['display_order' => $order, 'status' => 'active']])->all());
        }

        return $subject;
    }

    private function nextNumber(int $branchId): int
    {
        return Subject::where('branch_id', $branchId)->get(['code'])->reduce(function (int $highest, Subject $subject): int {
            return preg_match('/(\d+)$/', (string) $subject->code, $matches) ? max($highest, (int) $matches[1] + 1) : $highest;
        }, 1);
    }

    private function gradesForLevel(int $branchId, string $level): array
    {
        return Grade::where('branch_id', $branchId)->get()->filter(function (Grade $grade) use ($level): bool {
            if ($grade->education_level) {
                return $grade->education_level === $level;
            }

            if ($level === 'preschool') {
                return Str::contains($grade->name, 'មត្តេយ្យ');
            }

            return Str::contains($grade->name, 'ថ្នាក់ទី');
        })->sortBy('level')->pluck('id')->values()->all();
    }
}
