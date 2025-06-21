<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Controllers\OtpController;

use App\Traits\JsonResponseTrait;



class UserController extends Controller
{
    use JsonResponseTrait;
    public $OtpController;

    public function __construct()
    {
        $this->OtpController = new OtpController();
    }
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|numeric|unique:users,mobile',
            'password' => 'required|min:6|confirmed',
            'refer_code' => 'nullable|max:8|exists:users,refer_code'
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return $this->errorResponse([], $validator->errors(), 422);
        }

        $referBy = null;
        if(isset($request->refer_code)){
            $referBy = User::where('refer_code',$request->refer_code)->first();
            $referById = User::where('refer_code',$request->refer_code)->first()->id;
        }

        $avatars = Config::get('vsangam.constant.avatars');
        $randomAvatar = '/avatars/' . Arr::random($avatars);

        $isOtpSent = $this->OtpController->sendOtp(new Request([
            'email' => $request->email,
            'label' => 'verify_email'
        ]));

        if($isOtpSent->original['success']){
            $user = User::create([
                'name' => $request->name,
                'avatar' => $randomAvatar,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
                'refer_code' => $this->generateReferralCode(),
                'refer_by' => isset($referById) ? $referById : null,
            ]);
        } else {
            return $this->errorResponse([], $isOtpSent->original['message'], 500);
        }

        return $this->successResponse($user, "user created successfully!", 201);
    }

    public function generateReferralCode()
    {
        $code = strtoupper(Str::random(8));
        while (User::where('refer_by', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }  
        return $code;
    }

    /**
     * Login User using Email or Mobile.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required', // Can be email or mobile
            'password' => 'required'
        ]);
        
        $user = User::where('email', $request->login)
                    ->orWhere('mobile', $request->login)
                    ->first();
        if(empty($user)){
            return $this->errorResponse([], "User not found", 403);
        }
        
        if (empty($user) || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse([], "Invalid Credential", 422);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], "user logged in successfully!", 200);
    }

    public function listReferredUsers(Request $request){
        $user = Auth::user();

        $referAmountEarned = Transaction::where('user_id', $user->id)
                                        ->where('action', 'referred_reward')
                                        ->sum('amount');
        $baseQuery = User::where('refer_by', $user->id);

        // Clone base query to avoid mutation
        $totalCount = (clone $baseQuery)->count();
        $claimedRewards = (clone $baseQuery)->where('is_reward_given', 1)->count();
        $pendingRewards = (clone $baseQuery)->where('is_reward_given', 0)->count();
        $referredUsers = (clone $baseQuery)->orderByDesc('id')->get();

        return $this->successResponse([
            'referred_users' => $referredUsers,
            'claimed_rewards' => $claimedRewards,
            'pending_rewards' => $pendingRewards,
            'refer_earned' => $referAmountEarned,
            'total_referred' => $totalCount
        ], "Referred Users Found Successfully", 200);
    }

    public function userList(Request $request){
        $page = $request->input('page', 1);
        $limit = Config::get('himpri.constant.adminPaginationLimit'); 
        $offset = ($page - 1) * $limit; 
        $usersQuery = User::with('referredBy:id,refer_code')->orderByDesc('id');
        $totalCount = $usersQuery->count();
        $users = $usersQuery->limit($limit)->offset($offset)->get();
        $users = $users->map(function($user){
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'mobile' => $user->mobile,
                'upi_id' => $user->upi_id,
                'funds' => $user->funds,
                'refer_code' => $user->refer_code,
                'referred_by_code' => $user->referredBy?->refer_code,
            ];
        });
        return $this->successResponse([
            'totalCount' => $totalCount,
            'users' => $users
        ], "Users has been fetched", 200);
    }

    public function fetchUser(Request $request){
        $user = Auth::user()->load([
            'lifelines', 
            'user_responses' => function($query) {
                $query->select(['user_id', 'node_id', 'quiz_variant_id', 'score', 'status']);
            }
        ]);

        // Hide user_id from responses before returning
        $user->user_responses->makeHidden('user_id');
        
        return response()->json([
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse([], $validator->errors()->first(), 422);
        }

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->successResponse([], "Password has been reset", 200);
    }

    public function updatePaymentUpi(Request $request){
        $validator = Validator::make($request->all(), [
            'upi_id' => 'required|string|max:225'
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return $this->errorResponse([], $validator->errors, 422);
        }

        $user = Auth::user();
        $user->update(['upi_id' => $request->upi_id]);

        return $this->successResponse([], "UPI ID has been updated successfully", 200);
    }

    /**
     * Logout User (Invalidate Token)
     */
    public function logout(Request $request)
    {
        // $request->user()->tokens()->delete();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
