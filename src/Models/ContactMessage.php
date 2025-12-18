<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = [
        'NAME',
        'email',
        'SUBJECT',
        'message'
    ];

    public $timestamps = false; // omdat je alleen created_at hebt
}
