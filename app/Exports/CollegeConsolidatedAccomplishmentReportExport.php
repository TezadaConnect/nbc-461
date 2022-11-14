<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\User;
use App\Models\Report;
use App\Models\Dean;
use App\Models\FacultyExtensionist;
use App\Models\FacultyResearcher;
use Illuminate\Support\Facades\DB;
use App\Models\Maintenance\College;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Maintenance\Department;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Maintenance\GenerateTable;
use App\Models\Maintenance\GenerateColumn;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Services\NameConcatenationService;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CollegeConsolidatedAccomplishmentReportExport implements FromView, WithEvents
{
    function __construct($level, $type, $quarterGenerate, $yearGenerate,
         $collegeID, $collegeName, $facultyResearcher, $facultyExtensionist) {
        $this->level = $level;
        $this->type = $type;
        $this->quarterGenerate = $quarterGenerate;
        $this->yearGenerate = $yearGenerate;
        $this->collegeID = $collegeID;
        $this->collegeName = $collegeName;

        $user = Dean::where('deans.college_id', $this->collegeID)->join('users', 'users.id', 'deans.user_id')->select('users.*')->first();
        if (isset($user))
            $this->signature = $user->signature;
        else
            $this->signature = '';

        $this->arrangedName = (new NameConcatenationService())->getConcatenatedNameByUserAndRoleName($user, " ");
        if ($facultyResearcher != null) {
            $this->researcherSignature = User::where('id', $facultyResearcher['user_id'])->pluck('users.signature')->first();
            $this->researcherName = (new NameConcatenationService())->getConcatenatedNameByUserAndRoleName($facultyResearcher, "Researcher");
        } else {
            $this->researcherName = '';
            $this->researcherSignature = '';
        }

        if ($facultyExtensionist != null) {
            $this->extensionistSignature = User::where('id', $facultyExtensionist['user_id'])->pluck('users.signature')->first();
            $this->extensionistName = (new NameConcatenationService())->getConcatenatedNameByUserAndRoleName($facultyExtensionist, "Extensionist");
        } else {
            $this->extensionistName = '';
            $this->extensionistSignature = '';
        }
    }

    public function view(): View
    {
        $tableFormat;
        $tableColumns;
        $tableContents;
        $data;

        if($this->type == "academic"){
            if($this->level == "college"){
                $data = College::where('id', $this->collegeID)->first();
                $tableFormat = GenerateTable::where('type_id', 2)->get();
                $tableColumns = [];
                foreach ($tableFormat as $format){
                    if($format->is_table == "0")
                        $tableColumns[$format->id] = [];
                    else
                        $tableColumns[$format->id] = GenerateColumn::where('table_id', $format->id)->orderBy('order')->get()->toArray();
                }

                $tableContents = [];
                foreach ($tableFormat as $format){
                    if($format->is_table == "0" || $format->report_category_id == null)
                        $tableContents[$format->id] = [];
                    else
                        $tableContents[$format->id] = Report::
                            // ->where('user_roles.role_id', 1)
                            whereIn('reports.format', ['f', 'x'])
                            ->where('reports.report_category_id', $format->report_category_id)
                            ->where('reports.college_id', $this->collegeID)
                            ->where(function($query) {
                                $query->where('reports.researcher_approval', 1)
                                    ->orWhere('reports.extensionist_approval', 1)
                                    ->orWhereIn('reports.dean_approval', ['1', '2']);
                            })
                            ->where('reports.report_year', $this->yearGenerate)
                            ->where('reports.report_quarter', $this->quarterGenerate)
                            ->join('users', 'users.id', 'reports.user_id')
                            ->select('reports.*', DB::raw("CONCAT(COALESCE(users.last_name, ''), ', ', COALESCE(users.first_name, ''), ' ', COALESCE(users.middle_name, ''), ' ', COALESCE(users.suffix, '')) as faculty_name"))
                            ->orderBy('users.last_name')
                            ->get()->toArray();
                }
            }
        }
        elseif($this->type == "admin"){
            if($this->level == "college"){
                $data = College::where('id', $this->collegeID)->first();
                $tableFormat = GenerateTable::where('type_id', 1)->get();
                $tableColumns = [];
                foreach ($tableFormat as $format){
                    if($format->is_table == "0")
                        $tableColumns[$format->id] = [];
                    else
                        $tableColumns[$format->id] = GenerateColumn::where('table_id', $format->id)->orderBy('order')->get()->toArray();
                }

                $tableContents = [];
                foreach ($tableFormat as $format){
                    if($format->is_table == "0" || $format->report_category_id == null)
                        $tableContents[$format->id] = [];
                    else
                        $tableContents[$format->id] = Report::
                            // ->where('user_roles.role_id', 1)
                            whereIn('reports.format', ['a', 'x'])
                            ->where('reports.report_category_id', $format->report_category_id)
                            ->where('reports.college_id', $this->collegeID)
                            ->where(function($query) {
                                $query->where('reports.researcher_approval', 1)
                                    ->orWhere('reports.extensionist_approval', 1)
                                    ->orWhereIn('reports.dean_approval', ['1', '2']);
                            })
                            ->where('reports.report_year', $this->yearGenerate)
                            ->where('reports.report_quarter', $this->quarterGenerate)
                            ->join('users', 'users.id', 'reports.user_id')
                            ->join('departments', 'departments.id', 'reports.department_id')
                            ->select('reports.*', DB::raw("CONCAT(COALESCE(users.last_name, ''), ', ', COALESCE(users.first_name, ''), ' ', COALESCE(users.middle_name, ''), ' ', COALESCE(users.suffix, '')) as faculty_name"))
                            ->orderBy('departments.name')
                            ->orderBy('users.last_name')
                            ->get()->toArray();
                }
            }
        }

        $this->tableFormat = $tableFormat;
        $this->tableColumns = $tableColumns;
        $this->tableContents = $tableContents;
        $level = $this->level;
        $type = $this->type;
        $yearGenerate = $this->yearGenerate;
        $quarterGenerate = $this->quarterGenerate;
        return view('reports.generate.example', compact('tableFormat', 'tableColumns', 'tableContents', 'level', 'data', 'type', 'yearGenerate', 'quarterGenerate'));
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(Aftersheet $event) {
                $event->sheet->getSheetView()->setZoomScale(70);
                $event->sheet->getStyle('A1:Z500')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setName('Arial');
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setSize(12);
                $event->sheet->getDefaultColumnDimension()->setWidth(33);
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->freezePane('C1');
                $event->sheet->setCellValue('A1', 'CONSOLIDATED QUARTERLY ACCOMPLISHMENT REPORT');
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 20,
                    ]
                ]);

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->mergeCells('B2:C2');
                if ($this->type == "academic")
                    $event->sheet->setCellValue('B2', 'COLLEGE/BRANCH/CAMPUS:');
                elseif ($this->type == "admin")
                    $event->sheet->setCellValue('B2', 'OFFICE:');

                $event->sheet->getStyle('B2')->applyFromArray([
                    'font' => [
                        'size' => 16,
                        'bold' => true,
                    ]
                ]);
                $event->sheet->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->mergeCells('D2:F2');
                $event->sheet->setCellValue('D2', $this->collegeName);
                $event->sheet->getStyle('D2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('D2:F2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('D2')->applyFromArray([
                    'font' => [
                        'size' => 16,
                    ]
                ]);

                // Name
                // if ($this->isFacultyRes != null) {
                //     $event->sheet->setCellValue('C3', 'FACULTY RESEARCHER:');
                // } elseif ($this->isFacultyExt != null) {
                //     $event->sheet->setCellValue('C3', 'FACULTY EXTENSIONIST:');
                // } else {
                //     $event->sheet->setCellValue('C3', 'DEAN/DIRECTOR:');
                // }

                // $event->sheet->getStyle('C3')->applyFromArray([
                //     'font' => [
                //         'size' => 16,
                //     ]
                // ]);
                // $event->sheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                // $event->sheet->mergeCells('D3:F3');
                // $event->sheet->setCellValue('D3', $this->arrangedName);
                // $event->sheet->getStyle('D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                // $event->sheet->getStyle('D3:F3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                // $event->sheet->getStyle('D3')->applyFromArray([
                //     'font' => [
                //         'size' => 16,
                //     ]
                // ]);

                $event->sheet->setCellValue('C3', 'QUARTER:');
                $event->sheet->getStyle('C3')->applyFromArray([
                    'font' => [
                        'size' => 16,
                    ]
                ]);
                $event->sheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->setCellValue('D3', $this->quarterGenerate);
                $event->sheet->getStyle('D3')->applyFromArray([
                    'font' => [
                        'size' => 16,
                    ]
                ]);
                $event->sheet->getStyle('D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('D3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->setCellValue('E3', 'CALENDAR YEAR:');
                $event->sheet->getStyle('E3')->applyFromArray([
                    'font' => [
                        'size' => 16,
                    ]
                ]);
                $event->sheet->getStyle('E3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->setCellValue('F3', $this->yearGenerate);
                $event->sheet->getStyle('F3')->applyFromArray([
                    'font' => [
                        'size' => 16,
                    ]
                ]);
                $event->sheet->getStyle('F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('F3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $count = 7;
                $tableFormat = $this->tableFormat;
                $tableColumns = $this->tableColumns;
                $tableContents = $this->tableContents;
                foreach($tableFormat as $format) {
                    if($format->is_table == '0'){

                        //title
                        $event->sheet->mergeCells('A'.$count.':K'.$count);
                        $event->sheet->getStyle('A'.$count)->getAlignment()->setWrapText(true);
                        $event->sheet->getStyle('A'.$count)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("FFC00000");
                        $event->sheet->getStyle('A'.$count)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
                        $event->sheet->getStyle('A'.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $event->sheet->getRowDimension($count)->setRowHeight(30);
                        $event->sheet->getStyle('A'.$count)->applyFromArray([
                            'font' => [
                                'name' => 'Arial',
                            ]
                        ]);
                        $count++;

                    }
                    elseif($format->is_table == '1') {
                        $length = count($tableColumns[$format->id]);
                        if ($length == null){
                            $length = 4;
                        }
                        else{
                            $length = $length+4;
                        }
                        $letter = Coordinate::stringFromColumnIndex($length);

                        // title
                        $event->sheet->mergeCells('A'.$count.':'.$letter.$count);
                        $event->sheet->getStyle('A'.$count)->getAlignment()->setWrapText(true);
                        if ($format->is_individual == '0') {
                            $event->sheet->getStyle('A'.$count)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("FF002060");
                            $event->sheet->getStyle('A'.$count)->getFont()->getColor()->setARGB('ffffffff');
                        }
                        else {
                            $event->sheet->getStyle('A'.$count)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFC000");
                            $event->sheet->getStyle('A'.$count)->getFont()->getColor()->setARGB('FFC00000');
                        }

                        $event->sheet->getRowDimension($count)->setRowHeight(30);
                        $event->sheet->getStyle('A'.$count)->applyFromArray([
                            'font' => [
                                'name' => 'Arial',
                            ]
                        ]);
                        $count++;

                        //column
                        $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getAlignment()->setWrapText(true);
                        $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $event->sheet->getStyle('A'.$count.':'.$letter.$count)->applyFromArray([
                            'font' => [
                                'name' => 'Arial',
                                'bold' => true,
                                'size' => 14
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF515256'],
                                ],
                            ],
                        ]);
                        $count++;

                        //contents
                        foreach($tableContents[$format->id] as $contents){
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getAlignment()->setWrapText(true);
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->applyFromArray([
                                'font' => [
                                    'name' => 'Arial',
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    ],
                                ],
                            ]);
                            $count++;
                        }

                        if($tableContents[$format->id] == null){
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getAlignment()->setWrapText(true);
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getStyle('A'.$count.':'.$letter.$count)->applyFromArray([
                                'font' => [
                                    'name' => 'Arial',
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    ],
                                ],
                            ]);
                            $count++;
                        }

                        $footers = json_decode($format->footers);
                        if ($footers != null){
                            foreach ($footers as $footer){
                                $event->sheet->getStyle('A'.$count)->applyFromArray([
                                    'font' => [
                                        'name' => 'Arial',
                                    ]
                                ]);
                                $count++;
                            }
                        }

                        $count += 2;
                    }
                }
                $count = $count + 2;
                $event->sheet->setCellValue('A'.$count, 'Validated By:');
                $event->sheet->getStyle('A'.$count)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 14
                    ],
                ]);
                $count = $count + 5;
                if ($this->signature != null) {
                    $path = storage_path('app/documents/'. $this->signature);
                    $coordinates = 'A'.$count-4;
                    $sheet = $event->sheet->getDelegate();
                    echo $this->addImage($path, $coordinates, $sheet);
                }

                $event->sheet->setCellValue('A'.$count, $this->arrangedName);
                $event->sheet->getStyle('A'.$count.':'.'E'.$count)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 14
                    ],
                ]);
                $event->sheet->getStyle('A'.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $count = $count + 1;
                    if ($this->type == "academic")
                        $event->sheet->setCellValue('A'.$count, 'Dean/Director, '.$this->collegeName);
                    elseif ($this->type == "admin")
                        $event->sheet->setCellValue('A'.$count, 'Director, '.$this->collegeName);

                $event->sheet->getStyle('A'.$count.':'.'E'.$count)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 14
                    ],
                ]);

                $event->sheet->getStyle('A'.$count)->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('A'.$count)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('A'.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }

    public function addImage($path, $coordinates, $sheet) {
        $signature = new Drawing();
        $signature->setPath($path);
        $signature->setCoordinates($coordinates);
        $signature->setWidth(30);
        $signature->setHeight(80);
        $signature->setWorksheet($sheet);
    }
}
