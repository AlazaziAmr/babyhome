<?php

use App\Helpers\JsonResponse;
use App\Models\Api\Admin\Admin;
use App\Models\Api\Master\Master;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;



if (!function_exists('user')) {

    /**
     * get authenticated user
     *
     * @return User|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    function user()
    {
        return auth('api')->check() ? auth('api')->user() : new User();
    }
}

if (!function_exists('admin')) {

    /**
     * get authenticated admin
     *
     * @return Admin|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    function admin()
    {
        return auth('admin')->check() ? auth('admin')->user() : new Admin();
    }
}
if (!function_exists('master')) {

    /**
     * get authenticated master
     *
     * @return Admin|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    function master()
    {
        return auth('master')->check() ? auth('master')->user() : new Master();
    }
}

if (!function_exists('clean_description')) {

    function clean_description($text)
    {
        return str_replace(["\r\n", '&nbsp;'], ' ', filter_var($text, FILTER_SANITIZE_STRING));
    }
}

if (!function_exists('OTPGenrator')) {

    /**
     * generate 5 digit for the sms code
     * @return int
     */
    function OTPGenrator()
    {
//        return '1234';
         return rand(1000, 9999);
    }
}

if (!function_exists('sendOTP')) {


    /**
     * send  OTP as Sms
     *
     * @param integer $OTP
     * @param string  $phone
     * @param string  $message
     */
    function sendOTP($OTP, $phone, $message = 'رمز التحقق للدخول هو  %s , أهلا بك عميلنا العزيز')
    {
        //!function_exists('send_verification_code')
         try {
             $phone = str_replace('+9660','966',$phone);
             $phone = str_replace('+966','966',$phone);
                $message = "رمز التحقق: $OTP";
                $response = Http::post('https://www.msegat.com/gw/sendsms.php', [
                    "userName"    => "babyhome",
                    "apiKey"      => "0eacc90c694d720222a39c3b74241915",
                    "numbers"     => $phone,
                    "userSender"  => "babyhome",
                    "msg"         => $message,
                    "msgEncoding" => "UTF8",
                ]);
                return $response;
//                dd($response->body());
            } catch (\Exception $e) {
                return JsonResponse::errorResponse($e->getMessage());
            }

        return  true;

    }
}


if (!function_exists('sendAdMessage')) {


    /**
     * send  Advertisement Sms
     *
     * @param string  $phone
     * @param string  $message
     */
    function sendAdMessage($phone, $message)
    {

         try {
             $phone = str_replace('+9660','966',$phone);
             $phone = str_replace('+966','966',$phone);
                $response = Http::post('https://www.msegat.com/gw/sendsms.php', [
                    "userName"    => "babyhome",
                    "apiKey"      => "0eacc90c694d720222a39c3b74241915",
                    "numbers"     => $phone,
                    "userSender"  => "BabyHome-ad",
                    "msg"         => $message,
                    "msgEncoding" => "UTF8",
                ]);
                return $response;

            } catch (\Exception $e) {
                return JsonResponse::errorResponse($e->getMessage());
            }

        return true;

    }
}

// if (!function_exists('executeBase64')) {

//     /**
//      * save base64
//      *
//      */
//     function executeBase64($model, $files, $destination = null)
//     {
//         foreach ($files as $file) {
//             if (preg_match('/^data:image\/(\w+);base64,/', $file)) {
//                 $extension = explode('/', explode(':', substr($file, 0, strpos($file, ';')))[1])[1];
//                 $replace = substr($file, 0, strpos($file, ',') + 1);
//                 $image = str_replace($replace, '', $file);
//                 $image = str_replace(' ', '+', $image);
//                 $imageName = Str::random(10) . '.' . $extension;
//                 Storage::disk('public')->put($imageName, base64_decode($image));
//                 $model->images()->create(['image' => $imageName, 'type' => $extension]);
//             }
//         }
//     }
// }

// if (!function_exists('uploadAttachment')) {
//     function uploadAttachment($model, $request, $fileName = 'attachments', $folderName = 'attachments')
//     {

