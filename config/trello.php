<?php

return [
    'key'      => env('TRELLO_API_KEY'),
    'token'    => env('TRELLO_TOKEN'),
    'board_id' => env('TRELLO_BOARD_ID'),

    'lists' => [
        'draft'    => env('TRELLO_LIST_DRAFT'),
        'sent'     => env('TRELLO_LIST_SENT'),
        'accepted'  => env('TRELLO_LIST_ACCEPTED'),
        'completed' => env('TRELLO_LIST_COMPLETED'),
        'rejected'  => env('TRELLO_LIST_REJECTED'),
        'expired'   => env('TRELLO_LIST_EXPIRED'),
    ],
];
