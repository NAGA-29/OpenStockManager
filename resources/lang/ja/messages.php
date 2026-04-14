<?php

return [

    /*
    |--------------------------------------------------------------------------
    | アプリケーションメッセージ
    |--------------------------------------------------------------------------
    */

    // 共通メッセージ
    'registration_completed' => '登録が完了しました',
    'registration_failed' => '登録が失敗しました',
    'registration_error' => '登録に失敗しました',
    'save_completed' => '保存が完了しました',
    'edit_failed' => '編集に失敗しました',
    'data_fetch_failed' => 'データの取得に失敗しました',
    'data_fetch_succeeded' => 'データの取得に成功しました',
    'upload_completed' => 'アップロードが完了しました',
    'search_failed' => '検索に失敗しました',
    'file_not_selected' => 'ファイルが選択されていません',
    'file_not_found' => 'ファイルが見つかりません',
    'session_expired' => 'セッションが切れました',

    // デバイス関連
    'device_not_found' => '指定されたデバイスが見つかりません',
    'device_registration_failed' => '新規機材登録が失敗しました',
    'device_multi_registration_failed' => '複数台機材登録が失敗しました',
    'image_analysis_failed' => '画像の分析に失敗しました',
    'image_save_failed' => '画像の保存に失敗しました',
    'image_delete_failed' => '画像の削除に失敗しました',

    // レンタル関連
    'rental_registration_failed' => 'レンタル情報の登録に失敗しました',
    'rental_process_failed' => 'レンタル処理が失敗しました',
    'rental_history_not_found' => '指定されたレンタル履歴が見つかりません',
    'csv_parse_failed' => '解析が失敗しました',
    'device_not_exists' => '存在しない端末が記載されています Device ID : :device_id',
    'device_already_sold' => '既に販売されている端末が含まれています Device ID : :device_id',
    'device_currently_rented' => 'レンタル中の端末が含まれています Device ID : :device_id',
    'device_defective' => '不具合が報告されている端末が含まれています Device ID : :device_id',

    // 販売関連
    'sales_process_failed' => '販売処理が失敗しました',
    'sales_csv_failed' => '販売CSV処理が失敗しました',
    'sales_history_not_found' => '指定された販売履歴が見つかりません',
    'cart_device_registration_failed' => 'カゴ内の端末登録に失敗しました',
    'device_duplicate' => '重複があります Device ID : :device_id',

    // クライアント関連
    'client_not_found' => '指定されたクライアントが見つかりません',
    'client_registration_failed' => 'クライアントの登録に失敗しました',
    'client_data_fetch_failed' => 'クライアント情報のデータ取得に失敗しました',
    'crm_sync_failed' => '顧客データの取得または更新に失敗しました',

    // ユーザー関連
    'user_registered' => 'ユーザーを登録しました。',
    'user_registration_failed' => 'ユーザーの登録に失敗しました。',
    'user_updated' => 'ユーザー情報を更新しました。',
    'user_update_failed' => 'ユーザー情報の更新に失敗しました。',
    'user_registration_error' => 'ユーザー登録エラー',
    'email_not_available' => '入力したメールアドレスを使用することはできません。',
    'email_verification_sent' => '確認メールを送信しました',
    'user_not_found' => 'ユーザーが存在しません。',
    'email_changed' => 'メールアドレスを変更しました。',
    'email_change_expired' => 'メールアドレス変更の有効期限が切れています。',
    'email_verification_error' => 'メール変更認証エラー',
    'email_verification_failed' => '認証に失敗しました。',

    // メール関連
    'return_deadline_subject' => '[OpenStockManager]返却期限通知',
    'return_deadline_mail_success' => '返却期限通知メール送信成功',
    'return_deadline_mail_failed' => '返却期限通知メール送信失敗',
    'return_deadline_mail_exception' => '返却期限通知メール送信例外',

    // 担当者関連
    'contact_registration_failed' => '登録に失敗しました',
    'contact_data_fetch_failed' => 'データの取得に失敗しました',

    // ログメッセージ
    'log_search_failed' => '検索が失敗しました',
    'log_edit_failed' => '編集が失敗しました',
];
