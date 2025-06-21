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

            'contents'                    => 'required|array|min:1',
            'contents.*.question_text_en' => 'required|string',
            'contents.*.question_text_hi' => 'required|string',
            'contents.*.options'          => 'required|array|min:2',
            'contents.*.options.*.id'     => 'required|integer',
            'contents.*.options.*.option' => 'required|string',
            'contents.*.correctAnswerId'  => 'required|integer',
            'contents.*.explanation'      => 'nullable|string',
            'contents.*.difficulty'       => 'required|in:easy,medium,hard'
        ]);
        if($validator->fails()){
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }
        $data = $validator->validated();
        $nid = $data['node_id'];
        $contents = $data['contents'];
        DB::beginTransaction();
        try{
            $topic = Topic::where('nid', $data['node_id'])->first();
            $questions = [];
            foreach($contents as $key => $content){
                $question = Question::create([
                    'topic_id' => $topic->id,
                    'nid' => $nid,
                    'qid' => uniqid('q_'),
                    'question_text_en' => $content['question_text_en'],
                    'question_text_hi' => $content['question_text_hi'],
                    'explanation' => $content['explanation'],
                    'difficulty' => $content['difficulty']
                ]);
    
                foreach ($content['options'] as $option) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $option['option'],
                        'is_correct'  => $option['id'] == $content['correctAnswerId'],
                    ]);
                }
            }
            DB::commit();
            return $this->successResponse($contents, "Test has been created successfully!", 200);
        } catch(\Exception $e){
            DB::rollBack();
            return $this->exceptionHandler($e, $e->getMessage(), 500);
        }
    
    }

    public function createTopic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nid' => 'required|integer',
            'title' => 'required|string|max:225',
            'alias' => 'required|string|max:50|alpha_dash',
            'description' => 'nullable',
            'status' => 'required|string|in:draft,published,archived'
        ]);
        if($validator->fails()){
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }
        $data = $validator->validated();
        $sku = generateSku($data['alias'], 'topic', \App\Models\Topic::class);
        $data['sku'] = $sku;
        $data['slug'] = Str::slug($data['title']);

        $topic = Topic::create($data);

        return $this->successResponse($topic, "Topic has been created successfully!", 200);
    }

    public function getMockByNid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nid' => 'required|exists:topics,nid'
        ]);
        if($validator->fails()){
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }
        $data = $validator->validated();
        $topic = Topic::with('questions.options')->where('nid', $data['nid'])->get();

        return $this->successResponse($topic, "Topic has been fetched successfully!", 200);
    }

    
}
