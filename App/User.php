<?php
namespace App;

use Fontibus\Model\Model;

class User extends Model {

    protected string $table = 'users';

    protected array $fillable = [
        'first_name', 'sur_name', 'email'
    ];

    public function fullname(): string {
        return $this->first_name.' '.$this->sur_name;
    }

}