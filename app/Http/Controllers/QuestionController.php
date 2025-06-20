<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Question;
use App\Models\Option;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;


use App\Traits\JsonResponseTrait;

class QuestionController extends Controller
{
    use JsonResponseTrait;
    public function createMock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'node_id'                     => 'required|integer|exists:topics,nid',
            'lang'                        => 'required|in:english,hindi',
            'explanation'                 => 'nullable|string',
            'difficulty'                  => 'required|in:easy,medium,hard',

            'contents'                    => 'required|array|min:1',
            'contents.*.question'         => 'required|string',
            'contents.*.options'          => 'required|array|min:2',
            'contents.*.options.*.id'     => 'required|integer',
            'contents.*.options.*.option' => 'required|string',
            'contents.*.correctAnswerId'  => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }
        $data = $validator->validated();

        return $this->successResponse($response, "Test has been created successfully!", 200);
    }

    public function createTopic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:225',
            'alias' => 'required|string|max:50|alpha_dash',
            'description' => 'nullable',
            'status' => 'required|string|in:draft,published,archived'
        ]);
        if($validator->fails()){
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }
        $data = $validator->validated();
        
        $sku = generateSku($data, 'topic', \App\Models\Topic::class);
        $data['sku'] = $sku;
        $data['slug'] = Str::slug($data['title']);

        $topic = Topic::create($data);

        return $this->successResponse($topic, "Topic has been created successfully!", 200);
    }

    
}
