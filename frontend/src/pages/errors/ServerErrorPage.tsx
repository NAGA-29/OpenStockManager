import { useRouteError } from 'react-router-dom';
import ErrorPage from './ErrorPage';

/**
 * 500 Internal Server Error。
 * Router の `errorElement` としても使うため、`useRouteError()` の内容を補足表示する。
 */
function ServerErrorPage() {
  const error = useRouteError() as unknown;
  const now = new Date().toLocaleString('ja-JP');

  const detailText =
    error instanceof Error
      ? error.message
      : typeof error === 'string'
        ? error
        : undefined;

  return (
    <ErrorPage
      code="500"
      title="システムに問題が発生しました。"
      message={
        <>
          <p>
            お手数ですが、しばらく時間を置いてからやり直すか、
            <br />
            下記の情報と併せて管理者にお問い合わせください。
          </p>
        </>
      }
      detail={
        <>
          Error: 500
          <br />
          {now}
          {detailText ? (
            <>
              <br />
              {detailText}
            </>
          ) : null}
        </>
      }
    />
  );
}

export default ServerErrorPage;
