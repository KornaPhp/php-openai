<p align="center">
    <img src="https://raw.githubusercontent.com/openai-php/client/main/art/example.png" width="600" alt="OpenAI PHP">
    <p align="center">
        <a href="https://github.com/openai-php/client/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/openai-php/client/tests.yml?branch=main&label=tests&style=round-square"></a>
        <a href="https://packagist.org/packages/openai-php/client"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/openai-php/client"></a>
        <a href="https://packagist.org/packages/openai-php/client"><img alt="Latest Version" src="https://img.shields.io/packagist/v/openai-php/client"></a>
        <a href="https://packagist.org/packages/openai-php/client"><img alt="License" src="https://img.shields.io/github/license/openai-php/client"></a>
    </p>
</p>

------
**OpenAI PHP** is a community-maintained PHP API client that allows you to interact with the [Open AI API](https://platform.openai.com/docs/api-reference/introduction).

- Follow the creator Nuno Maduro:
    - YouTube: **[youtube.com/@nunomaduro](https://www.youtube.com/@nunomaduro)** — Videos every weekday
    - Twitch: **[twitch.tv/enunomaduro](https://www.twitch.tv/enunomaduro)** — Streams (almost) every weekday
    - Twitter / X: **[x.com/enunomaduro](https://x.com/enunomaduro)**
    - LinkedIn: **[linkedin.com/in/nunomaduro](https://www.linkedin.com/in/nunomaduro)**
    - Instagram: **[instagram.com/enunomaduro](https://www.instagram.com/enunomaduro)**
    - Tiktok: **[tiktok.com/@enunomaduro](https://www.tiktok.com/@enunomaduro)**

If you or your business relies on this package, it's important to support the developers who have contributed their time and effort to create and maintain this valuable tool:

- Nuno Maduro: **[github.com/sponsors/nunomaduro](https://github.com/sponsors/nunomaduro)**
- Sandro Gehri: **[github.com/sponsors/gehrisandro](https://github.com/sponsors/gehrisandro)**

## Table of Contents
- [Get Started](#get-started)
- [Usage](#usage)
  - [Models Resource](#models-resource)
  - [Responses Resource](#responses-resource)
  - [Chat Resource](#chat-resource)
  - [Audio Resource](#audio-resource)
  - [Embeddings Resource](#embeddings-resource)
  - [Files Resource](#files-resource)
  - [FineTuning Resource](#finetuning-resource)
  - [Moderations Resource](#moderations-resource)
  - [Images Resource](#images-resource)
  - [Vector Stores Resource](#vector-stores-resource)
  - [Vector Stores Files Resource](#vector-store-files-resource)
  - [Vector Stores File Batches Resource](#vector-store-file-batches-resource)
  - [Batches Resource](#batches-resource)
  - [Realtime Ephemeral Keys](#realtime-ephemeral-keys)
  - [Completions Resource (legacy)](#completions-resource-legacy)
  - [Assistants Resource (deprecated)](#assistants-resource-deprecated)
  - [Thread Resource (deprecated)](#threads-resource-deprecated)
  - [Thread Messages Resource (deprecated)](#thread-messages-resource-deprecated)
  - [Thread Runs Resource (deprecated)](#thread-runs-resource-deprecated)
  - [Thread Runs Steps Resource (deprecated)](#thread-run-steps-resource-deprecated)
  - [FineTunes Resource (deprecated)](#finetunes-resource-deprecated)
  - [Edits Resource (deprecated)](#edits-resource-deprecated)
- [Meta Information](#meta-information)
- [Troubleshooting](#troubleshooting)
- [Testing](#testing)
- [Services](#services)
  - [Azure](#azure)

## Get Started

> **Requires [PHP 8.2+](https://www.php.net/releases/)**

First, install OpenAI via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require openai-php/client
```

Ensure that the `php-http/discovery` composer plugin is allowed to run or install a client manually if your project does not already have a PSR-18 client integrated.
```bash
composer require guzzlehttp/guzzle
```

Then, interact with OpenAI's API:

```php
$yourApiKey = getenv('YOUR_API_KEY');
$client = OpenAI::client($yourApiKey);

$result = $client->chat()->create([
    'model' => 'gpt-4o',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
]);

echo $result->choices[0]->message->content; // Hello! How can I assist you today?
```

If necessary, it is possible to configure and create a separate client.

```php
$yourApiKey = getenv('YOUR_API_KEY');

$client = OpenAI::factory()
    ->withApiKey($yourApiKey)
    ->withOrganization('your-organization') // default: null
    ->withProject('Your Project') // default: null
    ->withBaseUri('openai.example.com/v1') // default: api.openai.com/v1
    ->withHttpClient($httpClient = new \GuzzleHttp\Client([])) // default: HTTP client found using PSR-18 HTTP Client Discovery
    ->withHttpHeader('X-My-Header', 'foo')
    ->withQueryParam('my-param', 'bar')
    ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $httpClient->send($request, [
        'stream' => true // Allows to provide a custom stream handler for the http client.
    ]))
    ->make();
```

## Usage

### `Models` Resource

#### `list`

Lists the currently available models, and provides basic information about each one such as the owner and availability.

```php
$response = $client->models()->list();

$response->object; // 'list'

foreach ($response->data as $result) {
    $result->id; // 'gpt-3.5-turbo-instruct'
    $result->object; // 'model'
    // ...
}

$response->toArray(); // ['object' => 'list', 'data' => [...]]
```

#### `retrieve`

Retrieves a model instance, providing basic information about the model such as the owner and permissioning.

```php
$response = $client->models()->retrieve('gpt-3.5-turbo-instruct');

$response->id; // 'gpt-3.5-turbo-instruct'
$response->object; // 'model'
$response->created; // 1642018370
$response->ownedBy; // 'openai'

$response->toArray(); // ['id' => 'gpt-3.5-turbo-instruct', ...]
```

#### `delete`

Delete a fine-tuned model.

```php
$response = $client->models()->delete('curie:ft-acmeco-2021-03-03-21-44-20');

$response->id; // 'curie:ft-acmeco-2021-03-03-21-44-20'
$response->object; // 'model'
$response->deleted; // true

$response->toArray(); // ['id' => 'curie:ft-acmeco-2021-03-03-21-44-20', ...]
```

### `Responses` Resource

#### `create`

Creates a model response. Provide text or image inputs to generate text or JSON outputs. Have the model call your own custom code or use built-in tools like web search or file search to use your own data as input for the model's response.

```php
$response = $client->responses()->create([
    'model' => 'gpt-4o-mini',
    'tools' => [
        [
            'type' => 'web_search_preview'
        ]
    ],
    'input' => "what was a positive news story from today?",
    'temperature' => 0.7,
    'max_output_tokens' => 150,
    'tool_choice' => 'auto',
    'parallel_tool_calls' => true,
    'store' => true,
    'metadata' => [
        'user_id' => '123',
        'session_id' => 'abc456'
    ]
]);

$response->id; // 'resp_67ccd2bed1ec8190b14f964abc054267'
$response->object; // 'response'
$response->createdAt; // 1741476542
$response->status; // 'completed'
$response->model; // 'gpt-4o-mini'

foreach ($response->output as $output) {
    $output->type; // 'message'
    $output->id; // 'msg_67ccd2bf17f0819081ff3bb2cf6508e6'
    $output->status; // 'completed'
    $output->role; // 'assistant'
    
    foreach ($output->content as $content) {
        $content->type; // 'output_text'
        $content->text; // The response text
        $content->annotations; // Any annotations in the response
    }
}

$response->usage->inputTokens; // 36
$response->usage->outputTokens; // 87
$response->usage->totalTokens; // 123

$response->toArray(); // ['id' => 'resp_67ccd2bed1ec8190b14f964abc054267', ...]
```

#### `create streamed`

When you create a Response with stream set to true, the server will emit server-sent events to the client as the Response is generated. All events and their payloads can be found in [OpenAI docs](https://platform.openai.com/docs/api-reference/responses-streaming).

```php
$stream = $client->responses()->createStreamed([
    'model' => 'gpt-4o-mini',
    'tools' => [
        [
            'type' => 'web_search_preview'
        ]
    ],
    'input' => "what was a positive news story from today?",
]);

foreach ($stream as $response) {
    $response->event; // 'response.created'
}
```

#### `retrieve`

Retrieves a model response with the given ID.

```php
$response = $client->responses()->retrieve('resp_67ccd2bed1ec8190b14f964abc054267');

$response->id; // 'resp_67ccd2bed1ec8190b14f964abc054267'
$response->object; // 'response'
$response->createdAt; // 1741476542
$response->status; // 'completed'
$response->error; // null
$response->incompleteDetails; // null
$response->instructions; // null
$response->maxOutputTokens; // null
$response->model; // 'gpt-4o-mini-2024-07-18"'
$response->parallelToolCalls; // true
$response->previousResponseId; // null
$response->store; // true
$response->temperature; // 1.0
$response->toolChoice; // 'auto'
$response->topP; // 1.0
$response->truncation; // 'disabled'

$response->toArray(); // ['id' => 'resp_67ccd2bed1ec8190b14f964abc054267', ...]
```

#### `cancel`

Cancel a model response (background request) with the given ID.

```php
$response = $client->responses()->cancel('resp_67ccd2bed1ec8190b14f964abc054267');

$response->id; // 'resp_67ccd2bed1ec8190b14f964abc054267'
$response->status; // 'canceled'

$response->toArray(); // ['id' => 'resp_67ccd2bed1ec8190b14f964abc054267', 'status' => 'canceled', ...]
```

#### `delete`

Deletes a model response with the given ID.

```php
$response = $client->responses()->delete('resp_67ccd2bed1ec8190b14f964abc054267');

$response->id; // 'resp_67ccd2bed1ec8190b14f964abc054267'
$response->object; // 'response'
$response->deleted; // true

$response->toArray(); // ['id' => 'resp_67ccd2bed1ec8190b14f964abc054267', 'deleted' => true, ...]
```

#### `list`

Lists input items for a response with the given ID. All events and their payloads can be found in [OpenAI docs](https://platform.openai.com/docs/api-reference/responses/list).

```php
$response = $client->responses()->list('resp_67ccd2bed1ec8190b14f964abc054267', [
    'limit' => 10,
    'order' => 'desc'
]);

$response->object; // 'list'

foreach ($response->data as $item) {
    $item->type; // 'message'
    $item->id; // 'msg_680bf4e8c1948192b64abf0bad54b30806e0834f49400fc3'
    $item->status; // 'completed'
    $item->role; // 'user'
}

$response->firstId; // 'msg_680bf4e8c1948192b64abf0bad54b30806e0834f49400fc3'
$response->lastId; // 'msg_680bf4e8c1948192b64abf0bad54b30806e0834f49400fc3'
$response->hasMore; // false

$response->toArray(); // ['object' => 'list', 'data' => [...], ...]
```

### `Completions` Resource

#### `create`

Creates a completion for the provided prompt and parameters.

```php
$response = $client->completions()->create([
    'model' => 'gpt-3.5-turbo-instruct',
    'prompt' => 'Say this is a test',
    'max_tokens' => 6,
    'temperature' => 0
]);

$response->id; // 'cmpl-uqkvlQyYK7bGYrRHQ0eXlWi7'
$response->object; // 'text_completion'
$response->created; // 1589478378
$response->model; // 'gpt-3.5-turbo-instruct'

foreach ($response->choices as $choice) {
    $choice->text; // '\n\nThis is a test'
    $choice->index; // 0
    $choice->logprobs; // null
    $choice->finishReason; // 'length' or null
}

$response->usage->promptTokens; // 5,
$response->usage->completionTokens; // 6,
$response->usage->totalTokens; // 11

$response->toArray(); // ['id' => 'cmpl-uqkvlQyYK7bGYrRHQ0eXlWi7', ...]
```

#### `create streamed`

Creates a streamed completion for the provided prompt and parameters.

```php
$stream = $client->completions()->createStreamed([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'Hi',
        'max_tokens' => 10,
    ]);

foreach($stream as $response){
    $response->choices[0]->text;
}
// 1. iteration => 'I'
// 2. iteration => ' am'
// 3. iteration => ' very'
// 4. iteration => ' excited'
// ...
```

### `Chat` Resource

#### `create`

Creates a completion for the chat message.

```php
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
]);

$response->id; // 'chatcmpl-6pMyfj1HF4QXnfvjtfzvufZSQq6Eq'
$response->object; // 'chat.completion'
$response->created; // 1677701073
$response->model; // 'gpt-3.5-turbo-0301'

foreach ($response->choices as $choice) {
    $choice->index; // 0
    $choice->message->role; // 'assistant'
    $choice->message->content; // '\n\nHello there! How can I assist you today?'
    $choice->logprobs; // null
    $choice->finishReason; // 'stop'
}

$response->usage->promptTokens; // 9,
$response->usage->completionTokens; // 12,
$response->usage->totalTokens; // 21

$response->toArray(); // ['id' => 'chatcmpl-6pMyfj1HF4QXnfvjtfzvufZSQq6Eq', ...]
```

Creates a completion for the chat message with a tool call.

```php
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo-0613',
    'messages' => [
        ['role' => 'user', 'content' => 'What\'s the weather like in Boston?'],
    ],
    'tools' => [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_current_weather',
                'description' => 'Get the current weather in a given location',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA',
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit']
                        ],
                    ],
                    'required' => ['location'],
                ],
            ],
        ]
    ]
]);

$response->id; // 'chatcmpl-6pMyfj1HF4QXnfvjtfzvufZSQq6Eq'
$response->object; // 'chat.completion'
$response->created; // 1677701073
$response->model; // 'gpt-3.5-turbo-0613'

foreach ($response->choices as $choice) {
    $choice->index; // 0
    $choice->message->role; // 'assistant'
    $choice->message->content; // null
    $choice->message->toolCalls[0]->id; // 'call_123'
    $choice->message->toolCalls[0]->type; // 'function'
    $choice->message->toolCalls[0]->function->name; // 'get_current_weather'
    $choice->message->toolCalls[0]->function->arguments; // "{\n  \"location\": \"Boston, MA\"\n}"
    $choice->finishReason; // 'tool_calls'
}

$response->usage->promptTokens; // 82,
$response->usage->completionTokens; // 18,
$response->usage->totalTokens; // 100
```

Creates a completion for the chat message with a function call.

```php
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo-0613',
    'messages' => [
        ['role' => 'user', 'content' => 'What\'s the weather like in Boston?'],
    ],
    'functions' => [
        [
            'name' => 'get_current_weather',
            'description' => 'Get the current weather in a given location',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The city and state, e.g. San Francisco, CA',
                    ],
                    'unit' => [
                        'type' => 'string',
                        'enum' => ['celsius', 'fahrenheit']
                    ],
                ],
                'required' => ['location'],
            ],
        ]
    ]
]);

$response->id; // 'chatcmpl-6pMyfj1HF4QXnfvjtfzvufZSQq6Eq'
$response->object; // 'chat.completion'
$response->created; // 1677701073
$response->model; // 'gpt-3.5-turbo-0613'

foreach ($response->choices as $choice) {
    $choice->index; // 0
    $choice->message->role; // 'assistant'
    $choice->message->content; // null
    $choice->message->functionCall->name; // 'get_current_weather'
    $choice->message->functionCall->arguments; // "{\n  \"location\": \"Boston, MA\"\n}"
    $choice->finishReason; // 'function_call'
}

$response->usage->promptTokens; // 82,
$response->usage->completionTokens; // 18,
$response->usage->totalTokens; // 100
```

Creates a chat completion with image input via `image_url`.  
Useful for describing and analyzing visual content.

```php
$response = $client->chat()->create([
    'model' => 'gpt-4o',
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'What is in this image?'],
                // Replace with a real, accessible image
                ['type' => 'image_url', 'image_url' => ['url' => 'https://example.com/image.jpg']], 
            ]
        ]
    ]
]);
```

#### `create streamed`

Creates a streamed completion for the chat message.

```php
$stream = $client->chat()->createStreamed([
    'model' => 'gpt-4o',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
]);

foreach($stream as $response){
    $response->choices[0]->toArray();
}
// 1. iteration => ['index' => 0, 'delta' => ['role' => 'assistant'], 'finish_reason' => null]
// 2. iteration => ['index' => 0, 'delta' => ['content' => 'Hello'], 'finish_reason' => null]
// 3. iteration => ['index' => 0, 'delta' => ['content' => '!'], 'finish_reason' => null]
// ...
```

To get usage report when using stream you can use `include_usage` in `stream_options` .

```php
$stream = $client->chat()->createStreamed([
    'model' => 'gpt-4',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
    'stream_options'=>[
        'include_usage' => true,
    ]
]);

foreach($stream as $response){
    if($response->usage !== null){
        $response->usage->promptTokens; // 9,
        $response->usage->completionTokens; // 12,
        $response->usage->totalTokens; // 21
    }
}
```

`usage` is always `null` except for the last chunk which contains the token usage statistics for the entire request.

### `Audio` Resource

#### `speech`

Generates audio from the input text.

```php
$client->audio()->speech([
    'model' => 'tts-1',
    'input' => 'The quick brown fox jumped over the lazy dog.',
    'voice' => 'alloy',
]); // audio file content as string
```

#### `speechStreamed`

Generates streamed audio from the input text.

```php
$stream = $client->audio()->speechStreamed([
    'model' => 'tts-1',
    'input' => 'The quick brown fox jumped over the lazy dog.',
    'voice' => 'alloy',
]);

foreach($stream as $chunk){
    $chunk; // chunk of audio file content as string
}
```

#### `transcribe`

Transcribes audio into the input language.

```php
$response = $client->audio()->transcribe([
    'model' => 'whisper-1',
    'file' => fopen('audio.mp3', 'r'),
    'response_format' => 'verbose_json',
    'timestamp_granularities' => ['segment', 'word']
]);

$response->task; // 'transcribe'
$response->language; // 'english'
$response->duration; // 2.95
$response->text; // 'Hello, how are you?'

foreach ($response->segments as $segment) {
    $segment->index; // 0
    $segment->seek; // 0
    $segment->start; // 0.0
    $segment->end; // 4.0
    $segment->text; // 'Hello, how are you?'
    $segment->tokens; // [50364, 2425, 11, 577, 366, 291, 30, 50564]
    $segment->temperature; // 0.0
    $segment->avgLogprob; // -0.45045216878255206
    $segment->compressionRatio; // 0.7037037037037037
    $segment->noSpeechProb; // 0.1076972484588623
    $segment->transient; // false
}

foreach ($response->words as $word) {
    $word->word; // 'Hello'
    $word->start; // 0.31
    $word->end; // 0.92
}

$response->toArray(); // ['task' => 'transcribe', ...]
```

#### `transcribe streamed`

Transcribes audio into the input language with streaming.

```php
$stream = $client->audio()->transcribeStreamed([
    'model' => 'gpt-4o-transcribe',
    'file' => fopen('audio.mp3', 'r'),
]);

foreach ($stream as $event) {
    echo json_encode($event->toArray()); // {"event":"transcript.text.delta","data":{"delta":"The"}}
}
```

#### `translate`

Translates audio into English.

```php
$response = $client->audio()->translate([
    'model' => 'whisper-1',
    'file' => fopen('german.mp3', 'r'),
    'response_format' => 'verbose_json',
]);

$response->task; // 'translate'
$response->language; // 'english'
$response->duration; // 2.95
$response->text; // 'Hello, how are you?'

foreach ($response->segments as $segment) {
    $segment->index; // 0
    $segment->seek; // 0
    $segment->start; // 0.0
    $segment->end; // 4.0
    $segment->text; // 'Hello, how are you?'
    $segment->tokens; // [50364, 2425, 11, 577, 366, 291, 30, 50564]
    $segment->temperature; // 0.0
    $segment->avgLogprob; // -0.45045216878255206
    $segment->compressionRatio; // 0.7037037037037037
    $segment->noSpeechProb; // 0.1076972484588623
    $segment->transient; // false
}

$response->toArray(); // ['task' => 'translate', ...]
```

### `Embeddings` Resource

#### `create`

Creates an embedding vector representing the input text.

```php
$response = $client->embeddings()->create([
    'model' => 'text-similarity-babbage-001',
    'input' => 'The food was delicious and the waiter...',
]);

$response->object; // 'list'

foreach ($response->embeddings as $embedding) {
    $embedding->object; // 'embedding'
    $embedding->model; // 'text-similarity-babbage-001'
    $embedding->embedding; // [0.018990106880664825, -0.0073809814639389515, ...]
    $embedding->index; // 0
}

$response->usage->promptTokens; // 8,
$response->usage->totalTokens; // 8

$response->toArray(); // ['data' => [...], ...]
```

### `Files` Resource

#### `list`

Returns a list of files that belong to the user's organization.

```php
$response = $client->files()->list();

$response->object; // 'list'

foreach ($response->data as $result) {
    $result->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
    $result->object; // 'file'
    // ...
}

$response->toArray(); // ['object' => 'list', 'data' => [...]]
```

#### `delete`

Delete a file.

```php
$response = $client->files()->delete($file);

$response->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
$response->object; // 'file'
$response->deleted; // true

$response->toArray(); // ['id' => 'file-XjGxS3KTG0uNmNOK362iJua3', ...]
```

#### `retrieve`

Returns information about a specific file.

```php
$response = $client->files()->retrieve('file-XjGxS3KTG0uNmNOK362iJua3');

$response->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
$response->object; // 'file'
$response->bytes; // 140
$response->createdAt; // 1613779657
$response->filename; // 'mydata.jsonl'
$response->purpose; // 'fine-tune'
$response->status; // 'succeeded'
$response->status_details; // null

$response->toArray(); // ['id' => 'file-XjGxS3KTG0uNmNOK362iJua3', ...]
```

#### `upload`

Upload a file that contains document(s) to be used across various endpoints/features.

```php
$response = $client->files()->upload([
        'purpose' => 'fine-tune',
        'file' => fopen('my-file.jsonl', 'r'),
    ]);

$response->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
$response->object; // 'file'
$response->bytes; // 140
$response->createdAt; // 1613779657
$response->filename; // 'mydata.jsonl'
$response->purpose; // 'fine-tune'
$response->status; // 'succeeded'
$response->status_details; // null

$response->toArray(); // ['id' => 'file-XjGxS3KTG0uNmNOK362iJua3', ...]
```

#### `download`

Returns the contents of the specified file.

```php
$client->files()->download($file); // '{"prompt": "<prompt text>", ...'
```

### `FineTuning` Resource

#### `create job`

Creates a job that fine-tunes a specified model from a given dataset.

```php
$response = $client->fineTuning()->createJob([
    'training_file' => 'file-abc123',
    'validation_file' => null,
    'model' => 'gpt-3.5-turbo',
    'hyperparameters' => [
        'n_epochs' => 4,
    ],
    'suffix' => null,
]);

$response->id; // 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F'
$response->object; // 'fine_tuning.job'
$response->model; // 'gpt-3.5-turbo-0613'
$response->fineTunedModel; // null
// ...

$response->toArray(); // ['id' => 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F', ...]
```

#### `list jobs`

List your organization's fine-tuning jobs.

```php
$response = $client->fineTuning()->listJobs();

$response->object; // 'list'

foreach ($response->data as $result) {
    $result->id; // 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F'
    $result->object; // 'fine_tuning.job'
    // ...
}

$response->toArray(); // ['object' => 'list', 'data' => [...]]
```

You can pass additional parameters to the `listJobs` method to narrow down the results.

```php
$response = $client->fineTuning()->listJobs([
    'limit' => 3, // Number of jobs to retrieve (Default: 20)
    'after' => 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F', // Identifier for the last job from the previous pagination request.
]);
```

#### `retrieve job`

Get info about a fine-tuning job.

```php
$response = $client->fineTuning()->retrieveJob('ftjob-AF1WoRqd3aJAHsqc9NY7iL8F');

$response->id; // 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F'
$response->object; // 'fine_tuning.job'
$response->model; // 'gpt-3.5-turbo-0613'
$response->createdAt; // 1614807352
$response->finishedAt; // 1692819450
$response->fineTunedModel; // 'ft:gpt-3.5-turbo-0613:jwe-dev::7qnxQ0sQ'
$response->organizationId; // 'org-jwe45798ASN82s'
$response->resultFiles[0]; // 'file-1bl05WrhsKDDEdg8XSP617QF'
$response->status; // 'succeeded'
$response->validationFile; // null
$response->trainingFile; // 'file-abc123'
$response->trainedTokens; // 5049

$response->hyperparameters->nEpochs; // 9

$response->toArray(); // ['id' => 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F', ...]
```

#### `cancel job`

Immediately cancel a fine-tune job.

```php
$response = $client->fineTuning()->cancelJob('ftjob-AF1WoRqd3aJAHsqc9NY7iL8F');

$response->id; // 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F'
$response->object; // 'fine_tuning.job'
// ...
$response->status; // 'cancelled'
// ...

$response->toArray(); // ['id' => 'ftjob-AF1WoRqd3aJAHsqc9NY7iL8F', ...]
```

#### `list job events`

Get status updates for a fine-tuning job.

```php
$response = $client->fineTuning()->listJobEvents('ftjob-AF1WoRqd3aJAHsqc9NY7iL8F');

$response->object; // 'list'

foreach ($response->data as $result) {
    $result->object; // 'fine_tuning.job.event' 
    $result->createdAt; // 1614807352
    // ...
}

$response->toArray(); // ['object' => 'list', 'data' => [...]]
```

You can pass additional parameters to the `listJobEvents` method to narrow down the results.

```php
$response = $client->fineTuning()->listJobEvents('ftjob-AF1WoRqd3aJAHsqc9NY7iL8F', [
    'limit' => 3, // Number of events to retrieve (Default: 20)
    'after' => 'ftevent-kLPSMIcsqshEUEJVOVBVcHlP', // Identifier for the last event from the previous pagination request.
]);
```

### `Moderations` Resource

#### `create`

Classifies if text violates OpenAI's Content Policy.

```php

$response = $client->moderations()->create([
    'model' => 'text-moderation-latest',
    'input' => 'I want to k*** them.',
]);

$response->id; // modr-5xOyuS
$response->model; // text-moderation-003

foreach ($response->results as $result) {
    $result->flagged; // true

    foreach ($result->categories as $category) {
        $category->category->value; // 'violence'
        $category->violated; // true
        $category->score; // 0.97431367635727
    }
}

$response->toArray(); // ['id' => 'modr-5xOyuS', ...]
```

### `Images` Resource

#### `create`

Creates an image given a prompt.

```php
$response = $client->images()->create([
    'model' => 'dall-e-3',
    'prompt' => 'A cute baby sea otter',
    'n' => 1,
    'size' => '1024x1024',
    'response_format' => 'url',
]);

$response->created; // 1589478378

foreach ($response->data as $data) {
    $data->url; // 'https://oaidalleapiprodscus.blob.core.windows.net/private/...'
    $data->b64_json; // null
}

$response->toArray(); // ['created' => 1589478378, data => ['url' => 'https://oaidalleapiprodscus...', ...]]
```

#### `edit`

Creates an edited or extended image given an original image and a prompt.

```php
$response = $client->images()->edit([
    'image' => fopen('image_edit_original.png', 'r'),
    'mask' => fopen('image_edit_mask.png', 'r'),
    'prompt' => 'A sunlit indoor lounge area with a pool containing a flamingo',
    'n' => 1,
    'size' => '256x256',
    'response_format' => 'url',
]);

$response->created; // 1589478378

foreach ($response->data as $data) {
    $data->url; // 'https://oaidalleapiprodscus.blob.core.windows.net/private/...'
    $data->b64_json; // null
}

$response->toArray(); // ['created' => 1589478378, data => ['url' => 'https://oaidalleapiprodscus...', ...]]
```

#### `variation`

Creates a variation of a given image.

```php
$response = $client->images()->variation([
    'image' => fopen('image_edit_original.png', 'r'),
    'n' => 1,
    'size' => '256x256',
    'response_format' => 'url',
]);

$response->created; // 1589478378

foreach ($response->data as $data) {
    $data->url; // 'https://oaidalleapiprodscus.blob.core.windows.net/private/...'
    $data->b64_json; // null
}

$response->toArray(); // ['created' => 1589478378, data => ['url' => 'https://oaidalleapiprodscus...', ...]]
```

### `Batches` Resource

#### `create`

Creates a batch.

```php

$fileResponse = $client->files()->upload(
     parameters: [
          'purpose' => 'batch',
          'file' => fopen('commands.jsonl', 'r'),
    ]
);

$fileId = $fileResponse->id;

$response = $client->batches()->create(
    parameters: [
        'input_file_id' => $fileId,
        'endpoint' => '/v1/chat/completions',
        'completion_window' => '24h'
    ]
 );

$response->id; // 'batch_abc123'
$response->object; // 'batch'
$response->endpoint; // /v1/chat/completions
$response->errors; // null
$response->completionWindow; // '24h'
$response->status; // 'validating'
$response->outputFileId; // null
$response->errorFileId; // null
$response->createdAt; // 1714508499
$response->inProgressAt; // null
$response->expiresAt; // 1714536634
$response->completedAt; // null
$response->failedAt; // null
$response->expiredAt; // null
$response->requestCounts; // null
$response->metadata; // ['name' => 'My batch name']

$response->toArray(); // ['id' => 'batch_abc123', ...]
```

#### `retrieve`

Retrieves a batch.

```php
$response = $client->batches()->retrieve(id: 'batch_abc123');

$response->id; // 'batch_abc123'
$response->object; // 'batch'
$response->endpoint; // /v1/chat/completions
$response->errors; // null
$response->completionWindow; // '24h'
$response->status; // 'validating'
$response->outputFileId; // null
$response->errorFileId; // null
$response->createdAt; // 1714508499
$response->inProgressAt; // null
$response->expiresAt; // 1714536634
$response->completedAt; // null
$response->failedAt; // null
$response->expiredAt; // null
$response->requestCounts->total; // 100
$response->requestCounts->completed; // 95
$response->requestCounts->failed; // 5
$response->metadata; // ['name' => 'My batch name']

$response->toArray(); // ['id' => 'batch_abc123', ...]
```

#### `cancel`

Cancels a batch.

```php
$response = $client->batches()->cancel(id: 'batch_abc123');

$response->id; // 'batch_abc123'
$response->object; // 'batch'
$response->endpoint; // /v1/chat/completions
$response->errors; // null
$response->completionWindow; // '24h'
$response->status; // 'cancelling'
$response->outputFileId; // null
$response->errorFileId; // null
$response->createdAt; // 1711471533
$response->inProgressAt; // 1711471538
$response->expiresAt; // 1711557933
$response->cancellingAt; // 1711475133
$response->cancelledAt; // null
$response->requestCounts->total; // 100
$response->requestCounts->completed; // 23
$response->requestCounts->failed; // 1
$response->metadata; // ['name' => 'My batch name']

$response->toArray(); // ['id' => 'batch_abc123', ...]
```

#### `list`

Returns a list of batches.

```php
$response = $client->batches()->list(
    parameters: [
        'limit' => 10, 
    ],
);

$response->object; // 'list'
$response->firstId; // 'batch_abc123'
$response->lastId; // 'batch_abc456'
$response->hasMore; // true

foreach ($response->data as $result) {
    $result->id; // 'batch_abc123'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

### `Vector Stores` Resource

#### `create`

Create a vector store.

```php
$response = $client->vectorStores()->create([
    'file_ids' => [
        'file-fUU0hFRuQ1GzhOweTNeJlCXG',
    ],
    'name' => 'My first Vector Store',
]);

$response->id; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->object; // 'vector_store'
$response->createdAt; // 1717703267
$response->name; // 'My first Vector Store'
$response->usageBytes; // 0
$response->fileCounts->inProgress; // 1
$response->fileCounts->completed; // 0
$response->fileCounts->failed; // 0
$response->fileCounts->cancelled; // 0
$response->fileCounts->total; // 1
$response->status; // 'in_progress'
$response->expiresAfter; // null
$response->expiresAt; // null
$response->lastActiveAt; // 1717703267

$response->toArray(); // ['id' => 'vs_vzfQhlTWVUl38QGqQAoQjeDF', ...]
```

#### `retrieve`

Retrieves a vector store.

```php
$response = $client->vectorStores()->retrieve(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
);

$response->id; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->object; // 'vector_store'
$response->createdAt; // 1717703267
$response->name; // 'My first Vector Store'
$response->usageBytes; // 0
$response->fileCounts->inProgress; // 1
$response->fileCounts->completed; // 0
$response->fileCounts->failed; // 0
$response->fileCounts->cancelled; // 0
$response->fileCounts->total; // 1
$response->status; // 'in_progress'
$response->expiresAfter; // null
$response->expiresAt; // null
$response->lastActiveAt; // 1717703267

$response->toArray(); // ['id' => 'vs_vzfQhlTWVUl38QGqQAoQjeDF', ...]
```

#### `modify`

Modifies a vector store.

```php
$response = $client->vectorStores()->modify(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    parameters:  [
        'name' => 'New name',
    ],
);

$response->id; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->object; // 'vector_store'
$response->createdAt; // 1717703267
$response->name; // 'New name'
$response->usageBytes; // 0
$response->fileCounts->inProgress; // 1
$response->fileCounts->completed; // 0
$response->fileCounts->failed; // 0
$response->fileCounts->cancelled; // 0
$response->fileCounts->total; // 1
$response->status; // 'in_progress'
$response->expiresAfter; // null
$response->expiresAt; // null
$response->lastActiveAt; // 1717703267

$response->toArray(); // ['id' => 'vs_vzfQhlTWVUl38QGqQAoQjeDF', ...]
```

#### `delete`

Delete a vector store.

```php
$response = $client->vectorStores()->delete(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
);

$response->id; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->object; // 'vector_store.deleted'
$response->deleted; // true

$response->toArray(); // ['id' => 'vs_vzfQhlTWVUl38QGqQAoQjeDF', ...]
```

#### `list`

Returns a list of vector stores.

```php
$response = $client->vectorStores()->list(
    parameters: [
        'limit' => 10,
    ],
);

$response->object; // 'list'
$response->firstId; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->lastId; // 'vs_D5DPOgBxSoEBTmYBgUESdPpa'
$response->hasMore; // true

foreach ($response->data as $result) {
    $result->id; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

### `Vector Store Files` Resource

#### `create`

Create a vector store file by attaching a File to a vector store.

```php
$response = $client->vectorStores()->files()->create(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    parameters: [
        'file_id' => 'file-fUU0hFRuQ1GzhOweTNeJlCXG',
    ]
);

$response->id; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
$response->object; // 'vector_store.file'
$response->usageBytes; // 4553
$response->createdAt; // 1717703267
$response->vectorStoreId; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->status; // 'completed'
$response->lastError; // null
$response->chunkingStrategy->type; // 'static'
$response->chunkingStrategy->maxChunkSizeTokens; // 800
$response->chunkingStrategy->chunkOverlapTokens; // 400

$response->toArray(); // ['id' => 'file-fUU0hFRuQ1GzhOweTNeJlCXG', ...]
```

#### `retrieve`

Retrieves a vector store file.

```php
$response = $client->vectorStores()->files()->retrieve(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    fileId: 'file-fUU0hFRuQ1GzhOweTNeJlCXG',
);

$response->id; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
$response->object; // 'vector_store.file'
$response->usageBytes; // 4553
$response->createdAt; // 1717703267
$response->vectorStoreId; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->status; // 'completed'
$response->lastError; // null
$response->chunkingStrategy->type; // 'static'
$response->chunkingStrategy->maxChunkSizeTokens; // 800
$response->chunkingStrategy->chunkOverlapTokens; // 400

$response->toArray(); // ['id' => 'file-fUU0hFRuQ1GzhOweTNeJlCXG', ...]
```

#### `delete`

Delete a vector store file. This will remove the file from the vector store but the file itself will not be deleted. To delete the file, use the delete file endpoint.

```php
$response = $client->vectorStores()->files()->delete(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    fileId: 'file-fUU0hFRuQ1GzhOweTNeJlCXG',
);

$response->id; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
$response->object; // 'vector_store.file.deleted'
$response->deleted; // true

$response->toArray(); // ['id' => 'file-fUU0hFRuQ1GzhOweTNeJlCXG', ...]
```

#### `list`

Returns a list of vector store files.

```php
$response = $client->vectorStores()->files()->list(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    parameters: [
        'limit' => 10,
    ],
);

$response->object; // 'list'
$response->firstId; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
$response->lastId; // 'file-D5DPOgBxSoEBTmYBgUESdPpa'
$response->hasMore; // true

foreach ($response->data as $result) {
    $result->id; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

#### `search`

Search a vector store for relevant chunks based on a query and file attributes filter.

```php
$response = $client->vectorStores()->search(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    parameters: [
        'query' => 'What is the return policy?',
        'max_num_results' => 5,
        'filters' => [],
        'rewrite_query' => false
    ]
);

$response->object; // 'vector_store.search_results.page'
$response->searchQuery; // 'What is the return policy?'
$response->hasMore; // false
$response->nextPage; // null
foreach ($response->data as $file) {
    $result->fileId; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
    $result->filename; // 'return_policy.pdf'
    $result->score; // 0.95
    $result->attributes; // ['category' => 'customer_service']

    foreach ($result->content as $content) {
        $content->type; // 'text'
        $content->text; // 'Our return policy allows customers to return items within 30 days...'
    }
}

$response->toArray(); // ['object' => 'vector_store.search_results.page', ...]
```

### `Vector Store File Batches` Resource

#### `create`

Create a vector store file batch.

```php
$response = $client->vectorStores()->batches()->create(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    parameters: [
        'file_ids' => [
            'file-fUU0hFRuQ1GzhOweTNeJlCXG',
        ],
    ]
);

$response->id; // 'vsfb_123'
$response->object; // 'vector_store.files_batch'
$response->createdAt; // 1698107661
$response->vectorStoreId; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->status; // 'completed'
$response->fileCounts->inProgress; // 1
$response->fileCounts->completed; // 0
$response->fileCounts->failed; // 0
$response->fileCounts->cancelled; // 0
$response->fileCounts->total; // 1

$response->toArray(); // ['id' => 'vsfb_123', ...]
```

#### `retrieve`

Retrieves a vector store file batch.

```php
$response = $client->vectorStores()->batches()->retrieve(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    fileBatchId: 'vsfb_123',
);

$response->id; // 'vsfb_123'
$response->object; // 'vector_store.files_batch'
$response->createdAt; // 1698107661
$response->vectorStoreId; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->status; // 'completed'
$response->fileCounts->inProgress; // 1
$response->fileCounts->completed; // 0
$response->fileCounts->failed; // 0
$response->fileCounts->cancelled; // 0
$response->fileCounts->total; // 1

$response->toArray(); // ['id' => 'vsfb_123', ...]
```

#### `cancel`

Cancel a vector store file batch. This attempts to cancel the processing of files in this batch as soon as possible.

```php
$response = $client->vectorStores()->batches()->cancel(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    fileBatchId: 'vsfb_123',
);

$response->id; // 'vsfb_123'
$response->object; // 'vector_store.files_batch'
$response->createdAt; // 1698107661
$response->vectorStoreId; // 'vs_vzfQhlTWVUl38QGqQAoQjeDF'
$response->status; // 'cancelling'
$response->fileCounts->inProgress; // 1
$response->fileCounts->completed; // 0
$response->fileCounts->failed; // 0
$response->fileCounts->cancelled; // 0
$response->fileCounts->total; // 1

$response->toArray(); // ['id' => 'vsfb_123', ...]
```

#### `list`

Returns a list of vector store files.

```php
$response = $client->vectorStores()->batches()->listFiles(
    vectorStoreId: 'vs_vzfQhlTWVUl38QGqQAoQjeDF',
    fileBatchId: 'vsfb_123',
    parameters: [
        'limit' => 10,
    ],
);

$response->object; // 'list'
$response->firstId; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
$response->lastId; // 'file-D5DPOgBxSoEBTmYBgUESdPpa'
$response->hasMore; // true

foreach ($response->data as $result) {
    $result->id; // 'file-fUU0hFRuQ1GzhOweTNeJlCXG'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

### Realtime Ephemeral Keys

#### `token`

Create an ephemeral API token for real time sessions.

```php
$response = $client->realtime()->token();

$response->clientSecret->value // 'ek-1234567890abcdefg'
$response->clientSecret->expiresAt // 1717703267
```

#### `transcribeToken`

Create an ephemeral API token for real time transcription sessions.

```php
$response = $client->realtime()->transcribeToken();

$response->clientSecret->value // 'et-1234567890abcdefg'
$response->clientSecret->expiresAt // 1717703267
```

### `Completions` Resource (legacy)

> [!WARNING]  
> The `Completions` resource was marked "Legacy" by OpenAI in July 2023. Please use the `Chat` resource instead.

<details>
<summary>Completion API Information</summary>

#### `create`

Creates a completion for the provided prompt and parameters.

```php
$response = $client->completions()->create([
    'model' => 'gpt-3.5-turbo-instruct',
    'prompt' => 'Say this is a test',
    'max_tokens' => 6,
    'temperature' => 0
]);

$response->id; // 'cmpl-uqkvlQyYK7bGYrRHQ0eXlWi7'
$response->object; // 'text_completion'
$response->created; // 1589478378
$response->model; // 'gpt-3.5-turbo-instruct'

foreach ($response->choices as $choice) {
    $choice->text; // '\n\nThis is a test'
    $choice->index; // 0
    $choice->logprobs; // null
    $choice->finishReason; // 'length' or null
}

$response->usage->promptTokens; // 5,
$response->usage->completionTokens; // 6,
$response->usage->totalTokens; // 11

$response->toArray(); // ['id' => 'cmpl-uqkvlQyYK7bGYrRHQ0eXlWi7', ...]
```

#### `create streamed`

Creates a streamed completion for the provided prompt and parameters.

```php
$stream = $client->completions()->createStreamed([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'Hi',
        'max_tokens' => 10,
    ]);

foreach($stream as $response){
    $response->choices[0]->text;
}
// 1. iteration => 'I'
// 2. iteration => ' am'
// 3. iteration => ' very'
// 4. iteration => ' excited'
// ...
```

</details>

### `Assistants` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the Assistants API and will stop working by first half of 2026. https://platform.openai.com/docs/guides/responses-vs-chat-completions#assistants

<details>
<summary>Assistants API Information</summary>

> **Note** - If you are creating the client manually from the factory. Make sure you provide the necessary header:
> ```php
> $factory->withHttpHeader('OpenAI-Beta', 'assistants=v2')
> ```

#### `create`

Create an assistant with a model and instructions.

```php
$response = $client->assistants()->create([
    'instructions' => 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.',
    'name' => 'Math Tutor',
    'tools' => [
        [
            'type' => 'code_interpreter',
        ],
    ],
    'model' => 'gpt-4o',
]);

$response->id; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->object; // 'assistant'
$response->createdAt; // 1623936000
$response->name; // 'Math Tutor'
$response->instructions; // 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.'
$response->model; // 'gpt-4o'
$response->description; // null
$response->tools[0]->type; // 'code_interpreter'
$response->toolResources; // []
$response->metadata; // []
$response->temperature: // null
$response->topP: // null
$response->format: // 'auto'

$response->toArray(); // ['id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd', ...]
```

#### `retrieve`

Retrieves an assistant.

```php
$response = $client->assistants()->retrieve('asst_gxzBkD1wkKEloYqZ410pT5pd');

$response->id; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->object; // 'assistant'
$response->createdAt; // 1623936000
$response->name; // 'Math Tutor'
$response->instructions; // 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.'
$response->model; // 'gpt-4o'
$response->description; // null
$response->tools[0]->type; // 'code_interpreter'
$response->toolResources; // []
$response->metadata; // []
$response->temperature: // null
$response->topP: // null
$response->format: // 'auto'

$response->toArray(); // ['id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd', ...]
```

#### `modify`

Modifies an assistant.

```php
$response = $client->assistants()->modify('asst_gxzBkD1wkKEloYqZ410pT5pd', [
        'name' => 'New Math Tutor',
    ]);

$response->id; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->object; // 'assistant'
$response->createdAt; // 1623936000
$response->name; // 'New Math Tutor'
$response->instructions; // 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.'
$response->model; // 'gpt-4o'
$response->description; // null
$response->tools[0]->type; // 'code_interpreter'
$response->toolResources; // []
$response->metadata; // []
$response->temperature: // null
$response->topP: // null
$response->format: // 'auto'

$response->toArray(); // ['id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd', ...]
```

#### `delete`

Delete an assistant.

```php
$response = $client->assistants()->delete('asst_gxzBkD1wkKEloYqZ410pT5pd');

$response->id; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->object; // 'assistant.deleted'
$response->deleted; // true

$response->toArray(); // ['id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd', ...]
```

#### `list`

Returns a list of assistants.

```php
$response = $client->assistants()->list([
    'limit' => 10,
]);

$response->object; // 'list'
$response->firstId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->lastId; // 'asst_reHHtAM0jKLDIxanM6gP6DaR'
$response->hasMore; // true

foreach ($response->data as $result) {
    $result->id; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

</details>

### `Threads` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the Assistants API and will stop working by first half of 2026. https://platform.openai.com/docs/guides/responses-vs-chat-completions#assistants

<details>
<summary>Threads API Information</summary>

#### `create`

Create a thread.

```php
$response = $client->threads()->create([]);

$response->id; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->object; // 'thread'
$response->createdAt; // 1623936000
$response->toolResources; // null
$response->metadata; // []

$response->toArray(); // ['id' => 'thread_tKFLqzRN9n7MnyKKvc1Q7868', ...]
```

#### `createAndRun`

Create a thread and run it in one request.

```php
$response = $client->threads()->createAndRun(
    [
        'assistant_id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd',
        'thread' => [
            'messages' =>
                [
                    [
                        'role' => 'user',
                        'content' => 'Explain deep learning to a 5 year old.',
                    ],
                ],
        ],
    ],
);

$response->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->object; // 'thread.run'
$response->createdAt; // 1623936000
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'queued'
$response->requiredAction; // null
$response->lastError; // null
$response->startedAt; // null
$response->expiresAt; // 1699622335
$response->cancelledAt; // null
$response->failedAt; // null
$response->completedAt; // null
$response->incompleteDetails; // null
$response->lastError; // null
$response->model; // 'gpt-4o'
$response->instructions; // null
$response->tools; // []
$response->metadata; // []
$response->usage->total_tokens; // 579
$response->temperature; // null
$response->topP; // null
$response->maxPromptTokens; // 1000
$response->maxCompletionTokens; // 1000
$response->truncationStrategy->type; // 'auto'
$response->responseFormat; // 'auto'
$response->toolChoice; // 'auto'

$response->toArray(); // ['id' => 'run_4RCYyYzX9m41WQicoJtUQAb8', ...]
```

#### `retrieve`

Retrieves a thread.

```php
$response = $client->threads()->retrieve('thread_tKFLqzRN9n7MnyKKvc1Q7868');

$response->id; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->object; // 'thread'
$response->createdAt; // 1623936000
$response->toolResources; // null
$response->metadata; // []

$response->toArray(); // ['id' => 'thread_tKFLqzRN9n7MnyKKvc1Q7868', ...]
```

#### `modify`

Modifies a thread.

```php
$response = $client->threads()->modify('thread_tKFLqzRN9n7MnyKKvc1Q7868', [
        'metadata' => [
            'name' => 'My new thread name',
        ],
    ]);

$response->id; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->object; // 'thread'
$response->createdAt; // 1623936000
$response->toolResources; // null
$response->metadata; // ['name' => 'My new thread name']

$response->toArray(); // ['id' => 'thread_tKFLqzRN9n7MnyKKvc1Q7868', ...]
```

#### `delete`

Delete a thread.

```php
$response = $client->threads()->delete('thread_tKFLqzRN9n7MnyKKvc1Q7868');

$response->id; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->object; // 'thread.deleted'
$response->deleted; // true

$response->toArray(); // ['id' => 'thread_tKFLqzRN9n7MnyKKvc1Q7868', ...]
```

</details>

### `Thread Messages` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the Assistants API and will stop working by first half of 2026. https://platform.openai.com/docs/guides/responses-vs-chat-completions#assistants

<details>
<summary>Thread Messages API Information</summary>

#### `create`

Create a message.

```php
$response = $client->threads()->messages()->create('thread_tKFLqzRN9n7MnyKKvc1Q7868', [
    'role' => 'user',
    'content' => 'What is the sum of 5 and 7?',
]);

$response->id; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
$response->object; // 'thread.message'
$response->createdAt; // 1623936000
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'in_progress
$response->incompleteDetails; // null
$response->completedAt; // null
$response->incompleteAt; // null
$response->role; // 'user'
$response->content[0]->type; // 'text'
$response->content[0]->text->value; // 'What is the sum of 5 and 7?'
$response->content[0]->text->annotations; // []
$response->assistantId; // null
$response->runId; // null
$response->attachments; // []
$response->metadata; // []

$response->toArray(); // ['id' => 'msg_SKYwvF3zcigxthfn6F4hnpdU', ...]
```

#### `retrieve`

Retrieve a message.

```php
$response = $client->threads()->messages()->retrieve(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    messageId: 'msg_SKYwvF3zcigxthfn6F4hnpdU',
);

$response->id; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
$response->object; // 'thread.message'
$response->createdAt; // 1623936000
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'in_progress
$response->incompleteDetails; // null
$response->completedAt; // null
$response->incompleteAt; // null
$response->role; // 'user'
$response->content[0]->type; // 'text'
$response->content[0]->text->value; // 'What is the sum of 5 and 7?'
$response->content[0]->text->annotations; // []
$response->assistantId; // null
$response->runId; // null
$response->attachments; // []
$response->metadata; // []

$response->toArray(); // ['id' => 'msg_SKYwvF3zcigxthfn6F4hnpdU', ...]
```

#### `modify`

Modifies a message.

```php
$response = $client->threads()->messages()->modify(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    messageId: 'msg_SKYwvF3zcigxthfn6F4hnpdU',
    parameters:  [
        'metadata' => [
            'name' => 'My new message name',
        ],
    ],
);

$response->id; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
$response->object; // 'thread.message'
$response->createdAt; // 1623936000
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'in_progress
$response->incompleteDetails; // null
$response->completedAt; // null
$response->incompleteAt; // null
$response->role; // 'user'
$response->content[0]->type; // 'text'
$response->content[0]->text->value; // 'What is the sum of 5 and 7?'
$response->content[0]->text->annotations; // []
$response->assistantId; // null
$response->runId; // null
$response->attachments; // []
$response->metadata; // ['name' => 'My new message name']

$response->toArray(); // ['id' => 'msg_SKYwvF3zcigxthfn6F4hnpdU', ...]
```

#### `delete`

Deletes a message.

```php
$response = $client->threads()->messages()->delete(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    messageId: 'msg_SKYwvF3zcigxthfn6F4hnpdU'
);

$response->id; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
$response->object; // 'thread.message.deleted'
$response->deleted; // true

$response->toArray(); // ['id' => 'msg_SKYwvF3zcigxthfn6F4hnpdU', ...]
```

#### `list`

Returns a list of messages for a given thread.

```php
$response = $client->threads()->messages()->list('thread_tKFLqzRN9n7MnyKKvc1Q7868', [
    'limit' => 10,
]);

$response->object; // 'list'
$response->firstId; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
$response->lastId; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
$response->hasMore; // false

foreach ($response->data as $result) {
    $result->id; // 'msg_SKYwvF3zcigxthfn6F4hnpdU'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

</details>

### `Thread Runs` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the Assistants API and will stop working by first half of 2026. https://platform.openai.com/docs/guides/responses-vs-chat-completions#assistants

<details>
<summary>Thread Runs API Information</summary>

#### `create`

Create a run.

```php
$response = $client->threads()->runs()->create(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868', 
    parameters: [
        'assistant_id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd',
    ],
);

$response->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->object; // 'thread.run'
$response->createdAt; // 1623936000
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'queued'
$response->startedAt; // null
$response->expiresAt; // 1699622335
$response->cancelledAt; // null
$response->failedAt; // null
$response->completedAt; // null
$response->incompleteDetails; // null
$response->lastError; // null
$response->model; // 'gpt-4o'
$response->instructions; // null
$response->tools; // []
$response->metadata; // []
$response->usage->total_tokens; // 579
$response->temperature; // null
$response->topP; // null
$response->maxPromptTokens; // 1000
$response->maxCompletionTokens; // 1000
$response->truncationStrategy->type; // 'auto'
$response->toolChoice; // 'auto'
$response->responseFormat; // 'auto'

$response->toArray(); // ['id' => 'run_4RCYyYzX9m41WQicoJtUQAb8', ...]
```

#### `create streamed`

Creates a streamed run.

[OpenAI Assistant Events](https://platform.openai.com/docs/api-reference/assistants-streaming/events)

```php
$stream = $client->threads()->runs()->createStreamed(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    parameters: [
        'assistant_id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd',
    ],
);

foreach($stream as $response){
    $response->event // 'thread.run.created' | 'thread.run.in_progress' | .....
    $response->response // ThreadResponse | ThreadRunResponse | ThreadRunStepResponse | ThreadRunStepDeltaResponse | ThreadMessageResponse | ThreadMessageDeltaResponse
}

// ...
```

#### `create streamed with function calls`

Creates a streamed run with function calls

[OpenAI Assistant Events](https://platform.openai.com/docs/api-reference/assistants-streaming/events)

```php
$stream = $client->threads()->runs()->createStreamed(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    parameters: [
        'assistant_id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd',
    ],
);


do{
    foreach($stream as $response){
        $response->event // 'thread.run.created' | 'thread.run.in_progress' | .....
        $response->response // ThreadResponse | ThreadRunResponse | ThreadRunStepResponse | ThreadRunStepDeltaResponse | ThreadMessageResponse | ThreadMessageDeltaResponse

        switch($response->event){
            case 'thread.run.created':
            case 'thread.run.queued':
            case 'thread.run.completed':
            case 'thread.run.cancelling':
                $run = $response->response;
                break;
            case 'thread.run.expired':
            case 'thread.run.cancelled':
            case 'thread.run.failed':
                $run = $response->response;
                break 3;
            case 'thread.run.requires_action':
                // Overwrite the stream with the new stream started by submitting the tool outputs
                $stream = $client->threads()->runs()->submitToolOutputsStreamed(
                    threadId: $run->threadId,
                    runId: $run->id,
                    parameters: [
                        'tool_outputs' => [
                            [
                                'tool_call_id' => 'call_KSg14X7kZF2WDzlPhpQ168Mj',
                                'output' => '12',
                            ]
                        ],
                    ]
                );
                break;
        }
    }
} while ($run->status != "completed")

// ...
```

#### `retrieve`

Retrieves a run.

```php
$response = $client->threads()->runs()->retrieve(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    runId: 'run_4RCYyYzX9m41WQicoJtUQAb8',
);

$response->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->object; // 'thread.run'
$response->createdAt; // 1623936000
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'queued'
$response->startedAt; // null
$response->expiresAt; // 1699622335
$response->cancelledAt; // null
$response->failedAt; // null
$response->completedAt; // null
$response->incompleteDetails; // null
$response->lastError; // null
$response->model; // 'gpt-4o'
$response->instructions; // null
$response->tools; // []
$response->metadata; // []
$response->usage->promptTokens; // 25,
$response->usage->completionTokens; // 32,
$response->usage->totalTokens; // 57
$response->temperature; // null
$response->topP; // null
$response->maxPromptTokens; // 1000
$response->maxCompletionTokens; // 1000
$response->truncationStrategy->type; // 'auto'
$response->toolChoice; // 'auto'
$response->responseFormat; // 'auto'

$response->toArray(); // ['id' => 'run_4RCYyYzX9m41WQicoJtUQAb8', ...]
```

#### `modify`

Modifies a run.

```php
$response = $client->threads()->runs()->modify(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    runId: 'run_4RCYyYzX9m41WQicoJtUQAb8',
    parameters:  [
        'metadata' => [
            'name' => 'My new run name',
        ],
    ],
);

$response->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->object; // 'thread.run'
$response->createdAt; // 1623936000
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'queued'
$response->startedAt; // null
$response->expiresAt; // 1699622335
$response->cancelledAt; // null
$response->failedAt; // null
$response->completedAt; // null
$response->incompleteDetails; // null
$response->lastError; // null
$response->model; // 'gpt-4o'
$response->instructions; // null
$response->tools; // []
$response->usage->total_tokens; // 579
$response->temperature; // null
$response->topP; // null
$response->maxPromptTokens; // 1000
$response->maxCompletionTokens; // 1000
$response->truncationStrategy->type; // 'auto'
$response->toolChoice; // 'auto'
$response->responseFormat; // 'auto'
$response->metadata; // ['name' => 'My new run name']

$response->toArray(); // ['id' => 'run_4RCYyYzX9m41WQicoJtUQAb8', ...]
```

#### `cancel`

Cancels a run that is `in_progress`.

```php
$response = $client->threads()->runs()->cancel(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    runId: 'run_4RCYyYzX9m41WQicoJtUQAb8',
);

$response->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->object; // 'thread.run'
$response->createdAt; // 1623936000
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'cancelling'
$response->startedAt; // null
$response->expiresAt; // 1699622335
$response->cancelledAt; // null
$response->failedAt; // null
$response->completedAt; // null
$response->incompleteDetails; // null
$response->lastError; // null
$response->model; // 'gpt-4o'
$response->instructions; // null
$response->tools; // []
$response->usage?->total_tokens; // 579
$response->temperature; // null
$response->topP; // null
$response->maxPromptTokens; // 1000
$response->maxCompletionTokens; // 1000
$response->truncationStrategy->type; // 'auto'
$response->toolChoice; // 'auto'
$response->responseFormat; // 'auto'
$response->metadata; // []

$response->toArray(); // ['id' => 'run_4RCYyYzX9m41WQicoJtUQAb8', ...]
```

#### `submitToolOutputs`

When a run has the status: `requires_action` and `required_action.type` is `submit_tool_outputs`, this endpoint can be used to submit the outputs from the tool calls once they're all completed. All outputs must be submitted in a single request.

```php
$response = $client->threads()->runs()->submitToolOutputs(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    runId: 'run_4RCYyYzX9m41WQicoJtUQAb8',
    parameters: [
        'tool_outputs' => [
            [
                'tool_call_id' => 'call_KSg14X7kZF2WDzlPhpQ168Mj',
                'output' => '12',
            ],
        ],
    ]
);

$response->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->object; // 'thread.run'
$response->createdAt; // 1623936000
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->status; // 'in_progress'
$response->startedAt; // null
$response->expiresAt; // 1699622335
$response->cancelledAt; // null
$response->failedAt; // null
$response->completedAt; // null
$response->incompleteDetails; // null
$response->lastError; // null
$response->model; // 'gpt-4o'
$response->instructions; // null
$response->usage->total_tokens; // 579
$response->temperature; // null
$response->topP; // null
$response->maxPromptTokens; // 1000
$response->maxCompletionTokens; // 1000
$response->truncationStrategy->type; // 'auto'
$response->responseFormat; // 'auto'
$response->tools[0]->type; // 'function'
$response->toolChoice; // 'auto'
$response->metadata; // []

$response->toArray(); // ['id' => 'run_4RCYyYzX9m41WQicoJtUQAb8', ...]
```

#### `list`

Returns a list of runs belonging to a thread.

```php
$response = $client->threads()->runs()->list(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    parameters: [
        'limit' => 10,
    ],
);

$response->object; // 'list'
$response->firstId; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->lastId; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->hasMore; // false

foreach ($response->data as $result) {
    $result->id; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

</details>

### `Thread Run Steps` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the Assistants API and will stop working by first half of 2026. https://platform.openai.com/docs/guides/responses-vs-chat-completions#assistants

<details>
<summary>Thread Run Steps API Information</summary>

#### `retrieve`

Retrieves a run step.

```php
$response = $client->threads()->runs()->steps()->retrieve(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    runId: 'run_4RCYyYzX9m41WQicoJtUQAb8',
    stepId: 'step_1spQXgbAabXFm1YXrwiGIMUz',
);

$response->id; // 'step_1spQXgbAabXFm1YXrwiGIMUz'
$response->object; // 'thread.run.step'
$response->createdAt; // 1699564106
$response->runId; // 'run_4RCYyYzX9m41WQicoJtUQAb8'
$response->assistantId; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
$response->threadId; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
$response->type; // 'message_creation'
$response->status; // 'completed'
$response->cancelledAt; // null
$response->completedAt; // 1699564119
$response->expiresAt; // null
$response->failedAt; // null
$response->lastError; // null
$response->stepDetails->type; // 'message_creation'
$response->stepDetails->messageCreation->messageId; // 'msg_i404PxKbB92d0JAmdOIcX7vA'

$response->toArray(); // ['id' => 'step_1spQXgbAabXFm1YXrwiGIMUz', ...]
```

#### `list`

Returns a list of run steps belonging to a run.

```php
$response = $client->threads()->runs()->steps()->list(
    threadId: 'thread_tKFLqzRN9n7MnyKKvc1Q7868',
    runId: 'run_4RCYyYzX9m41WQicoJtUQAb8',
    parameters: [
        'limit' => 10,
    ],
);

$response->object; // 'list'
$response->firstId; // 'step_1spQXgbAabXFm1YXrwiGIMUz'
$response->lastId; // 'step_1spQXgbAabXFm1YXrwiGIMUz'
$response->hasMore; // false

foreach ($response->data as $result) {
    $result->id; // 'step_1spQXgbAabXFm1YXrwiGIMUz'
    // ...
}

$response->toArray(); // ['object' => 'list', ...]]
```

</details>

### `Edits` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the Edits API and will stop working by January 4, 2024. https://openai.com/blog/gpt-4-api-general-availability#deprecation-of-the-edits-api

<details>
<summary>Edits API Information</summary>

#### `create`

Creates a new edit for the provided input, instruction, and parameters.

```php
$response = $client->edits()->create([
    'model' => 'text-davinci-edit-001',
    'input' => 'What day of the wek is it?',
    'instruction' => 'Fix the spelling mistakes',
]);

$response->object; // 'edit'
$response->created; // 1589478378

foreach ($response->choices as $choice) {
    $choice->text; // 'What day of the week is it?'
    $choice->index; // 0
}

$response->usage->promptTokens; // 25,
$response->usage->completionTokens; // 32,
$response->usage->totalTokens; // 57

$response->toArray(); // ['object' => 'edit', ...]
```

</details>

### `FineTunes` Resource (deprecated)

> [!WARNING]
> OpenAI has deprecated the FineTunes API and will stop working by January 4, 2024 https://platform.openai.com/docs/deprecations#2023-08-22-fine-tunes-endpoint

<details>
<summary>FineTunes API Information</summary>

#### `create`

Creates a job that fine-tunes a specified model from a given dataset.

```php
$response = $client->fineTunes()->create([
    'training_file' => 'file-ajSREls59WBbvgSzJSVWxMCB',
    'validation_file' => 'file-XjSREls59WBbvgSzJSVWxMCa',
    'model' => 'curie',
    'n_epochs' => 4,
    'batch_size' => null,
    'learning_rate_multiplier' => null,
    'prompt_loss_weight' => 0.01,
    'compute_classification_metrics' => false,
    'classification_n_classes' => null,
    'classification_positive_class' => null,
    'classification_betas' => [],
    'suffix' => null,
]);

$response->id; // 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'
$response->object; // 'fine-tune'
// ...

$response->toArray(); // ['id' => 'ft-AF1WoRqd3aJAHsqc9NY7iL8F', ...]
```

#### `list`

List your organization's fine-tuning jobs.

```php
$response = $client->fineTunes()->list();

$response->object; // 'list'

foreach ($response->data as $result) {
    $result->id; // 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'
    $result->object; // 'fine-tune'
    // ...
}

$response->toArray(); // ['object' => 'list', 'data' => [...]]
```

#### `retrieve`

Gets info about the fine-tune job.

```php
$response = $client->fineTunes()->retrieve('ft-AF1WoRqd3aJAHsqc9NY7iL8F');

$response->id; // 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'
$response->object; // 'fine-tune'
$response->model; // 'curie'
$response->createdAt; // 1614807352
$response->fineTunedModel; // 'curie => ft-acmeco-2021-03-03-21-44-20'
$response->organizationId; // 'org-jwe45798ASN82s'
$response->resultFiles; // [
$response->status; // 'succeeded'
$response->validationFiles; // [
$response->trainingFiles; // [
$response->updatedAt; // 1614807865

foreach ($response->events as $result) {
    $result->object; // 'fine-tune-event' 
    $result->createdAt; // 1614807352
    $result->level; // 'info'
    $result->message; // 'Job enqueued. Waiting for jobs ahead to complete. Queue number =>  0.'
}

$response->hyperparams->batchSize; // 4 
$response->hyperparams->learningRateMultiplier; // 0.1 
$response->hyperparams->nEpochs; // 4 
$response->hyperparams->promptLossWeight; // 0.1

foreach ($response->resultFiles as $result) {
    $result->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
    $result->object; // 'file'
    $result->bytes; // 140
    $result->createdAt; // 1613779657
    $result->filename; // 'mydata.jsonl'
    $result->purpose; // 'fine-tune'
    $result->status; // 'succeeded'
    $result->status_details; // null
}

foreach ($response->validationFiles as $result) {
    $result->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
    // ...
}

foreach ($response->trainingFiles as $result) {
    $result->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
    // ...
}

$response->toArray(); // ['id' => 'ft-AF1WoRqd3aJAHsqc9NY7iL8F', ...]
```

#### `cancel`

Immediately cancel a fine-tune job.

```php
$response = $client->fineTunes()->cancel('ft-AF1WoRqd3aJAHsqc9NY7iL8F');

$response->id; // 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'
$response->object; // 'fine-tune'
// ...
$response->status; // 'cancelled'
// ...

$response->toArray(); // ['id' => 'ft-AF1WoRqd3aJAHsqc9NY7iL8F', ...]
```

#### `list events`

Get fine-grained status updates for a fine-tune job.

```php
$response = $client->fineTunes()->listEvents('ft-AF1WoRqd3aJAHsqc9NY7iL8F');

$response->object; // 'list'

foreach ($response->data as $result) {
    $result->object; // 'fine-tune-event' 
    $result->createdAt; // 1614807352
    // ...
}

$response->toArray(); // ['object' => 'list', 'data' => [...]]
```

#### `list events streamed`

Get streamed fine-grained status updates for a fine-tune job.

```php
$stream = $client->fineTunes()->listEventsStreamed('ft-y3OpNlc8B5qBVGCCVsLZsDST');

foreach($stream as $response){
    $response->message;
}
// 1. iteration => 'Created fine-tune: ft-y3OpNlc8B5qBVGCCVsLZsDST'
// 2. iteration => 'Fine-tune costs $0.00'
// ...
// xx. iteration => 'Uploaded result file: file-ajLKUCMsFPrT633zqwr0eI4l'
// xx. iteration => 'Fine-tune succeeded'
```

</details>

## Meta Information

On all response objects you can access the meta information returned by the API via the `meta()` method.

```php
$response = $client->completions()->create([
    'model' => 'gpt-3.5-turbo-instruct',
    'prompt' => 'Say this is a test',
]);

$meta = $response->meta();

$meta->requestId; // '574a03e2faaf4e9fd703958e4ddc66f5'

$meta->openai->model; // 'gpt-3.5-turbo-instruct'
$meta->openai->organization; // 'org-jwe45798ASN82s'
$meta->openai->version; // '2020-10-01'
$meta->openai->processingMs; // 425

$meta->requestLimit->limit; // 3000
$meta->requestLimit->remaining; // 2999
$meta->requestLimit->reset; // '20ms'

$meta->tokenLimit->limit; // 250000
$meta->tokenLimit->remaining; // 249984
$meta->tokenLimit->reset; // '3ms'
```

The `toArray()` method returns the meta information in the form originally returned by the API.

```php
$meta->toArray();

// [ 
//   'x-request-id' => '574a03e2faaf4e9fd703958e4ddc66f5',
//   'openai-model' => 'gpt-3.5-turbo-instruct',
//   'openai-organization' => 'org-jwe45798ASN82s',
//   'openai-processing-ms' => 402,
//   'openai-version' => '2020-10-01',
//   'x-ratelimit-limit-requests' => 3000,
//   'x-ratelimit-remaining-requests' => 2999,
//   'x-ratelimit-reset-requests' => '20ms',
//   'x-ratelimit-limit-tokens' => 250000,
//   'x-ratelimit-remaining-tokens' => 249983,
//   'x-ratelimit-reset-tokens' => '3ms',
// ]
```

On streaming responses you can access the meta information on the reponse stream object.

```php
$stream = $client->completions()->createStreamed([
    'model' => 'gpt-3.5-turbo-instruct',
    'prompt' => 'Say this is a test',
]);
    
$stream->meta(); 
```

For further details about the rates limits and what to do if you hit them visit the [OpenAI documentation](https://platform.openai.com/docs/guides/rate-limits/rate-limits).

## Troubleshooting

### Timeout

You may run into a timeout when sending requests to the API. The default timeout depends on the HTTP client used.

You can increase the timeout by configuring the HTTP client and passing in to the factory.

This example illustrates how to increase the timeout using Guzzle.
```php
OpenAI::factory()
    ->withApiKey($apiKey)
    ->withOrganization($organization)
    ->withHttpClient(new \GuzzleHttp\Client(['timeout' => $timeout]))
    ->make();
```

## Testing

The package provides a fake implementation of the `OpenAI\Client` class that allows you to fake the API responses.

To test your code ensure you swap the `OpenAI\Client` class with the `OpenAI\Testing\ClientFake` class in your test case.

The fake responses are returned in the order they are provided while creating the fake client.

All responses are having a `fake()` method that allows you to easily create a response object by only providing the parameters relevant for your test case.

```php
use OpenAI\Testing\ClientFake;
use OpenAI\Responses\Completions\CreateResponse;

$client = new ClientFake([
    CreateResponse::fake([
        'choices' => [
            [
                'text' => 'awesome!',
            ],
        ],
    ]),
]);

$completion = $client->completions()->create([
    'model' => 'gpt-3.5-turbo-instruct',
    'prompt' => 'PHP is ',
]);

expect($completion['choices'][0]['text'])->toBe('awesome!');
```

In case of a streamed response you can optionally provide a resource holding the fake response data.

```php
use OpenAI\Testing\ClientFake;
use OpenAI\Responses\Chat\CreateStreamedResponse;

$client = new ClientFake([
    CreateStreamedResponse::fake(fopen('file.txt', 'r'););
]);

$completion = $client->chat()->createStreamed([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!'],
        ],
]);

expect($response->getIterator()->current())
        ->id->toBe('chatcmpl-6yo21W6LVo8Tw2yBf7aGf2g17IeIl');
```

After the requests have been sent there are various methods to ensure that the expected requests were sent:

```php
// assert completion create request was sent
$client->assertSent(Completions::class, function (string $method, array $parameters): bool {
    return $method === 'create' &&
        $parameters['model'] === 'gpt-3.5-turbo-instruct' &&
        $parameters['prompt'] === 'PHP is ';
});
// or
$client->completions()->assertSent(function (string $method, array $parameters): bool {
    // ...
});

// assert 2 completion create requests were sent
$client->assertSent(Completions::class, 2);

// assert no completion create requests were sent
$client->assertNotSent(Completions::class);
// or
$client->completions()->assertNotSent();

// assert no requests were sent
$client->assertNothingSent();
```

To write tests expecting the API request to fail you can provide a `Throwable` object as the response.

```php
$client = new ClientFake([
    new \OpenAI\Exceptions\ErrorException([
        'message' => 'The model `gpt-1` does not exist',
        'type' => 'invalid_request_error',
        'code' => null,
    ], 404)
]);

// the `ErrorException` will be thrown
$completion = $client->completions()->create([
    'model' => 'gpt-3.5-turbo-instruct',
    'prompt' => 'PHP is ',
]);
```

## Services

### Azure

In order to use the Azure OpenAI Service, it is necessary to construct the client manually using the factory.

```php
$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

To use Azure, you must deploy a model, identified by the {deployment-id}, which is already incorporated into the API calls. As a result, you do not have to provide the model during the calls since it is included in the `BaseUri`.

Therefore, a basic sample completion call would be:

```php
$result = $client->completions()->create([
    'prompt' => 'PHP is'
]);
``` 

---

OpenAI PHP is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
