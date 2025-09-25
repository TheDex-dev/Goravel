-- Create escorts table for Pendataan IGD migration
CREATE TABLE IF NOT EXISTS escorts (
    id BIGSERIAL PRIMARY KEY,
    status VARCHAR(20) CHECK (status IN ('pending', 'verified', 'rejected')) DEFAULT 'pending',
    kategori_pengantar VARCHAR(20) CHECK (kategori_pengantar IN ('Polisi', 'Ambulans', 'Perorangan')),
    nama_pengantar VARCHAR(255) NOT NULL,
    jenis_kelamin VARCHAR(20) CHECK (jenis_kelamin IN ('Laki-laki', 'Perempuan')),
    nomor_hp VARCHAR(20) NOT NULL,
    plat_nomor VARCHAR(20) NOT NULL,
    nama_pasien VARCHAR(255) NOT NULL,
    foto_pengantar VARCHAR(255) NULL,
    submission_id VARCHAR(255) NULL,
    submitted_from_ip VARCHAR(255) NULL,
    api_submission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_escorts_status ON escorts(status);
CREATE INDEX IF NOT EXISTS idx_escorts_kategori ON escorts(kategori_pengantar);
CREATE INDEX IF NOT EXISTS idx_escorts_created_at ON escorts(created_at);
CREATE INDEX IF NOT EXISTS idx_escorts_submission_id ON escorts(submission_id);

-- Insert some sample data for testing
INSERT INTO escorts (
    status, kategori_pengantar, nama_pengantar, jenis_kelamin, 
    nomor_hp, plat_nomor, nama_pasien, api_submission
) VALUES 
    ('pending', 'Polisi', 'Budi Santoso', 'Laki-laki', '081234567890', 'B1234ABC', 'Siti Aminah', true),
    ('verified', 'Ambulans', 'Dr. Sarah', 'Perempuan', '081987654321', 'B5678DEF', 'Ahmad Rahman', false),
    ('pending', 'Perorangan', 'Andi Wijaya', 'Laki-laki', '082345678901', 'B9012GHI', 'Maria Sari', true),
    ('rejected', 'Polisi', 'Joko Susilo', 'Laki-laki', '083456789012', 'B3456JKL', 'Dewi Lestari', false)
ON CONFLICT DO NOTHING;