import ErrorPage from './ErrorPage';

/** 503 Service Unavailable。 */
function ServiceUnavailablePage() {
  return (
    <ErrorPage
      code="503"
      title="システムに問題が発生しました。"
      message={
        <>
          <p>アクセスが集中しているため、しばらく時間を置いてからやり直してください。</p>
        </>
      }
    />
  );
}

export default ServiceUnavailablePage;