//         if ($request->hasFile($fileName)) {
//             $attachments = $request->file($fileName);
//             foreach ($attachments as $attachment) {
//                 $extension = explode('/', mime_content_type($attachment['file']))[1];
//                $attachment['file'] = str_replace(' ', '+', $attachment['file']);
//                 $attachmentName = Str::random(10) . '.' . $extension;
//                 Storage::disk('public')->put($attachmentName, base64_decode($attachment['file']));
//                 $model->attachmentable()->create([
//                     'title' => $attachment['title'],
//                     'description' => $attachment['description'],
//                     'path' => $attachmentName,
//                 ]);
//             }
//         }
//     }
// }
if (!function_exists('uploadAttachment')) {
    function uploadAttachment($model, $request, $fileName = 'attachments', $folderName = 'attachments')
    {
        $extensions = ['jpeg', 'png', 'jpg', 'gif'];

        foreach ($request[$fileName] as $attachment) {
            $fileBaseName = str_replace(
                '.' . $attachment['file']->getClientOriginalExtension(),
                '',
                $attachment['file']->getClientOriginalName()
            );
            $newFileName = strtolower(time() . str_random(5) . '-' . str_slug($fileBaseName)) . '.' . $attachment['file']->getClientOriginalExtension();
            if (in_array(strtolower($attachment['file']->getClientOriginalExtension()), $extensions)) {
                $resized_image = Image::make($attachment['file']->getRealPath());
                $resized_image->stream('jpg', 50);
                Storage::disk('public')->put($folderName  . '/' . $newFileName, $resized_image);
            } else {
                Storage::disk('public')->put($folderName  . '/' . $newFileName, file_get_contents($attachment['file']));
            }
            $model->attachmentable()->create([
                'title' => $attachment['title'] ?? null,
                'description' => $attachment['description'] ?? null,
                'path' => $newFileName,
            ]);
        }
    }


    if (!function_exists('getAllAttachments')) {


        function getAllAttachments($attachments, $fileName)
        {
            $allAttachments = [];
            foreach ($attachments  as $key => $attachment) {
                $allAttachments[$key]['title'] = $attachment->title;
                $allAttachments[$key]['description'] = $attachment->description;
                $allAttachments[$key]['path'] = asset('storage/' . $fileName . '/' . $attachment->path);
            }
            return $allAttachments;
        }
    }

    if (!function_exists('sendFireBaseNotification')) {
        function sendFireBaseNotification(
            $title,
            $description,
            $imageUrl = null,
            $sendType = null,
            $ids = [],
            $entityId = null
        ) {
            //        $entity = entity::find($entityId);
            fcm()->to($ids)->priority('high')->timeToLive(0)->data([
                'title' => $title,
                //            'link'  => $entity == null ? url('/') : 'snapsell://entity/notify/' . $entity,
            ])->notification([
                'title' => $title,
                'body'  => $description,
                'image' => $imageUrl,
                //            'link'  => $entity == null ? url('/') : 'snapsell://entity/notify/' . $entity,
            ])->send();
        }
    }
}
    // if (!function_exists('uploadImage')) {
    //     function uploadImage($request, $fileName = 'image', $folderName = 'images')
    //     {
    //         $file = $request->file($fileName);
    //         $fileBaseName = str_replace(
    //             '.' . $file->getClientOriginalExtension(),
    //             '',
    //             $file->getClientOriginalName()
    //         );
    //         $newFileName = strtolower(time() . str_random(5) . '-' . str_slug($fileBaseName)) . '.' . $file->getClientOriginalExtension();
    //         $resizeFile = \Image::make($file->getRealPath());
    //         $resizeFile->save('storage/' . $folderName . '/' . $newFileName);
    //         return asset('storage/' . $folderName . '/' . $newFileName);
    //     }
    // }

    // if (!function_exists('uploadImageWithReturnPath')) {
    //     function uploadImageWithReturnPath($request, $fileName = 'image', $folderName = 'images')
    //     {
    //         $file = $request->file($fileName);
    //         $fileBaseName = str_replace(
    //             '.' . $file->getClientOriginalExtension(),
    //             '',
    //             $file->getClientOriginalName()
    //         );
    //         $newFileName = strtolower(time() . str_random(5) . '-' . str_slug($fileBaseName)) . '.' . $file->getClientOriginalExtension();
    //         $resizeFile = \Image::make($file->getRealPath());
    //         $resizeFile->save('storage/' . $folderName . '/' . $newFileName);
    //         return public_path('storage/' . $folderName . '/' . $newFileName);
    //     }
    // }
