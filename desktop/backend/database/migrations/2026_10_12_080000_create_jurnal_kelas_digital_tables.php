<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Classes
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. XI RPL 1, XI RPL 2
            $table->string('major', 50)->default('RPL'); // RPL, DKV, Animasi
            $table->unsignedBigInteger('advisor_id')->nullable(); // Guru Pembimbing
            $table->unsignedBigInteger('secretary_id')->nullable(); // Siswa Sekretaris
            $table->timestamps();
        });

        // 2. Modify / extend users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email'); // e.g. 6285...
            $table->string('nip_nis', 30)->nullable()->after('phone'); // NIP atau NISN
            $table->enum('role', ['SISWA', 'SEKRETARIS', 'GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])->default('SISWA')->after('nip_nis');
            $table->string('specialization', 100)->nullable()->after('role'); // e.g. Pemrograman Web, Animasi 3D, Desain Grafis
            $table->boolean('is_active')->default(true)->after('specialization');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete()->after('is_active');
        });

        // 3. Teacher - Classes (Many-to-Many)
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'class_id']);
        });

        // 4. Subjects (Mata Pelajaran)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Pemrograman Web, Basis Data, dll.
            $table->string('code')->nullable();
            $table->string('major', 50)->nullable(); // RPL, DKV, Animasi, UMUM
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Schedules (Jadwal Pelajaran)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('day', 20); // Senin, Selasa, Rabu, Kamis, Jumat
            $table->string('start_time', 10); // 07:30
            $table->string('end_time', 10); // 09:00
            $table->string('room', 50)->default('Lab. 1'); // Lab. 1
            $table->timestamps();
        });

        // 6. Terms (Semester & Tahun Ajaran)
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 2025/2026 - Ganjil
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('effective_days')->default(108);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Attendance Sessions (Sesi Presensi Harian)
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['DRAFT', 'LOCKED'])->default('DRAFT');
            $table->foreignId('locked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->enum('verification_status', ['BELUM_KONFIRMASI', 'PENDING', 'TERVERIFIKASI'])->default('BELUM_KONFIRMASI');
            $table->string('school_hours')->default('07:30 - 15:30');
            $table->string('room')->nullable();
            $table->timestamps();
            $table->unique(['class_id', 'date']);
        });

        // 8. Attendance Records (Detail Kehadiran per Siswa)
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['HADIR', 'SAKIT', 'IZIN', 'ALPHA'])->default('HADIR');
            $table->string('note')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'student_id']);
        });

        // 9. Class Journals (Jurnal Kelas oleh Sekretaris)
        Schema::create('class_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->date('date');
            $table->text('activity'); // Kegiatan belajar / materi yang diajarkan
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->enum('verification_status', ['BELUM_KONFIRMASI', 'PENDING', 'TERVERIFIKASI'])->default('PENDING');
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete(); // Sekretaris
            $table->foreignId('confirmed_by_id')->nullable()->constrained('users')->nullOnDelete(); // Guru Pengajar
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // 10. Material Journals (Jurnal Materi & Rencana Belajar oleh Guru Pengajar)
        Schema::create('material_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->date('date');
            $table->string('topic'); // Pokok Bahasan / KD
            $table->text('plan')->nullable(); // Rencana Belajar
            $table->text('assignment')->nullable(); // Tugas / Catatan
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        // 11. Leave Requests (Permohonan Surat Izin Belajar)
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('submitted_by_id')->constrained('users')->cascadeOnDelete(); // Sekretaris
            $table->enum('type', ['SAKIT', 'IZIN', 'DISPENSASI'])->default('SAKIT');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('recipient')->default('Guru Pembimbing Sekolah / Wali Kelas');
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->longText('signature')->nullable(); // Base64 tanda tangan canvas
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 12. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30); // JURNAL, SURAT_IZIN, PRESENSI, USER
            $table->string('title');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 13. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();
        });

        // 14. OTP Tokens
        Schema::create('otp_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_tokens');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('material_journals');
        Schema::dropIfExists('class_journals');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('teacher_classes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn(['phone', 'nip_nis', 'role', 'is_active', 'class_id']);
        });
        Schema::dropIfExists('classes');
    }
};
