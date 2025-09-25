<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscortModel extends Model
{
    use HasFactory;
    
    protected $table = 'escorts';
    
    protected $fillable = [
        'kategori_pengantar',
        'nama_pengantar',
        'jenis_kelamin',
        'nomor_hp',
        'plat_nomor',
        'nama_pasien',
        'foto_pengantar',
        'submission_id',
        'submitted_from_ip',
        'api_submission',
        'status'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    // Helper methods for status checking
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    public function isVerified()
    {
        return $this->status === 'verified';
    }
    
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    // Get status badge class for styling
    public function getStatusBadgeClass()
    {
        $status = $this->status ?? 'pending'; // Default to pending if null
        return match($status) {
            'pending' => 'badge-warning',
            'verified' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary'
        };
    }
    
    // Get status display name
    public function getStatusDisplayName()
    {
        $status = $this->status ?? 'pending'; // Default to pending if null
        return match($status) {
            'pending' => 'Menunggu',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => 'Unknown'
        };
    }
    
    // Check if escort has image
    public function hasImage()
    {
        return !empty($this->foto_pengantar);
    }
    
    // Get image storage URL
    public function getImageUrl()
    {
        if (!$this->hasImage()) {
            return null;
        }
        
        return \Illuminate\Support\Facades\Storage::url($this->foto_pengantar);
    }
    
    // Get image storage path
    public function getImagePath()
    {
        if (!$this->hasImage()) {
            return null;
        }
        
        return storage_path('app/public/' . $this->foto_pengantar);
    }
    
    // Check if image file exists in storage
    public function imageExists()
    {
        if (!$this->hasImage()) {
            return false;
        }
        
        return \Illuminate\Support\Facades\Storage::exists('public/' . $this->foto_pengantar);
    }
}
