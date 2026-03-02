<?php

namespace App\Http\Controllers;

// Request
use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Models\Client;
// Models
use App\Models\Personnel;
use App\Traits\SearchesClients;
// Facades
use Exception;
// Exception
use GuzzleHttp\Client as GuzzleClient;
// Traits
use Illuminate\Http\Request;
// Enum
// Library
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class PersonnelsController extends Controller
{
    use SearchesClients;

    public function getAllPersonnel()
    {
        try {
            $personnel = Personnel::orderBy('name', 'asc')->paginate(10);
            return view('personnel.all_personnel', compact('personnel'));
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.personnel_data_fetch_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()->back()->with('error_message', __('messages.data_fetch_failed'));
        }
    }

    /**
     * 登録フォームを表示
     * @access public
     * @return \Illuminate\View\View
     */
    public function form()
    {
        // Clientテーブル全データ取得
        $clients = Client::all();
        return view('personnel.register', compact('clients'));
    }

    /**
     * 担当者の登録
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(StorePersonnelRequest $request)
    {
        $personnel_id = (string) Uuid::uuid7();
        try {
            Personnel::create([
                'personnel_id'  => $personnel_id,
                'client_id'     => $request->client_id,
                'name'          => $request->name,
                'email'         => $request->email,
                'tel'           => $request->tel,
                'note'          => $request->note,
            ]);
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.personnel_registration_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_error'));
        }
        return redirect()
            ->back()
            ->with('success_message', __('messages.registration_completed'));
    }

    /**
     * 担当者情報表示
     * @access public
     * @param Request $request
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function personnelDetail($personnel_id)
    {
        try {
            $psn = new Personnel();
            $personnel = $psn->find($personnel_id);
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.personnel_data_fetch_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()->back();
        }
        return view('personnel.detail', compact('personnel'));
    }


    /**
     * 担当者情報の編集
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit(UpdatePersonnelRequest $request)
    {
        try {
            $validated = $request->validated();

            $psn = new Personnel();
            $psn->where('personnel_id', $validated['personnel_id'])->update([
                'name' => $validated['name'],
                'tel' => $validated['tel'],
                'email' => $validated['email'],
                'note' => $validated['note'],
            ]);
            return redirect()
                ->back()
                ->with('success_message', __('messages.registration_completed'));
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.personnel_registration_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_error'));
        }
    }

    /**
     * 外部APIからクライアント先担当者情報を取得し更新(臨時)
     * @access public
     * @param string $url
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function synPersonnel(string $url)
    {
        try {
            $guzzle = new GuzzleClient();
            // APIからデータ取得
            $person_data = $guzzle->request('GET', $url);
            $persons = json_decode($person_data->getBody()->getContents(), true);
            // ヘッダー情報を削除
            $persons = array_splice($persons, 1);
            // データの更新
            foreach ($persons as $person) {
                $p = new Personnel();
                $p->updateOrCreate(
                    ['personnel_id' => $person['id']],
                    [
                        'client_id' => $person['client_id'],
                        'name'      => $person['name'],
                        'tel'       => $person['tel'],
                        'email'     => $person['email'],
                        'note'      => $person['description'],
                    ]
                );
            }

            return response()
                ->json([
                    'success_message' => __('messages.data_fetch_succeeded'),
                    'data' => $persons,
                ]);
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.crm_sync_failed'),
                ['error_message' => $err->getMessage()]
            );
            throw new Exception(__('messages.crm_sync_failed'));
        }
    }
}
