@echo off
setlocal enabledelayedexpansion

:: 1. PINDAH SECARA MUTLAK KE DRIVE C
c:

:: 2. ATUR JALUR DIREKTORI PROYEK (Sesuaikan folder Laragon-mu)
set "backupDir=C:\laragon\www\BOOKINGVILLA_UAP_PDT\backup"
set "mysqlDir=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin"

:: Masuk langsung ke dalam folder bin MySQL agar perintah dikenali mutlak
cd /d "%mysqlDir%"

:: Ambil format tanggal & waktu universal (Anti-Gagal Windows)
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set "dt=%%I"
set "year=!dt:~0,4!"
set "month=!dt:~4,2!"
set "day=!dt:~6,2!"
set "hour=!dt:~8,2!"
set "minute=!dt:~10,2!"
set "timestamp=!year!-!month!-!day!_!hour!-!minute!"

:: 3. EKSEKUSI BACKUP DENGAN USER DAN PASSWORD YANG BENAR (Tanpa Spasi setelah -p)
mysqldump.exe -u adm_backup_booking_villa -payamgoreng.4 uap_villa > "%backupDir%\backup_otomatis_%timestamp%.sql"

endlocal