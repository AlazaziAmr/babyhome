<?php

namespace App\Repositories\Interfaces\Api\Nurseries;

use App\Repositories\Interfaces\IBaseRepository;
use Illuminate\Http\Request;

interface IBookingNurseryRepository extends IBaseRepository
{
    public function showBooking();
    public function rejected(Request $request);
    public function confirmed(Request $request);
}