if (!function_exists('uid')) {
    function uid($d) {
            $number=('BH_p_'.substr(Str::uuid(),0,8));
            $find = $d::where('uid',$number)->first();


        if ($find !=null){
            uid($d);
        }
        return $number;
    }

}
if (!function_exists('uidn')) {

        function uidn($d) {

            $number=('BH_n_'.substr(Str::uuid(),0,8));
            $find = $d::where('uid',$number)->first();


            if ($find !=null){
                uid($d);
            }
            return $number;
        }

}

// For Visa and Master
if (!function_exists('paymentViMs')) {

    function paymentViMs($request) {
        $url = "https://eu-test.oppwa.com/payments";
        $data = "entityId=8ac7a4ca84c684140184c7b2b95e01c3" .
            "&testMode=EXTERNAL".
            "&merchantTransactionId=".$request['master_id'].
            "&customer.email=".$request['email'].
            "&billing.street1=".$request['street1'].
            "&billing.city=".$request['city'].
            "&billing.state=".$request['state'].
            "&billing.country=".$request['country'].
            "&billing.postcode=".$request['postcode'].
            "&customer.givenName=".$request['givenName'].
            "&customer.surname=".$request['surname'].
            "&amount=".$request['amount'] .
            "&currency=SAR" .
            "&paymentBrand=".$request['paymentBrand'] .
            "&paymentType=DB" .
            "&card.number=".$request['cardNumber'] .
            "&card.holder=".$request['cardHolder'] .
            "&card.expiryMonth=".$request['expiryMonth'] .
            "&card.expiryYear=".$request['expiryYear'] .
            "&card.cvv=".$request['cvv'] .
            "&standingInstruction.mode=INITIAL" .
            "&standingInstruction.source=CIT" .
            "&createRegistration=true"
        ;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer OGFjN2E0Y2E4NGM2ODQxNDAxODRjN2FkNzI0YzAxYjl8ZVQydEhoTjhBag=='));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $responseData;
    }
}

// For Mada and Apple Pay
if (!function_exists('paymentMaAp')) {

    function paymentMaAp($request) {
        $url = "https://eu-test.oppwa.com/payments";
        $data = "entityId=8ac7a4ca84c684140184c7b550b901c7" .
            "&testMode=EXTERNAL".
            "&merchantTransactionId=".$request['master_id'].
            "&customer.email=".$request['email'].
            "&billing.street1=".$request['street1'].
            "&billing.city=".$request['city'].
            "&billing.state=".$request['state'].
            "&billing.country=".$request['country'].
            "&billing.postcode=".$request['postcode'].
            "&customer.givenName=".$request['givenName'].
            "&customer.surname=".$request['surname'].
            "&amount=".$request['amount'] .
            "&currency=SAR" .
            "&paymentBrand=".$request['paymentBrand'] .
            "&paymentType=DB" .
            "&card.number=".$request['cardNumber'] .
            "&card.holder=".$request['cardHolder'] .
            "&card.expiryMonth=".$request['expiryMonth'] .
            "&card.expiryYear=".$request['expiryYear'] .
            "&card.cvv=".$request['cvv'] .
            "&standingInstruction.mode=INITIAL" .
            "&standingInstruction.source=CIT" .
            "&createRegistration=true"
        ;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer OGFjN2E0Y2E4NGM2ODQxNDAxODRjN2FkNzI0YzAxYjl8ZVQydEhoTjhBag=='));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $responseData;
    }

}
