<?php

namespace App\Repositories\Interfaces\Api\Nurseries\Profile;

use App\Repositories\Interfaces\IBaseRepository;

interface IBabySitterSkillsRepository extends IBaseRepository
{
    public function babySitterSkills($baby_sitter_id);
}
