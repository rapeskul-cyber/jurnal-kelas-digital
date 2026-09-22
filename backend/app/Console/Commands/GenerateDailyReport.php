<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClassModel;
use App\Models\JournalEntry;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateDailyReport extends Command
{
    protected $signature = 'report:daily {--date=}';
    protected $description = 'Generate daily report for all classes (PDF/Excel)';

    public function handle(): int
    {
        $date = $this->option('date') ?? Carbon::today()->toDateString();
        $classes = ClassModel::all();

        foreach ($classes as $class) {
            $journals = JournalEntry::where('class_id', $class->id)
                ->whereDate('created_at', $date)
                ->with('subject', 'author')
                ->get();

            $attendances = Attendance::where('class_id', $class->id)
                ->where('date', $date)
                ->with('student', 'subject')
                ->get();

            $summary = [
                'class' => $class->name,
                'date' => $date,
                'total_journals' => $journals->count(),
                'total_attendance' => $attendances->count(),
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'journals' => $journals->toArray(),
                'attendances' => $attendances->toArray(),
            ];

            // Save JSON report (PDF/Excel generation requires dompdf/phpspreadsheet packages)
            $filename = "reports/{$date}/class_{$class->id}.json";
            Storage::disk('local')->put($filename, json_encode($summary, JSON_PRETTY_PRINT));

            $this->info("Report generated: {$filename}");
        }

        return self::SUCCESS;
    }
}