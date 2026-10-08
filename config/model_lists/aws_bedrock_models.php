<?php

return [
    [
        'active'=> env('MODELS_ANTHROPIC_CLAUDE_HAIKU_4_5_ACTIVE', true),
       'id' => 'eu.anthropic.claude-haiku-4-5-20251001-v1:0',
       'label' => 'Claude Haiku 4.5',
       "input"=> [
           "text",
           "image"
       ],
       "output"=> [
           "text"
       ],
        'region' => 'eu-central-1',
       'tools' => [
           'stream' => env('MODELS_ANTHROPIC_CLAUDE_HAIKU_4_5_STREAM', true),
           'vision' => env('MODELS_ANTHROPIC_CLAUDE_HAIKU_4_5_VISION', false),
           'file_upload' => env('MODELS_ANTHROPIC_CLAUDE_HAIKU_4_5_FILE_UPLOAD', false),
           'region' => 'eu-central-1',
       ],
       'default_params' => [
            'temp' => env('MODELS_AWS_PARAMS_TEMP', 0.7),
            'top_p' => env('MODELS_AWS_PARAMS_TOP_P', 0.9),
        ],

    ],
    [
       'active'=> env('MODELS_MISTRAL_PIXTRAL_LARGE_ACTIVE', false),
       'id' => 'eu.mistral.pixtral-large-2502-v1:0',
       'label' => 'Pixtral Large',
       "input"=> [
           "text",
           "image"
       ],
       "output"=> [
           "text"
       ],
       'tools' => [
           'stream' => env('MODELS_MISTRAL_PIXTRAL_LARGE_STREAM', false),
           'vision' => env('MODELS_MISTRAL_PIXTRAL_LARGE_VISION', false),
           'file_upload' => env('MODELS_MISTRAL_PIXTRAL_LARGE_FILE_UPLOAD', false),
           'region' => 'eu-central-1',
       ],
       'default_params' => [
            'temp' => env('MODELS_AWS_PARAMS_TEMP', 0.7),
            'top_p' => env('MODELS_AWS_PARAMS_TOP_P', 0.9),
        ],

   ],

    [
       'active'=> env('MODELS_MISTRAL_8x7B_INSTRUCT_ACTIVE', false),
       'id' => 'mistral.mixtral-8x7b-instruct-v0:1',
       'label' => 'Mistral 8x7B Instruct',
       "input"=> [
           "text",
       ],
       "output"=> [
           "text"
       ],
       'tools' => [
           'stream' => env('MODELS_MISTRAL_8x7B_INSTRUCT_STREAM', true),
           'vision' => env('MODELS_MISTRAL_8x7B_INSTRUCT_VISION', false),
           'file_upload' => env('MODELS_MISTRAL_8x7B_INSTRUCT_FILE_UPLOAD', false),
           'region' => 'eu-west-3',
       ],
       'default_params' => [
            'temp' => env('MODELS_AWS_PARAMS_TEMP', 0.7),
            'top_p' => env('MODELS_AWS_PARAMS_TOP_P', 0.9),
        ],

   ]

   //    [
//        'active'=> false,
//        'id' => 'model-id',
//        'label' => 'Model label',
//        "input"=> [
//            "text",
//        ],
//        "output"=> [
//            "text"
//        ],
//        'tools' => [
//            'stream' => true,
//            'vision' => false,
//            'file_upload' => false,
//        ],
//
//    ],
];
