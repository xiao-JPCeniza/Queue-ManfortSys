<?php

return [
    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'mimes:mp4', 'max:3145728'],
        'max_upload_time' => 120,
    ],
];
