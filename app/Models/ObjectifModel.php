<?php

namespace App\Models;

use CodeIgniter\Model;

class ObjectifModel extends Model
{
    protected $table = 'objectif';
    protected $primaryKey = 'id_objectif';
    protected $returnType = 'array';
    protected $allowedFields = ['label_objectif'];

}
