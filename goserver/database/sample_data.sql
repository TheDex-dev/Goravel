-- Insert sample data for testing the Pendataan IGD API
INSERT INTO escorts (
    status, kategori_pengantar, nama_pengantar, jenis_kelamin, 
    nomor_hp, plat_nomor, nama_pasien, api_submission,
    submission_id, submitted_from_ip
) VALUES 
    ('pending', 'Polisi', 'Budi Santoso', 'Laki-laki', '081234567890', 'B1234ABC', 'Siti Aminah', true, 'ESC_1640995200_B1234ABC', '192.168.1.100'),
    ('verified', 'Ambulans', 'Dr. Sarah Wijaya', 'Perempuan', '081987654321', 'B5678DEF', 'Ahmad Rahman', false, 'ESC_1640995260_B5678DEF', '192.168.1.101'),
    ('pending', 'Perorangan', 'Andi Wijaya', 'Laki-laki', '082345678901', 'B9012GHI', 'Maria Sari', true, 'ESC_1640995320_B9012GHI', '192.168.1.102'),
    ('rejected', 'Polisi', 'Joko Susilo', 'Laki-laki', '083456789012', 'B3456JKL', 'Dewi Lestari', false, 'ESC_1640995380_B3456JKL', '192.168.1.103'),
    ('verified', 'Ambulans', 'Suster Rina', 'Perempuan', '084567890123', 'B7890MNO', 'Bambang Sutrisno', true, 'ESC_1640995440_B7890MNO', '192.168.1.104'),
    ('pending', 'Perorangan', 'Ibu Sari', 'Perempuan', '085678901234', 'B1357PQR', 'Anak Sari (5 tahun)', false, 'ESC_1640995500_B1357PQR', '192.168.1.105'),
    ('verified', 'Polisi', 'Pak Hendro', 'Laki-laki', '086789012345', 'B2468STU', 'Pak Hendro (kecelakaan)', true, 'ESC_1640995560_B2468STU', '192.168.1.106'),
    ('pending', 'Ambulans', 'Dr. Indah', 'Perempuan', '087890123456', 'B3691VWX', 'Ibu Hamil Emergency', false, 'ESC_1640995620_B3691VWX', '192.168.1.107')
ON CONFLICT DO NOTHING;