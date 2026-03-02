<?php

namespace App\Http\Controllers;

// Requests
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
// Facades
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Models
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// Libraries
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * 管理者一覧ページ
     * @access public
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userList(Request $request)
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

    /**
     * 管理者編集ページ
     * @access public
     * @param  \Illuminate\Http\Request  $request
     */
    public function register(Request $request)
    {
        return view('user.register');
    }

    /**
     * 管理者登録
     * @access public
     * @param  StoreUserRequest $request
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            $users = Auth::user();
            return redirect()
                ->route('user.list', compact('users'))
                ->with('success_message', __('messages.user_registered'));
        } catch (\Exception $e) {
            Log::channel('error')->error('user.store.failed', [
                'action' => 'user_registration',
                'target_email' => $request->email,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
            return redirect()
                ->back()
                ->with('error_message', __('messages.user_registration_failed'));
        }
    }

    /**
     * ユーザー情報の更新
     * @access public
     * @param  UpdateUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUserRequest $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return redirect()
                ->route('user.list')
                ->with('success_message', __('messages.user_updated'));
        } catch (\Exception $e) {
            Log::channel('error')->error('user.update.failed', [
                'action' => 'user_update',
                'target_user_id' => $request->id,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
            return redirect()
                ->back()
                ->with('error_message', __('messages.user_update_failed'));
        }
    }

    /**
     * メールアドレスの変更
     * 確認メールを送信
     * @access public
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeEmail(ChangeEmailRequest $request)
    {
        $user = Auth::user();

        // DBに一時保存
        $new_email = $request->email;
        $token = Str::random(64);
        DB::table('email_resets')->insert([
            'user_id'       => $user->id,
            'new_email'     => $new_email,
            'token'         => $token,
            'created_at'    => Carbon::now(),
        ]);
        $user->sendEmailChangeNotification($token);

        return redirect()
            ->back()
            ->with('success_message', __('messages.email_verification_sent'));
    }

    /**
     * メールアドレスの変更
     * 確認メールのリンクからアクセス(認証)
     * FIXME: バッチで定期的にトークンの有効期限をチェックして、期限切れの場合はDBから削除する
     * @access public
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyNewEmail(Request $request)
    {
        $token = $request->input('token');
        try {
            // トランザクション開始
            DB::beginTransaction();
            $emailReset = DB::table('email_resets') // トークンとcreated_atの時間が30分以内かチェック
                ->where('token', $token)
                ->where('created_at', '>=', Carbon::now()->subMinutes(30))
                ->first();

            if ($emailReset) {
                $user = User::find($emailReset->user_id);
                if (!$user) {
                    throw new \Exception(__('messages.user_not_found'));
                }
                $user->email = $emailReset->new_email;
                $user->save();

                DB::table('email_resets')->where('token', $token)
                    ->orWhere('user_id', $emailReset->user_id)
                    ->delete();

                DB::commit(); // トランザクションをコミット
                return redirect()
                    ->route('profile')
                    ->with('success_message', __('messages.email_changed'));
            } else {
                DB::table('email_resets')->where('token', $token)
                    ->delete();

                DB::commit(); // トランザクションをコミット
                return redirect()
                    ->route('profile')
                    ->with('error_message', __('messages.email_change_expired'));
            }
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('user.verify_email.failed', [
                'action' => 'email_verification',
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()
                ->route('project.index')
                ->with('error_message', __('messages.email_verification_failed'));
        }
    }
}
