<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $table = 'vacancies';

    // BELANGRIJK: voorkomt "updated_at not found" fouten
    public $timestamps = false;

    protected $fillable = [
        'title',
        'location',
        'type',
        'description'
    ];
}
