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
use Illuminate\Support\Facades\Log;

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
        $token_limit_set = strtolower(getenv('TOKEN_LIMIT')) ?? "false";
        $reachedLimit = false;
        if ($token_limit_set == "true"){
            $reachedLimit = $this->checkTokenLimit();
        }

        if(!$reachedLimit){
            return parent::handleAiConnectionRequest($request);
            //return NULL;
        }
        
        $translation = $this->languageController->getTranslation();
        $message = $translation["tokenUsedMessage"];
        $content = ['content' => [
            'text' => $message,
            ],
        ];
        if ($request['broadcast']){
            return $this->handleGroupChatRequest($request, $content);
            //return null;
        }

        return $this->handleRequest($request, $content);        
    }


    private function checkTokenLimit()
    {
        $Limit = env('LIMIT', 0);

        if ($Limit < 0){
            return true;
        }
        //'user_increased_limit'
        $today = Carbon::today();
        $userId = Auth::user()->id;

        $userLimit = DB::table('user_increased_limit')
            ->select('limit')
            ->where('user_id', $userId)
            ->first();

        if (!is_null($userLimit)){
            $Limit = $userLimit;
        }

        $result = DB::table('usage_records')
            ->selectRaw('SUM(prompt_tokens + completion_tokens) AS total' )
            ->where('updated_at', '>=', $today->subDays(2))
            ->where('user_id', $userId)
            ->groupBy('user_id')
            ->first();

        //Log::info('My DBquery:' . ($result->total ?? "0"));

        if ( is_null($result) || $result->total <= $Limit){
            return false;
        }

        return true;
    }

    private function handleRequest(request $data, array $content)
    {
        $request = $data;
        $hawki  = User::find(1); // HAWKI user 
        $avatar_url = $this->avatarStorage->getUrl('profile_avatars',
                                            $hawki->username,
                                            $hawki->avatar_id);
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
                    'content' => json_encode($content['content'])
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
                    'content' => ''
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
                'content'=> json_encode($content['content']),
            ]); 
        }
    }

    private function handleGroupChatRequest(request $data, array $content): void
    {
        $isUpdate = (bool) ($data['isUpdate'] ?? false);
        $room = Room::where('slug', $data['slug'])->firstOrFail();

        // Broadcast initial generation status
        $generationStatus = [
            'type' => 'status',
            'data' => [
                'slug' => $room->slug,
                'isGenerating' => true,
                'model' => $data['payload']['model']
            ]
        ];
        broadcast(new RoomMessageEvent($generationStatus));

        // Process the request
        $response = $content;

        $crypto = new SymmetricCrypto();
        $encryptedData = $crypto->encrypt($response['content']['text'],
                                          base64_decode($data['key']));

        // Store message
        $messageHandler = MessageHandlerFactory::create('group');
        $member = $room->members()->where('user_id', 1)->firstOrFail();

        if ($isUpdate) {
            $message = $messageHandler->update($room, [
                'message_id' => $data['messageId'],
                'model' => $data['payload']['model'],
                'content' => [
                    'text' => [
                        'ciphertext' => base64_encode($encryptedData->ciphertext),
                        'iv' => base64_encode($encryptedData->iv),
                        'tag' => base64_encode($encryptedData->tag),
                    ]
                ]
            ]);
        } else {
            $message = $messageHandler->create($room, [
                'threadId' => $data['threadIndex'],
                'member' => $member,
                'message_role'=> 'assistant',
                'model'=> $data['payload']['model'],
                'content' => [
                    'text' => [
                        'ciphertext' => base64_encode($encryptedData->ciphertext),
                        'iv' => base64_encode($encryptedData->iv),
                        'tag' => base64_encode($encryptedData->tag),
                    ]
                ]
            ]);
        }


        $broadcastObject = [
            'slug' => $room->slug,
            'message_id'=> $message->message_id,
        ];
        SendMessage::dispatch($broadcastObject, $isUpdate)->onQueue('message_broadcast');

        // Update and broadcast final generation status
        $generationStatus = [
            'type' => 'status',
            'data' => [
                'slug' => $room->slug,
                'isGenerating' => false,
                'model' => $data['payload']['model']
            ]
        ];

        broadcast(new RoomMessageEvent($generationStatus));
    }


}