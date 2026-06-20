import ErrorPage from './ErrorPage';

/** 400 Bad Request。 */
function BadRequestPage() {
  return (
    <ErrorPage
      code="400"
      title="リクエストに問題があります。"
      message={
        <>
          <p>送信された内容を処理できませんでした。</p>
          <p>お手数ですが、入力内容をご確認のうえ再度お試しください。</p>
        </>
      }
    />
  );
}

export default BadRequestPage;
