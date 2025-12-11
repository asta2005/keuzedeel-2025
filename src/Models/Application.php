<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model {
    protected $fillable = ['name', 'email', 'phone', 'motivation', 'cv_file'];
}
