<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ==========================================
// PEMBAHASAN / BAGIAN: Model Entitas PENGGUNA
// ==========================================
class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'PENGGUNA';

    protected $primaryKey = 'IDPENGGUNA';

    const CREATED_AT = 'TANGGALBUAT';

    const UPDATED_AT = 'TANGGALUBAH';

    protected $fillable = [
        'USERNAME',
        'PASSWORD',
        'NAMALENGKAP',
        'EMAIL',
        'NOMORTELEPON',
        'TANGGALBINDING',
        'ADALAHBAAK',
        'STATUSAKUN',
        'KUNCIPUBLIK',
        'KUNCIPRIVAT',
    ];

    protected $hidden = [
        'PASSWORD',
        'KUNCIPRIVAT',
    ];

    protected function casts(): array
    {
        return [
            'PASSWORD' => 'hashed',
            'ADALAHBAAK' => 'boolean',
            'TANGGALBINDING' => 'datetime',
            'TANGGALBUAT' => 'datetime',
            'TANGGALUBAH' => 'datetime',
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Override Autentikasi Sandi
    // ==========================================
    public function getAuthPassword(): string
    {
        return $this->PASSWORD;
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Override Token Ingat Sandi
    // ==========================================
    public function getRememberTokenName(): string
    {
        return '';
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Entitas DOSEN
    // ==========================================
    public function dosen(): HasOne
    {
        return $this->hasOne(Dosen::class, 'IDPENGGUNA', 'IDPENGGUNA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Entitas MAHASISWA
    // ==========================================
    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'IDPENGGUNA', 'IDPENGGUNA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Entitas ADMINISTRASI
    // ==========================================
    public function administrasi(): HasMany
    {
        return $this->hasMany(Administrasi::class, 'IDPEMOHON', 'IDPENGGUNA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Relasi ke Entitas PERSETUJUANSURAT
    // ==========================================
    public function persetujuanSurat(): HasMany
    {
        return $this->hasMany(PersetujuanSurat::class, 'IDPENYETUJU', 'IDPENGGUNA');
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Penurunan Peran dari Relasi
    // ==========================================
    public function daftarPeran(): array
    {
        $peran = [];

        // Deteksi peran BAAK
        if ($this->ADALAHBAAK) {
            $peran[] = 'BAAK';
        }

        // Deteksi peran Dosen dan Pimpinan
        if ($this->dosen) {
            $peran[] = 'DOSEN';
            if (! empty(trim($this->dosen->JABATAN ?? ''))) {
                $peran[] = 'PIMPINAN';
            }
        }

        // Deteksi peran Mahasiswa dan Ormawa
        if ($this->mahasiswa) {
            $peran[] = 'MAHASISWA';
            $adalahKetuaOrmawa = Organisasi::where('IDKETUA', $this->mahasiswa->IDMAHASISWA)
                ->where('STATUSORGANISASI', 'AKTIF')
                ->exists();

            if ($adalahKetuaOrmawa) {
                $peran[] = 'ORMAWA';
            }
        }

        return array_values(array_unique($peran));
    }
}
