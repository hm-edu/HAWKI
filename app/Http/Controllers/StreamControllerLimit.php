<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RoomController;

use App\Events\RoomMessageEvent;
use App\Jobs\SendMessage;

use App\Models\Room;
use App\Models\User;
use App\Services\AI\AiService;
use App\Services\AI\UsageAnalyzerService;
use App\Services\AI\Value\AiResponse;
use App\Services\Chat\Message\MessageHandlerFactory;
use App\Services\Storage\AvatarStorageService;
use Hawk\HawkiCrypto\SymmetricCrypto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StreamControllerLimit extends StreamController
{
    protected $aiFormatter;

    public function __construct( UsageAnalyzerService $usageAnalyzer,
                                AiService            $aiService,
                                AvatarStorageService $avatarStorage,
                                LanguageController $languageController
                                ){
        $this->languageController = $languageController;
        $this->avatarStorage = $avatarStorage;
        parent::__construct( $usageAnalyzer, $aiService, $avatarStorage);
    }



    public function handleAiConnectionRequest(Request $request)
    {

        $hawki  = User::find(1); // HAWKI user 
        $avatar_url = $this->avatarStorage->getUrl('profile_avatars',
                                            $hawki->username,
                                            $hawki->avatar_id);


        
        if (strtolower(env('TOKEN_LIMIT', '')) == "true"){
            $reachedLimit = $this->checkTokenLimit();
        }else{
            $reachedLimit = false;
        }

        if(!$reachedLimit){
            parent::handleAiConnectionRequest($request);
        }
        
        $translation = $this->languageController->getTranslation();
        $message = $translation["test"];
            $content = ['content' => [
                'text' => $message,
                ],
            ];

            if ($request['payload']['stream']){

                return response()->stream(function () use ($hawki, $avatar_url, $request, $content){

                    $messageData = [
                        'author' => [
                            'username' => $hawki->username,
                            'name' => $hawki->name,
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
                            'username' => $hawki->username,
                            'name' => $hawki->name,
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
                        'name' => '$hawki->name',
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



//        Log::info('My DBquery:' . ($result->total ?? "0"));

        $Limit = env('LIMIT','');        

        if ( is_null($result->total) || $result->total <= $Limit){
            return false;
        }

        return true;
    }


}