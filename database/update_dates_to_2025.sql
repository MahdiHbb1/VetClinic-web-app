-- Update all dates from 2024 to 2025 for demo purposes
-- This will make the data current and display properly on dashboard

USE vetclinic;

-- Update appointment dates: shift all dates by 1 year (365 days)
UPDATE appointment 
SET tanggal_appointment = DATE_ADD(tanggal_appointment, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_appointment) = 2024;

-- Update medical record dates: shift by 1 year
UPDATE medical_record 
SET tanggal_kunjungan = DATE_ADD(tanggal_kunjungan, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_kunjungan) = 2024;

-- Update pet registration dates: shift by 1 year
UPDATE pet 
SET tanggal_registrasi = DATE_ADD(tanggal_registrasi, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_registrasi) = 2024;

-- Update veterinarian joined dates: shift by 1 year
UPDATE veterinarian 
SET tanggal_bergabung = DATE_ADD(tanggal_bergabung, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_bergabung) < 2025;

-- Update pet birth dates for younger pets (born in 2024)
UPDATE pet 
SET tanggal_lahir = DATE_ADD(tanggal_lahir, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_lahir) = 2024;

-- Update vaksinasi (vaccination) dates
UPDATE vaksinasi 
SET tanggal_vaksin = DATE_ADD(tanggal_vaksin, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_vaksin) = 2024;

-- Update resep (prescription) dates if any
UPDATE resep 
SET tanggal_resep = DATE_ADD(tanggal_resep, INTERVAL 1 YEAR)
WHERE YEAR(tanggal_resep) = 2024;

-- Update medicine expiry dates that are in 2024 or 2025 to be future dates
UPDATE medicine 
SET expired_date = DATE_ADD(expired_date, INTERVAL 1 YEAR)
WHERE expired_date < '2026-01-01';

-- Show summary of updated records
SELECT 'Appointments' as TableName, 
       COUNT(*) as TotalRecords,
       MIN(tanggal_appointment) as EarliestDate,
       MAX(tanggal_appointment) as LatestDate
FROM appointment
WHERE YEAR(tanggal_appointment) = 2025

UNION ALL

SELECT 'Medical Records' as TableName,
       COUNT(*) as TotalRecords,
       MIN(tanggal_kunjungan) as EarliestDate,
       MAX(tanggal_kunjungan) as LatestDate
FROM medical_record
WHERE YEAR(tanggal_kunjungan) = 2025

UNION ALL

SELECT 'Pets' as TableName,
       COUNT(*) as TotalRecords,
       MIN(tanggal_registrasi) as EarliestDate,
       MAX(tanggal_registrasi) as LatestDate
FROM pet
WHERE YEAR(tanggal_registrasi) = 2025

UNION ALL

SELECT 'Vaccinations' as TableName,
       COUNT(*) as TotalRecords,
       MIN(tanggal_vaksin) as EarliestDate,
       MAX(tanggal_vaksin) as LatestDate
FROM vaksinasi
WHERE YEAR(tanggal_vaksin) = 2025;

-- Show today's appointments (should have some now)
SELECT COUNT(*) as AppointmentsToday
FROM appointment
WHERE tanggal_appointment = CURRENT_DATE;
