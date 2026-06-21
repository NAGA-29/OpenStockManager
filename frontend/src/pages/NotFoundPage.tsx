import ErrorPage from './errors/ErrorPage';

/** 404 Not Found（不明なルート）。 */
function NotFoundPage() {
  return (
    <ErrorPage
      code="404"
      title="お探しのページが見つかりません。"
      message={
        <>
          <p>お探しのページは削除されたか、URLが変更された可能性があります。</p>
          <p>お手数ですが、トップページから再度お探しください。</p>
        </>
      }
    />
  );
}

export default NotFoundPage;
