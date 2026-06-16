<?php

return [
    [
       'active'=> env('MODELS_MISTRAL_PIXTRAL_LARGE_ACTIVE', false),
       'id' => 'eu.mistral.pixtral-large-2502-v1:0',
       'label' => 'Pixtral Large',
       'region' => '',
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
       ],

   ],

    [
       'active'=> env('MODELS_MISTRAL_8x7B_INSTRUCT_ACTIVE', false),
       'id' => 'mistral.mixtral-8x7b-instruct-v0:1',
       'label' => 'Mistral 8x7B Instruct',
       'region' => 'eu-west-3',
       "input"=> [
           "text",
       ],
       "output"=> [
           "text"
       ],
       'tools' => [
           'stream' => env('MODELS_MISTRAL_8x7B_INSTRUCT_STREAM', false),
           'vision' => env('MODELS_MISTRAL_8x7B_INSTRUCT_VISION', false),
           'file_upload' => env('MODELS_MISTRAL_8x7B_INSTRUCT_FILE_UPLOAD', false),
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
