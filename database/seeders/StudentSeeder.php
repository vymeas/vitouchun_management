<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('code', 'MAIN')->firstOrFail();
        $createdBy = User::where('username', 'admin')->value('id');
        $students = [
            ['សុខ ដារ៉ា', 'Sok Dara', 'M', '2016-01-15', 'សុខ សុខា', '012345678'],
            ['ស្រី ពៅ', 'Srey Pov', 'F', '2016-04-22', 'ស្រី វណ្ណា', '012345679'],
            ['ចាន់ វិចិត្រ', 'Chan Vicheth', 'M', '2017-02-10', 'ចាន់ សុភា', '012345680'],
            ['លី សុភា', 'Ly Sophea', 'F', '2017-06-18', 'លី សុវណ្ណ', '012345681'],
            ['គង់ រដ្ឋា', 'Kong Ratha', 'M', '2016-09-03', 'គង់ វាសនា', '012345682'],
            ['ហេង មាលី', 'Heng Mali', 'F', '2018-01-28', 'ហេង សុខុន', '012345683'],
            ['វ៉ាន់ ចាន់ណា', 'Van Channa', 'F', '2017-11-12', 'វ៉ាន់ វុទ្ធី', '012345684'],
            ['ជា វិសាល', 'Chea Visal', 'M', '2016-12-07', 'ជា សំណាង', '012345685'],
            ['នួន ស្រីពេជ្រ', 'Nuon Sreypech', 'F', '2018-03-19', 'នួន សុផល', '012345686'],
            ['ប៊ុន ធារ៉ា', 'Bun Theara', 'M', '2017-08-25', 'ប៊ុន សាវឿន', '012345687'],
            ['ឈុន ដាលីន', 'Chhun Dalin', 'F', '2016-05-14', 'ឈុន ដារ៉ា', '012345688'],
            ['ផន សុវណ្ណារ៉ា', 'Phon Sovannara', 'M', '2018-07-09', 'ផន សុវណ្ណ', '012345689'],
            ['អ៊ុក មុនីរ័ត្ន', 'Ouk Moniroth', 'M', '2017-10-30', 'អ៊ុក ចាន់ថា', '012345690'],
            ['ទូច ស្រីនិច', 'Touch Sreynich', 'F', '2018-02-16', 'ទូច សុវណ្ណ', '012345691'],
            ['កែវ រតនា', 'Keo Rattana', 'F', '2016-07-21', 'កែវ រដ្ឋា', '012345692'],
            ['សំអាង វណ្ណៈ', 'Sam Ang Vanna', 'M', '2017-04-06', 'សំអាង សុខ', '012345693'],
            ['ម៉ៅ សុជាតា', 'Mao Socheata', 'F', '2018-09-11', 'ម៉ៅ សុភ័ក្រ', '012345694'],
            ['រឿន ពិសិដ្ឋ', 'Roeun Piseth', 'M', '2016-11-23', 'រឿន វណ្ណី', '012345695'],
            ['សេង កញ្ញា', 'Seng Kanya', 'F', '2017-01-31', 'សេង សុវណ្ណ', '012345696'],
            ['ដួង សុខហេង', 'Duong Sokheng', 'M', '2018-05-27', 'ដួង សុខា', '012345697'],
        ];

        foreach ($students as $index => [$nameKh, $nameEn, $gender, $dob, $emergencyName, $emergencyPhone]) {
            $code = sprintf('Stu%06d', $index + 1);
            Student::updateOrCreate(
                ['code' => $code],
                [
                    'student_code' => $code,
                    'name_kh' => $nameKh,
                    'khmer_name' => $nameKh,
                    'name_en' => $nameEn,
                    'english_name' => $nameEn,
                    'gender' => $gender,
                    'dob' => $dob,
                    'date_of_birth' => $dob,
                    'emergency_contact_name' => $emergencyName,
                    'emergency_contact_phone' => $emergencyPhone,
                    'student_type' => 'សិស្សធម្មតា',
                    'status' => 'active',
                    'created_by' => $createdBy,
                    'branch_id' => $branch->id,
                ],
            );
        }
    }
}
