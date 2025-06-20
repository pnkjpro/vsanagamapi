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
        $validator = Validator::make($request->all(), []);
        if($validator->fails()){
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }
        $data = $validator->validated();

        return $this->successResponse($response, "Test has been created successfully!", 200);
    }
}
