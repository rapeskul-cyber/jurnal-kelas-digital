<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\ClassJournal;
use App\Models\MaterialJournal;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\AuditLog;
use App\Models\Setting;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        switch ($user->role) {
            case 'SUPERADMIN':
                return $this->superadminDashboard($user, $today);
            case 'SISWA':
                return $this->siswaDashboard($user);
            case 'SEKRETARIS':
                return $this->sekretarisDashboard($user, $today);
            case 'GURU_PENGAJAR':
                return $this->pengajarDashboard($user, $today);
            case 'GURU_PEMBIMBING':
                return $this->pembimbingDashboard($user, $today);
            default:
                return response()->json(['message' => 'Role tidak valid'], 400);
        }
    }

    private function siswaDashboard(User $user)
    {
        // Calculate personal attendance counts
        $records = AttendanceRecord::where('student_id', $user->id)->get();
        $totalRecords = $records->count() ?: 1;
        $hadirCount = $records->where('status', 'HADIR')->count();
        $sakitCount = $records->where('status', 'SAKIT')->count();
        $izinCount = $records->where('status', 'IZIN')->count();
        $alphaCount = $records->where('status', 'ALPHA')->count();

        $percentage = round(($hadirCount / $totalRecords) * 100);

        // Recent personal attendance
        $recentAttendance = AttendanceRecord::with(['session.schoolClass'])
            ->where('student_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($rec) {
                return [
                    'id' => $rec->id,
                    'date' => Carbon::parse($rec->session->date)->translatedFormat('l, d M Y'),
                    'status' => $rec->status,
                    'note' => $rec->note ?: 'Sesuai Jadwal',
                    'school_hours' => $rec->session->school_hours,
                ];
            });

        // Recent material journals
        $recentMaterials = MaterialJournal::with(['subject', 'teacher'])
            ->latest('date')
            ->take(5)
            ->get()
            ->map(function ($mat) {
                return [
                    'id' => $mat->id,
                    'date' => Carbon::parse($mat->date)->translatedFormat('d M Y'),
                    'subject' => $mat->subject->name ?? 'Mata Pelajaran',
                    'topic' => $mat->topic,
                    'teacher' => $mat->teacher->name ?? 'Guru',
                ];
            });

        return response()->json([
            'stats' => [
                'hadir_hari' => $hadirCount . ' Hari',
                'hadir_percent' => $percentage . '% Kehadiran',
                'sakit_hari' => $sakitCount . ' Hari',
                'izin_hari' => $izinCount . ' Hari',
                'alpha_hari' => $alphaCount . ' Hari',
            ],
            'recent_attendance' => $recentAttendance,
            'recent_materials' => $recentMaterials,
        ]);
    }

    private function sekretarisDashboard(User $user, $today)
    {
        $classId = $user->class_id ?? 1;

        $session = AttendanceSession::where('class_id', $classId)
            ->whereDate('date', $today)
            ->with(['records.student'])
            ->first();

        // If today has not been filled, use the latest session so realistic Sakit/Izin/Alpha records are shown
        if (!$session) {
            $session = AttendanceSession::where('class_id', $classId)
                ->with(['records.student'])
                ->latest('date')
                ->first();
        }

        $students = User::where('class_id', $classId)
            ->whereIn('role', ['SISWA', 'SEKRETARIS'])
            ->orderBy('name')
            ->get();

        $hadir = 0; $sakit = 0; $izin = 0; $alpha = 0;
        $attendanceList = [];

        if ($session && $session->records->count() > 0) {
            foreach ($session->records as $idx => $rec) {
                if ($rec->status === 'HADIR') $hadir++;
                elseif ($rec->status === 'SAKIT') $sakit++;
                elseif ($rec->status === 'IZIN') $izin++;
                elseif ($rec->status === 'ALPHA') $alpha++;

                $attendanceList[] = [
                    'no' => $idx + 1,
                    'student_id' => $rec->student_id,
                    'name' => $rec->student->name ?? 'Siswa',
                    'nis' => $rec->student->nip_nis ?? '-',
                    'status' => $rec->status,
                    'note' => $rec->note,
                    'verified' => $session->verification_status === 'TERVERIFIKASI',
                ];
            }
        } else {
            foreach ($students as $idx => $std) {
                $hadir++;
                $attendanceList[] = [
                    'no' => $idx + 1,
                    'student_id' => $std->id,
                    'name' => $std->name,
                    'nis' => $std->nip_nis ?? '-',
                    'status' => 'HADIR',
                    'note' => null,
                    'verified' => false,
                ];
            }
        }

        $total = count($attendanceList) ?: 1;
        $percentage = round(($hadir / $total) * 100);

        return response()->json([
            'class_name' => $user->schoolClass->name ?? 'Kelas Industri',
            'session_date' => $session ? Carbon::parse($session->date)->translatedFormat('l, d M Y') : 'Hari ini',
            'stats' => [
                'hadir_count' => $hadir . ' Siswa',
                'hadir_percent' => $percentage . '% Tingkat Kehadiran',
                'izin_count' => $izin . ' Siswa',
                'sakit_count' => $sakit . ' Siswa',
                'alpha_count' => $alpha . ' Siswa',
            ],
            'attendance_list' => $attendanceList,
            'session_status' => $session ? $session->status : 'DRAFT',
            'verification_status' => $session ? $session->verification_status : 'BELUM_KONFIRMASI',
        ]);
    }

    private function pengajarDashboard(User $user, $today)
    {
        $taughtClasses = $user->taughtClasses()->count() ?: 4;
        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $user->id)
            ->get();

        $filledJournals = ClassJournal::where('confirmed_by_id', $user->id)
            ->where('date', $today)
            ->count();
        $totalSchedules = $schedules->count() ?: 5;
        $unfilled = max(0, $totalSchedules - $filledJournals);

        $scheduleList = $schedules->map(function ($sch) use ($today) {
            $journal = ClassJournal::where('schedule_id', $sch->id)
                ->where('date', $today)
                ->first();

            return [
                'id' => $sch->id,
                'time' => $sch->start_time . ' - ' . $sch->end_time,
                'subject' => $sch->subject->name ?? 'Mata Pelajaran',
                'class' => $sch->schoolClass->name ?? 'Kelas',
                'status' => $journal ? 'Terisi' : 'Belum Diisi',
                'verification' => $journal->verification_status ?? 'BELUM_DIISI',
            ];
        });

        $taughtClassIds = $user->taughtClasses()->pluck('classes.id');
        $classesToSummarize = SchoolClass::whereIn('id', $taughtClassIds)->get();
        if ($classesToSummarize->isEmpty()) {
            $classesToSummarize = SchoolClass::take(2)->get();
        }

        $attendanceSummary = $classesToSummarize->map(function ($cls) use ($today) {
            $totalStudents = User::where('class_id', $cls->id)->whereIn('role', ['SISWA', 'SEKRETARIS'])->count() ?: 36;
            $session = AttendanceSession::where('class_id', $cls->id)->where('date', $today)->first();
            $presentCount = $session ? $session->records()->where('status', 'HADIR')->count() : 0;
            $pct = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;
            return [
                'class' => $cls->name,
                'present_ratio' => "{$presentCount}/{$totalStudents} siswa",
                'percent' => $pct,
            ];
        });

        $teacherScheduleCount = Schedule::where('teacher_id', $user->id)->count();
        $filledTodayCount = ClassJournal::whereIn('schedule_id', Schedule::where('teacher_id', $user->id)->pluck('id'))
            ->whereDate('date', $today)
            ->count();
        $unfilledCount = max(0, $teacherScheduleCount - $filledTodayCount);

        // Dynamic unfilled journals list
        $unfilledSchedules = Schedule::where('teacher_id', $user->id)
            ->whereNotIn('id', ClassJournal::whereDate('date', $today)->pluck('schedule_id'))
            ->with('schoolClass')
            ->get();
        $unfilledClassesText = $unfilledSchedules->map(fn($s) => $s->schoolClass->name ?? 'Kelas')->unique()->implode(' dan ');

        $reminders = [];
        if ($unfilledCount > 0) {
            $reminders[] = $unfilledCount . ' jurnal belum diisi (' . ($unfilledClassesText ?: 'Pertemuan Terjadwal') . ')';
        } else {
            $reminders[] = 'Seluruh jurnal tatap muka hari ini telah tuntas diisi.';
        }

        // Real count of pending leaves for teacher's taught classes
        $pendingLeavesCount = LeaveRequest::where('status', 'PENDING')
            ->whereHas('student', function ($q) use ($taughtClassIds) {
                $q->whereIn('class_id', $taughtClassIds);
            })->count();

        if ($pendingLeavesCount > 0) {
            $reminders[] = $pendingLeavesCount . ' surat izin menunggu verifikasi wali kelas / pembimbing';
        }

        return response()->json([
            'stats' => [
                'kelas_diampu' => $taughtClasses . ' Kelas',
                'jam_mengajar' => '5 Jam',
                'jurnal_terisi' => $filledJournals . '/' . $totalSchedules . ' Pertemuan',
                'jurnal_belum_diisi' => $unfilled . ' Pertemuan',
            ],
            'schedules' => $scheduleList,
            'attendance_summary' => $attendanceSummary,
            'reminders' => $reminders,
            'recent_activity' => [
                [
                    'title' => 'Jurnal KBM terverifikasi di database',
                    'time' => Carbon::now()->format('H:i') . ' WIB',
                ],
            ],
        ]);
    }

    private function pembimbingDashboard(User $user, $today)
    {
        $advisedClassIds = SchoolClass::where('advisor_id', $user->id)->pluck('id')->toArray();
        if ($user->class_id && !in_array($user->class_id, $advisedClassIds)) {
            $advisedClassIds[] = $user->class_id;
        }

        $allClasses = SchoolClass::with(['secretary'])
            ->whereIn('id', $advisedClassIds)
            ->get();

        if ($allClasses->isEmpty()) {
            $allClasses = SchoolClass::with(['secretary'])->where('name', 'Kelas Industri')->get();
            if ($allClasses->isEmpty()) {
                $allClasses = SchoolClass::with(['secretary'])->take(1)->get();
            }
        }

        $monitoredClassIds = $allClasses->pluck('id')->toArray();

        $pendingLeaves = LeaveRequest::with(['student.schoolClass'])
            ->where('status', 'PENDING')
            ->whereHas('student', function ($q) use ($monitoredClassIds) {
                $q->whereIn('class_id', $monitoredClassIds);
            })
            ->latest()
            ->get();

        $todayJournalsCount = ClassJournal::whereIn('class_id', $monitoredClassIds)->whereDate('date', $today)->count();

        $monitoredClasses = $allClasses->map(function ($cls) use ($today) {
            $session = AttendanceSession::where('class_id', $cls->id)
                ->where('date', $today)
                ->first();

            $journal = ClassJournal::where('class_id', $cls->id)
                ->where('date', $today)
                ->latest()
                ->first();

            $totalStudents = User::where('class_id', $cls->id)->whereIn('role', ['SISWA', 'SEKRETARIS'])->count() ?: 36;
            $presentCount = $session ? $session->records()->where('status', 'HADIR')->count() : 0;
            $pct = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;

            return [
                'id' => $cls->id,
                'class' => $cls->name,
                'secretary' => $cls->secretary->name ?? 'Belum Ditugaskan',
                'journal_status' => $journal ? ($journal->verification_status === 'TERVERIFIKASI' ? 'Sudah Diisi' : 'Menunggu Konfirmasi Guru Pengajar') : 'Belum Diisi',
                'attendance_percent' => $session ? "{$pct}% Kehadiran" : 'Belum Ada Presensi',
            ];
        });

        $leaveList = $pendingLeaves->map(function ($lv) {
            return [
                'id' => $lv->id,
                'student_name' => $lv->student->name ?? 'Siswa',
                'class' => $lv->student->schoolClass->name ?? ($lv->student->class_id ? 'Kelas ' . $lv->student->class_id : 'Belum Ada Kelas'),
                'type' => $lv->type,
                'reason' => $lv->reason,
                'attachment' => $lv->attachment_path ? basename($lv->attachment_path) : null,
                'attachment_url' => $lv->attachment_path ? url('api/leave-requests/file/' . basename($lv->attachment_path)) : null,
            ];
        });

        return response()->json([
            'stats' => [
                'total_kelas' => $allClasses->count() . ' Kelas',
                'jurnal_terisi' => "{$todayJournalsCount} / " . $allClasses->count() . ' Kelas',
                'surat_izin_pending' => $pendingLeaves->count() . ' Permohonan',
            ],
            'monitored_classes' => $monitoredClasses,
            'pending_leaves' => $leaveList,
        ]);
    }

    private function superadminDashboard(User $user, $today)
    {
        $allClasses = SchoolClass::with(['advisor', 'secretary'])->get();
        $totalStudents = User::whereIn('role', ['SISWA', 'SEKRETARIS'])->count();
        $totalPembimbing = User::where('role', 'GURU_PEMBIMBING')->count();
        $totalPengajar = User::where('role', 'GURU_PENGAJAR')->count();
        $totalClasses = $allClasses->count();

        $classesRplCount = $allClasses->where('major', 'RPL')->count();
        $classesDkvCount = $allClasses->where('major', 'DKV')->count();
        $classesAnimCount = $allClasses->where('major', 'Animasi')->count();

        // Calculate average attendance today
        $sessionsToday = AttendanceSession::whereDate('date', $today)->with('records')->get();
        $totalPresentToday = 0;
        $totalRecordsToday = 0;
        foreach ($sessionsToday as $ses) {
            $present = $ses->records->where('status', 'HADIR')->count();
            $recCount = $ses->records->count();
            $totalPresentToday += $present;
            $totalRecordsToday += $recCount;
        }
        $avgAttendance = $totalRecordsToday > 0 ? round(($totalPresentToday / $totalRecordsToday) * 100) : 0;

        // Class journals filled today
        $journalsTodayCount = ClassJournal::whereDate('date', $today)->count();

        // Pending leaves
        $pendingLeavesCount = LeaveRequest::where('status', 'PENDING')->count();

        // Classes monitoring
        $classesMonitoring = $allClasses->map(function ($cls) use ($today) {
            $session = AttendanceSession::where('class_id', $cls->id)
                ->whereDate('date', $today)
                ->first();

            $journal = ClassJournal::where('class_id', $cls->id)
                ->whereDate('date', $today)
                ->latest()
                ->first();

            $stdCount = User::where('class_id', $cls->id)->whereIn('role', ['SISWA', 'SEKRETARIS'])->count() ?: 36;
            $presentCount = $session ? $session->records()->where('status', 'HADIR')->count() : 0;
            $pct = $stdCount > 0 ? round(($presentCount / $stdCount) * 100) : 0;

            return [
                'id' => $cls->id,
                'class_name' => $cls->name,
                'major' => $cls->major ?? 'RPL',
                'advisor_name' => $cls->advisor->name ?? 'Belum Ditentukan',
                'secretary_name' => $cls->secretary->name ?? 'Belum Ditentukan',
                'student_count' => $stdCount,
                'session_status' => $session ? $session->status : 'BELUM_DIBUKA',
                'session_date' => $today,
                'attendance_percent' => $pct,
                'present_ratio' => $session ? "{$presentCount}/{$stdCount} Siswa" : "0/{$stdCount} Siswa",
                'journal_status' => $journal ? ($journal->verification_status === 'TERVERIFIKASI' ? 'Terverifikasi' : 'Pending Guru') : 'Belum Diisi',
            ];
        });

        // Recent Audit Logs
        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'time' => Carbon::parse($log->created_at)->translatedFormat('H:i') . ' WIB',
                    'user' => $log->user ? $log->user->name : 'Sistem',
                    'role' => $log->user ? $log->user->role : 'SYSTEM',
                    'action' => $log->action,
                    'ip_address' => $log->ip_address ?? '127.0.0.1',
                ];
            });

        return response()->json([
            'stats' => [
                'total_guru_pembimbing' => $totalPembimbing . ' Walas',
                'total_guru_pengajar' => $totalPengajar . ' Guru',
                'total_siswa' => $totalStudents . ' Siswa',
                'total_kelas' => $totalClasses . ' Kelas',
                'kehadiran_rata_rata' => $avgAttendance . '%',
                'jurnal_terisi_today' => $journalsTodayCount . '/' . $totalClasses . ' Sesi',
                'surat_izin_pending' => $pendingLeavesCount . ' Menunggu',
                'majors' => [
                    'rpl' => $classesRplCount,
                    'dkv' => $classesDkvCount,
                    'animasi' => $classesAnimCount,
                ],
            ],
            'classes_monitoring' => $classesMonitoring,
            'recent_audit_logs' => $recentAuditLogs,
        ]);
    }
}
