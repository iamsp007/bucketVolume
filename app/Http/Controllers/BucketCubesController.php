<?php

namespace App\Http\Controllers;

use App\Models\Buckets;
use App\Models\Balls;
use App\Models\BucketBalls;
use App\Models\PlacedBalls;
use Illuminate\Http\Request;

class BucketCubesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    
    public function storeData(Request $request)
    {
        if($request->type == "bucket") {
//            If store buckets
            Buckets::updateOrCreate([
                'name' => $request->name,
            ], [
                'volume' => $request->volume,
                'remaining' => $request->volume
            ]);
        }else {
//            If store balls
            Balls::updateOrCreate([
                'name' => $request->name,
            ], [
                'volume' => $request->volume
            ]);
        }
        
    }
    
    public function placeBalls(Request $request)
    {
        PlacedBalls::truncate(); // Truncate Table Before New Entry
        BucketBalls::truncate(); // Truncate Table Before New Entry
        $buckets = Buckets::all(); // Get Buckets For update data to empty
        foreach ($buckets as $b){
            Buckets::where(['id'=>$b->id])->update(['used_volume'=> 0,'remaining'=> $b->volume]); // Update Bucket Volume
        }
        
        $totalBalls = Balls::count(); // Get Total Balls
        // Insert balls quantity
        for($i=1;$i<=$totalBalls;$i++) {
            $ballName = "ball_quantity_".$i;
            if(isset($request->$ballName)) {
                $placeBall = new PlacedBalls;
                $placeBall->ball_id = $i;
                $placeBall->total_balls = $request->$ballName;
                $placeBall->pending_balls = $request->$ballName;
                $placeBall->save();
            }
        }
        
        $placedBalls = PlacedBalls::with('ballDetails')->where('pending_balls', '!=' , 0)->get(); // Get Placed Balls With Volume And Quantity For Equal Volume Only
        if(!empty($placedBalls)) {
            foreach ($placedBalls as $v) {
                $ball_id = $v->ball_id;
                $totalBalls = $v->total_balls;
                $volume = $v->ballDetails->volume;
                $totalVolume = $v->ballDetails->volume * $totalBalls;
                
                $bucketDetails = Buckets::where('remaining',$totalVolume)->first(); // get bucket if remaining volume and total placed ball volume is equal
                if($bucketDetails) {
                    $newUsedBalls = $v->used_balls + $totalBalls;
                    $newUsedVolume = $bucketDetails->used_volume + $totalVolume;
                        
                    PlacedBalls::where(['id'=>$v->id])->update(['used_balls'=> $newUsedBalls,'pending_balls'=> 0]); // Update Place Balls
                    Buckets::where(['id'=>$bucketDetails->id])->update(['used_volume'=> $newUsedVolume,'remaining'=> 0]); // Update Bucket Volume
                    
                    // Insert balls with which bucket
                    $bucketBall = new BucketBalls;
                    $bucketBall->bucket_id = $bucketDetails->id;
                    $bucketBall->ball_id = $ball_id;
                    $bucketBall->ball_quantity = $totalBalls;
                    $bucketBall->save();
                }else {
                    $bucketDetails = Buckets::where('remaining','>',$totalVolume)->first(); // get bucket if remaining volume greater than total placed ball volume
                    if($bucketDetails) {
                        $newUsedBalls = $v->used_balls + $totalBalls;
                        $newUsedVolume = $bucketDetails->used_volume + $totalVolume;
                        $newRemainingVolumeBucket = $bucketDetails->remaining - $totalVolume;

                        PlacedBalls::where(['id'=>$v->id])->update(['used_balls'=> $newUsedBalls,'pending_balls'=> 0]); // Update Place Balls
                        Buckets::where(['id'=>$bucketDetails->id])->update(['used_volume'=> $newUsedVolume,'remaining'=> $newRemainingVolumeBucket]); // Update Bucket Volume

                        // Insert balls with which bucket
                        $bucketBall = new BucketBalls;
                        $bucketBall->bucket_id = $bucketDetails->id;
                        $bucketBall->ball_id = $ball_id;
                        $bucketBall->ball_quantity = $totalBalls;
                        $bucketBall->save();
                    }else {
                        $bucketDetails = Buckets::where('remaining','!=',0)->get(); // get all bucket if remaining volume is not zero
                        foreach ($bucketDetails as $bucket) {
                            $placedBallsCUrrentDetail = PlacedBalls::with('ballDetails')->where('id', $v->id)->first();
                            
                            $remainingVolume = $bucket->remaining;
                            $requiredVolume = $placedBallsCUrrentDetail->pending_balls * $placedBallsCUrrentDetail->ballDetails->volume;
                            $totalBalls = $placedBallsCUrrentDetail->pending_balls;
                            $ballVolume = $placedBallsCUrrentDetail->ballDetails->volume;
                            
                            $collectingBalls = $remainingVolume / $volume;
                            $collectimgVolume = $collectingBalls * $volume;
                            $newUsedBalls = $placedBallsCUrrentDetail->used_balls + $collectingBalls;
                            $newPendingBalls = $placedBallsCUrrentDetail->pending_balls - $collectingBalls;
                            $newUsedVolume = $bucket->used_volume + $collectimgVolume;
                            $newRemainingVolumeBucket = $bucket->remaining - $collectimgVolume;
                            
                            PlacedBalls::where(['id'=>$v->id])->update(['used_balls'=> $newUsedBalls,'pending_balls'=> $newPendingBalls]); // Update Place Balls
                            Buckets::where(['id'=>$bucket->id])->update(['used_volume'=> $newUsedVolume,'remaining'=> $newRemainingVolumeBucket]); // Update Bucket Volume

                            // Insert balls with which bucket
                            $bucketBall = new BucketBalls;
                            $bucketBall->bucket_id = $bucket->id;
                            $bucketBall->ball_id = $placedBallsCUrrentDetail->ball_id;
                            $bucketBall->ball_quantity = $totalBalls;
                            $bucketBall->save();
                            
                        }
                    }
                }
            }
        }
        
        return redirect()->back()->with('status','Balls Placed Successfully.');
    }
    
    public function getAllBucketsWithVolume(Request $request)
    {
        $buckets = Buckets::with('usedVolumeDetails')->get();
        foreach ($buckets as $bucket) {
            foreach ($bucket->usedVolumeDetails as $ball) {
                $ballDetails = Balls::find($ball->ball_id);
                $ball['ballDetails'] = $ballDetails;
            }
        }
        return $buckets;
    }
    
    public function getAllBallsWithVolume(Request $request)
    {
        $balls = Balls::all();
        return $balls;
    }
    
    public function getAllPlacedBalls(Request $request)
    {
        $placedBalls = PlacedBalls::with('ballDetails')->get();
        $html = "";
        if(!empty($placedBalls)) {
            foreach ($placedBalls as $v) {
                $html.='<div style="background-color:'.$v->ballDetails->name.';">'
                        . '<span>Ball Name : '.$v->ballDetails->name.' (ID : '.$v->ballDetails->id.') (Volume : '.$v->ballDetails->volume.' Cubic inches)</span></br>'
                        . '<span>Total Balls : '.$v->total_balls.' (Total Volume : '.$v->total_balls * $v->ballDetails->volume.' Cubic inches)</span></br>'
                        . '<span>Used Balls : '.$v->used_balls.'</span></br>'
                        . '<span>Pending Balls : '.$v->pending_balls.'</span></br>'
                        . '</div>';
            }
        }else {
            $html = "You have no any placed balls";
        }
        return $html;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BucketCubes  $bucketCubes
     * @return \Illuminate\Http\Response
     */
    public function show(BucketCubes $bucketCubes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BucketCubes  $bucketCubes
     * @return \Illuminate\Http\Response
     */
    public function edit(BucketCubes $bucketCubes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BucketCubes  $bucketCubes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BucketCubes $bucketCubes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BucketCubes  $bucketCubes
     * @return \Illuminate\Http\Response
     */
    public function destroy(BucketCubes $bucketCubes)
    {
        //
    }
}
