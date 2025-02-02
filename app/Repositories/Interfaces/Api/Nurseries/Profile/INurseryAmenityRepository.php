<?php

namespace App\Repositories\Interfaces\Api\Nurseries\Profile;

use App\Repositories\Interfaces\IBaseRepository;

interface INurseryAmenityRepository extends IBaseRepository
{
    public function fetchAllForCurrentUser($with = [], $columns = array('*'));
}
