<?php

namespace App\Traits;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Api Responser Trait
|--------------------------------------------------------------------------
|
| This trait will be used for any response we sent to clients.
|
*/

trait ApiResponser
{
    /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, string $message = null, int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = null, int $code, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function image_upload($image, $path)
    {

        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($path), $imageName);
        $imagepath = $path . "/" . $imageName;

        return $imagepath;
    }

    protected function image_delete($file)
    {
        if (File::exists(public_path($file))) {
            $data =  File::delete(public_path($file));
            return $data;
        }
    }

    function calculateWeeksDuration($start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);

        $interval = $start->diff($end);

        $weeks = floor($interval->days / 7);

        return $weeks;
    }
}
