<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Quotation;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];
    
    protected $appends = [
        'created_ago'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getCreatedAgoAttribute()
{
    return $this->created_at->diffForHumans();
}

    public function requests()
    {
        return $this->hasMany(Request::class, 'created_by');
    }

    public function collaboratingRequests()
    {
        return $this->belongsToMany(Request::class, 'request_collaborators')
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function processedQuotations()
    {
        return $this->hasMany(Quotation::class, 'processed_by');
    }

    public function processedPurchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'processed_by');
    }

    public function processedPurchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'processed_by');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(RequestFile::class, 'uploaded_by');
    }
}
