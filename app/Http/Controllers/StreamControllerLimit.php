<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RoomController;

use App\Models\User;
use App\Models\Room;
use App\Models\Message;
use App\Models\Member;


use App\Services\AI\UsageAnalyzerService;
use App\Services\AI\AIConnectionService;
use App\Services\AI\AIProviderFactory;

use App\Jobs\SendMessage;
use App\Events\RoomMessageEvent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StreamControllerLimit extends StreamController
{
    protected $aiFormatter;

    public function __construct( UsageAnalyzerService $usageAnalyzer,
                                AIConnectionService $aiConnection){
        parent::__construct( $usageAnalyzer, $aiConnection);
    }



    public function handleAiConnectionRequest(Request $request)
    {

        $user = User::find(1); // HAWKI user 
        $avatar_url = $user->avatar_id !== '' ? Storage::disk('public')->url('profile_avatars/' . $user->avatar_id) : null;


        
        if (strtolower(env('TOKEN_LIMIT', '')) == "true"){
            $reachedLimit = $this->checkTokenLimit();
        }else{
            $reachedLimit = false;
        }

        if(!$reachedLimit){
            parent::handleAiConnectionRequest($request);
        }
        

        $message = $translation["test"];
            $content = ['content' => [
                'text' => $message,
                ],
            ];

            if ($request['payload']['stream']){

                return response()->stream(function () use ($user, $avatar_url, $request, $content){

                    $messageData = [
                        'author' => [
                            'username' => $user->username,
                            'name' => $user->name,
                            'avatar_url' => $avatar_url,
                        ],
                        'model' => $request['payload']['model'],
                        'isDone' => false,
                        'content' => $content['content'],
                    ];
                    //Log::info('My Return:' . json_encode($messageData));
                    echo json_encode($messageData) . "\n";


                    $messagefinal = [
                        'author' => [
                            'username' => $user->username,
                            'name' => $user->name,
                            'avatar_url' => $avatar_url,
                        ],
                        'model' => $request['payload']['model'],
                        'isDone' => true,
                        'content' => json_encode($content['content'])
                    ];
                    //Log::info('My Return:' . json_encode($messagefinal));
                    echo json_encode($messagefinal) . "\n";


                },200, [            
                    'Content-Type' => 'text/event-stream',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                    'Access-Control-Allow-Origin'=> '*'            
            ]);

            }else{

            return response()->json([
                'author' => [
                        'username' => 'test',
                        'name' => '$user->name',
                        'avatar_url' => $avatar_url,
                        ],
                'model' => $request['payload']['model'],
                'isDone' => true,
                'content'=> $content['content'],
            ]); 
        }

    }


    private function checkTokenLimit()
    {
        $today = Carbon::today();
        $userId = Auth::user()->id;

        $result = DB::table('usage_records')
            ->selectRaw('SUM(prompt_tokens + completion_tokens) AS total' )
            ->where('updated_at', '>=', $today->subDays(2))
            ->where('user_id', $userId)
            ->groupBy('user_id')
            ->first();



        Log::info('My DBquery:' . ($result->total ?? "0"));

        $Limit = env('LIMIT','');

        if ( is_null($result->total) || $result->total <= $Limit){
            return false;
        }

        return true;
    }


}