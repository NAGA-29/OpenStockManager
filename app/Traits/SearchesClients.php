<?php

namespace App\Traits;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait SearchesClients
{
    /**
     * クライアント企業の検索
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchClient(Request $request)
    {
        $data_list = [];
        $data = [];
        $posts = null;
        $search_word = $request->search_word;

        // 検索
        if ($search_word != '') {
            $escapedWord = addcslashes($search_word, '%_\\');
            $posts = Client::where('company', 'like', '%' . $escapedWord . '%')
                ->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $posts = Client::orderBy('created_at', 'desc')->paginate(10);
        }

        // 結果判定
        if (count($posts) == 0) {
            $data_list['success'] = 0;
        } else {
            $data_list['success'] = 1;
            foreach ($posts as $post) {
                array_push($data, $post);
            }
            $data_list['data'] = $data;
        }

        Log::debug(json_encode(['success' => true, 'data' => $request->search_word]));
        return response()->json($data_list);
    }
}
