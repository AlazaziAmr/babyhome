<?php

namespace App\Repositories\Classes\Api\Nurseries;

use App\Http\Resources\Api\Master\Booking\BookingNurseryResource;
use App\Http\Resources\Api\Nurseries\BookingChildResource;
use App\Models\Api\Master\Booking\RejectResReasons;
use App\Models\Api\Master\BookingServices\Booking;
use App\Models\Api\Master\BookingServices\BookingLog;
use App\Models\Api\Master\BookingServices\BookingService;
use App\Models\Api\Master\BookingServices\ConfirmedBooking;
use App\Models\Api\Master\BookingServices\ReservedTime;
use App\Models\Api\Master\Child;
use App\Models\Api\Master\Master;
use App\Models\Api\Nurseries\JoinRequest\JoinRequest;
use App\Models\Api\Nurseries\Nursery;
use App\Models\User;
use App\Repositories\Classes\BaseRepository;
use App\Repositories\Interfaces\Api\Nurseries\IBookingNurseryRepository;
use App\Traits\ApiTraits;
use Carbon\Carbon;
use Illuminate\Http\Request;


class BookingNurseryRepository extends BaseRepository implements IBookingNurseryRepository
{
    use ApiTraits;

    public function model()
    {
        return JoinRequest::class;
    }

    public function Nursery()
    {
        return Nursery::class;
    }

    public function Booking()
    {
        $user_id = user()->id;
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');
        $nurseryBooking=Booking::whereIn("nursery_id",$nursery_id)->where('status_id', 1)->with([
            'masters:id,uid,first_name',
            'children:id,name,date_of_birth',
            'BookingStatus:id,name',
        ])->get();

        return $nurseryBooking;

    }
    public function showBooking()
    {
        $user_id = user()->id;
        $date=now()->format('Y:m:d');
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');

        $nurseryBooking=Booking::whereIn("nursery_id",$nursery_id)
            ->where('booking_date',$date)
            ->where('status_id', 1)->with([
            'masters:id,uid,first_name',
            'children:id,name,date_of_birth',
            'children.attachmentable',
            'BookingStatus:id,name',
            'nurseries',

        ])->get();
        if ($nurseryBooking->isEmpty()) {
            return null;
        }else{
            return $nurseryBooking;
        }
    }


    public function onlineStatus(Request $request){

        if (Nursery::where('id', $request['nursery_id'])->exists()) {
            $request = Nursery::where('id', $request['nursery_id'])->update([
                'online' => $request['online_status'],
            ]);
            return $request;
        }else{
            return null;
        }

    }
    public function rejectBooking()
    {
        $user_id = user()->id;
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');
        $nurseryBooking=Booking::whereIn("nursery_id",$nursery_id)->where('status_id', 3)->with([
            'masters:id,uid,first_name',
            'children:id,name,date_of_birth',
            'children.attachmentable',
            'BookingStatus:id,name',
            'nurseries',
            'RejectResReasons',
            'serviceBooking.service',
        ])->get();



        if ($nurseryBooking->isEmpty()) {
            return null;
        }else{
            return $nurseryBooking;
        }


    }



    public function showBookingDetails($id)
    {


        $nurseryBooking=Booking::where('id',$id)->with([
            'masters:id,first_name,uid',
            'serviceBooking.service',
            'BookingStatus:id,name',
            'children.sicknesses',
            'children.languages:name',
            'children.allergies',
            'children.attachmentable',
        ])->get();


        if ($nurseryBooking->isEmpty()) {
            return null;
        }else{
            return $nurseryBooking;
        }
    }


    public function bookingLog($request,$status,$user_type)
    {
        $babySitter = BookingLog::create([
            'user_id' => $request->nursery_id,
            'user_type' => $user_type,
            'booking_id' => $request->booking_id,
            'status_id' => $status,

        ]);
    }

    public function rejected(Request $request)
    {

        /*booking_id
         * reason
         * nursery_id
         * */


        $RejectResReasons = RejectResReasons::create([
            'booking_id' => $request->booking_id,
            'reason' => $request->reason,
        ]);
        $status="3";
        $user_type=2;
        $this->bookingLog($request,$status,$user_type);

        Booking::where('id', $request->booking_id)->update([
            'status_id' => $status,
        ]);

        BookingService::where('booking_id', $request->booking_id)->where('child_id',$request->child_id)
            ->update([
                'status' => $status,
            ]);
        ReservedTime::where('booking_id', $request->booking_id)
            ->update(
            ['num_of_unconfirmed_res' => '1']);



    }
    public function confirmed(Request $request)
    {

        /*booking_id
         * payment_method_id
         * nursery_id
         * confirm_date
         * total_payment
         * price_per_hour
         * total_services_price
         * created_by
         * status
         * child_id
         * */

        /*  $master_id=Booking::where('id',$request->booking_id)->first();
          $master=Master::where('id',$master_id->master_id)->first();
          $fcm = new \App\Functions\FcmNotification();
          $phone = str_replace("+9660","966",$master->phone);
          $phone = str_replace("+966","966",$phone);
          $fcm->send_notification("حالة الحجز",' تم قبول الحجز.',$phone);*/


        $StartTime=$request->start_time;
        $EndTime=$request->end_time;

        $Data = $this->SplitTime($StartTime, $EndTime, $Duration="60",$request);

        if ($Data['status'] == false) {
            $msg='عذراً لايمكنك إستقبال طلبات في لأوقات التالبة !';
            return $this->splitTimeReturn($Data['Data'],$msg);
        }else{

            $price_per_hour=Nursery::select('price')->where('id',$request->nursery_id)->first();
            $total_services_price=BookingService::where('booking_id',$request->booking_id)
                ->where('child_id',$request->child_id) ->where('nursery_id',$request->nursery_id)->sum('service_price');
            $total_payment=(($price_per_hour->price)*$request->total_hours)+$total_services_price;
            $status="2";
            $confirm_date =now()->format('Y-m-d');
            $RejectResReasons = ConfirmedBooking::create([
                'nursery_id' => $request->nursery_id,
                'booking_id' => $request->booking_id,
                'payment_method_id' => $request->payment_method_id,
                'confirm_date' => $confirm_date,
                'total_payment' => $total_payment,
                'price_per_hour' => $price_per_hour->price,
                'total_services_price' => $total_services_price,
                'created_by' => $request->created_by,
                'status' => "2",
            ]);
            BookingService::where('booking_id', $request->booking_id)->where('child_id',$request->child_id)->update([
                'status' => $status,
            ]);

            $user_type=2;
            $this->bookingLog($request,$status,$user_type);
            $request = Booking::where('id', $request->booking_id)->update([
                'status_id' => $status,
            ]);

            return true;

        }
    }

