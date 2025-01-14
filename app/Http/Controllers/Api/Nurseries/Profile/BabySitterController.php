<?php

namespace App\Http\Controllers\Api\Nurseries\Profile;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Nurseries\BabysitterInfoRequest;
use App\Http\Requests\Api\Nurseries\NurseryRequest;
use App\Http\Resources\Api\Nurseries\BabysitterInfoResource;
use App\Http\Resources\Api\Nurseries\NurseryResource;
use App\Models\Api\Nurseries\BabysitterInfo;
use App\Models\Api\Nurseries\Nursery;
use App\Repositories\Interfaces\Api\Nurseries\INurseryRepository;
use App\Repositories\Interfaces\Api\Nurseries\Profile\IBabySitterRepository;

class BabySitterController extends Controller
{
   private $babySitterRepository;
   private $nurseryRepository;

    public function __construct(IBabySitterRepository $babySitterRepository, INurseryRepository $nurseryRepository)
    {
        $this->babySitterRepository = $babySitterRepository;
        $this->nurseryRepository = $nurseryRepository;
    }

    public function index()
    {
        try {
            $nursery =Nursery::where('user_id',auth('api')->user()->id)->first();
            if($nursery){
                $nursery_id = $nursery->id;
                $nurseries = new BabysitterInfoResource($this->nurseryRepository->BabySitter($nursery_id));
                return JsonResponse::successfulResponse('msg_success', $nurseries);
            }else{
                return JsonResponse::errorResponse('');
            }
           } catch (\Exception $e) {
            return JsonResponse::errorResponse($e->getMessage());
        }
    }

    public function update(BabysitterInfoRequest $request)
    {
        try {
            $nursery =Nursery::where('user_id',auth('api')->user()->id)->first();
            if($nursery){
                $nursery_id = $nursery->id;
                $babySitter = BabysitterInfo::find($nursery_id);
                if($babySitter){
                    $this->babySitterRepository->update($request->validated(), $babySitter['id']);
                    if (!empty($request['languages'])) {
                        $babySitter->languages()->detach();
                        $babySitter->languages()->sync($request['languages']);
                    }
                }
//                else{
//                    $request_data = $request->validated();
//                    $request_data['nursery_id'] = $nursery_id;
//                    $this->babySitterRepository->create($request_data);
//                }
                return JsonResponse::successfulResponse('msg_updated_succssfully');
            }else{
                return JsonResponse::errorResponse('msg_not_found');
            }
             } catch (\Exception $e) {
            return JsonResponse::errorResponse($e->getMessage());
        }
    }

}
