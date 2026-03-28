<?php

namespace App\Http\Controllers;

// Controller
// Models
use App\Http\Requests\StoreClientRequest;
// Exception
use App\Http\Requests\UpdateClientRequest;
// Request
use App\Models\Client;
use App\Traits\SearchesClients;
use Exception;
// Facades
use GuzzleHttp\Client as GuzzleClient;
// Libraries
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class ClientsController extends Controller
{
    use SearchesClients;
    /**
     * 登録フォームの表示
     *
     * @access public
     * @return \Illuminate\View\View
     */
    public function form()
    {
        return view('client.register');
    }

    /**
     * クライアント企業一覧
     * @access public
     */
    public function getAllClient(Request $request)
    {
        try {
            $word = $request->input('word');
            $query = Client::query();
            if ($word) {
                $escaped = addcslashes($word, '%_\\');
                $query->where('company', 'like', '%' . $escaped . '%');
            }
            $clients = $query->paginate(10)->appends(['word' => $word]);
            return view('client.index', compact('clients', 'word'));
        } catch (Exception $err) {
            return redirect()->back()->with('error_message', __('messages.data_fetch_failed'));
        }
    }

    /**
     * クライアント企業を登録する
     * @access public
     * @param $request
     */
    public function register(StoreClientRequest $request)
    {
        $client_id = (string) Uuid::uuid7();
        try {
            Client::create([
                'client_id'         => $client_id,
                'company'           => $request->company,
                'url'               => $request->url,
                'tel'               => $request->tel,
                'street_address'    => $request->street_address,
                'note'              => $request->note,
            ]);
        } catch (Exception $err) {
            Log::channel('error')->error('client.register.failed', [
                'action' => 'client_registration',
                'client_id' => $client_id,
                'company' => $request->company,
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()->back()->with('error_message', __('messages.registration_error'));
        }
        return redirect()->back()->with('success_message', __('messages.registration_completed'));
    }


    /**
     * 企業情報を編集する
     * @access public
     * @param $request
     */
    public function edit(UpdateClientRequest $request)
    {
        try {
            $client = Client::where('client_id', $request->client_id)->first();
            $client->company = $request->company;
            $client->url = $request->url;
            $client->tel = $request->tel;
            $client->street_address = $request->street_address;
            $client->note = $request->note;
            $client->save();
        } catch (Exception $err) {
            Log::channel('error')->error('client.edit.failed', [
                'action' => 'client_update',
                'client_id' => $request->client_id,
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error_message', __('messages.registration_failed'));
        }
        return redirect()->back()->with('success_message', __('messages.registration_completed'));
    }

    /**
     * クライアント企業詳細&担当者取得　表示
     *
     * @access public
     * @param $client_id クライアントID
     */
    public function clientDetails(Request $request)
    {
        try {
            $c = new Client();
            $client = $c->find($request->client_id);
            if (!$client) {
                abort(404, __('messages.client_not_found'));
            }
        } catch (Exception $err) {
            Log::channel('error')->error('client.details.failed', [
                'action' => 'client_details_fetch',
                'client_id' => $request->client_id,
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()->back()->with('error_message', __('messages.data_fetch_failed'));
        }
        return view('client.client_detail', compact('client'));
    }

    /**
     * 外部APIからクライアント情報を取得し更新(臨時)
     * @access public
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function syncFromCRM()
    {
        try {
            $crm = config('services.crm');
            $guzzle = new GuzzleClient();
            // APIからデータ取得
            $client_data = $guzzle->request('GET', $crm['url']);
            $clients = json_decode($client_data->getBody()->getContents(), true);
            // ヘッダー情報を削除
            $clients = array_splice($clients, 1);
            // データの更新
            DB::beginTransaction();
            foreach ($clients as $client) {
                $c = new Client();
                $c->updateOrCreate(
                    ['client_id' => $client['id']],
                    [
                        'company'           => $client['client'],
                        'url'               => $client['url'],
                        'tel'               => $client['tel'],
                        'post_code'         => $client['post_code'],
                        'street_address'    => $client['address'],
                        'note'              => $client['description'],
                    ]
                );
            }

            // クライアント先担当者情報取得と更新
            $p = new ContactsController();
            $p->synPersonnel($crm['url'] . '?sheet_name=contacts');

            DB::commit();
            return response()
                ->json([
                    'success_message' => __('messages.data_fetch_succeeded'),
                    'data' => $clients,
                ]);
        } catch (Exception $err) {
            DB::rollBack();
            Log::channel('operation')->error('client.crm_sync.failed', [
                'action' => 'crm_sync',
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return response()
                ->json(['error_message' => __('messages.data_fetch_failed')]);
        }
    }
}