    function SplitTime($StartTime, $EndTime, $Duration="60",$request){
        $ReturnArray = array ();
        $ReturnArrayTable = array ();// Define output
        $StartTime    = strtotime ($StartTime); //Get Timestamp
        $EndTime      = strtotime ($EndTime); //Get Timestamp

        $AddMins  = $Duration * 60;

        while ($StartTime <= $EndTime) //Run loop
        {
            $ReturnArray[] = date ("G:i", $StartTime);
            $StartTime += $AddMins; //Endtime check
        }
        $ReturnTable=ReservedTime::where('nursery_id',$request->nursery_id)
            ->where('date',$request->booking_date)
            ->whereIn('start_hour',$ReturnArray)->where('num_of_confirmed_res',3)->get();
        if ($ReturnTable->count()) {
            return ['status'=>false,
                'Data'=>$ReturnTable];

        }else{
            foreach ($ReturnArray as $array){
                $time= Carbon::parse($array);
                $start_time= $time->format('H:i');
                $end_time= $time->addMinutes(60)->format('H:i');




                $ReturnArrayTable=ReservedTime::where('nursery_id',$request->nursery_id)
                    ->where('date',$request->booking_date)
                    ->where('start_hour',$array)->first();
                if ($ReturnArrayTable !=null){

                    $ReturnArrayTable->update([
                        'num_of_unconfirmed_res'=>$ReturnArrayTable->num_of_unconfirmed_res-1,
                        'num_of_confirmed_res'=>$ReturnArrayTable->num_of_confirmed_res+1,
                    ]);




                }else{
                    $booking_date = Carbon::now()->format('Y:m:d');

                    $babySitter = ReservedTime::create([

                        'nursery_id' => $request->nursery_id,
                        'date' => $request->booking_date,
                        'start_hour' => $start_time,
                        'end_hour' => $end_time,
                        'booking_id' => 1,
                        'num_of_confirmed_res' => "0",
                        'num_of_unconfirmed_res' => 1,
                    ]);

                }
            }


        }
        return ['status'=>true];
    }

    public function confirmedShow()
    {
        $user_id = user()->id;
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');


        $dateToday=now()->format('Y:m:d');
        $TimeNow=now()->format('Y:m:d');

        $booking=Booking::where('status_id',2)->whereIn('nursery_id',$nursery_id)
            ->where('booking_date', $dateToday)
            ->pluck('id');
        $ConfirmedBooking=ConfirmedBooking::whereIn('booking_id',$booking)
            ->where('booking_date', $dateToday)
            ->whereIn('nursery_id',$nursery_id)->with([
            "Booking.children",
            "PaymentMethod",
            "bookingServices.services",
            "Booking.masters",
            'Booking.children.sicknesses',
            'Booking.children.languages:name',
            'Booking.children.allergies',
            'Booking.children.attachmentable',
        ])->get();
        if ($ConfirmedBooking==null) {
            return null;
        }else{
            return $ConfirmedBooking;
        }

    }


    public function showChildrenBooking()
    {
        $user_id = user()->id;
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');

        $dateToday=now()->format('Y:m:d');
        $TimeNow=now()->format('Y:m:d');
        $booking=Booking::where('status_id',2)->whereIn('nursery_id',$nursery_id)
            ->where('booking_date', $TimeNow)
            ->pluck('id');
        $ConfirmedBooking=ConfirmedBooking::whereIn('booking_id',$booking)->whereIn('nursery_id',$nursery_id)
            ->where('confirm_date',$dateToday)->with([
            "Booking.children","Booking.children.attachmentable"
        ])->get();
        if ($ConfirmedBooking==null) {
            return null;
        }else{
            return BookingChildResource::collection($ConfirmedBooking);
        }
    /*    $user_id = auth('master')->user()->id;
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');


        $dateToday=now()->format('Y:m:d');
        $TimeNow=now()->format('Y:m:d');

        $booking=Booking::where('status_id',2)->whereIn('nursery_id',$nursery_id)
            ->where('booking_date', $TimeNow)
            ->pluck('child_id');
        return $booking;
        $data['child'] = Child::with(['languages', 'attachmentable','bookingService.service'])
            ->where('id', $booking)
            ->get();
        if ($data->isEmpty()) {
            return null;
        }else{
            return $data;
        }*/

    }
    public function showAllChildrenBooking()
    {

        $user_id = user()->id;
        $nursery_id=Nursery::where('user_id',$user_id)->pluck('id');
        $booking=Booking::whereIn('nursery_id',$nursery_id)->with([
            "children","children.attachmentable","masters","BookingStatus"
        ])->get();
        if ($booking==null) {
            return null;
        }else{
            return BookingNurseryResource::collection($booking);
        }

    }


}
