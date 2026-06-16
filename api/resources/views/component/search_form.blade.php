<div class="col-lg-6 col-md-6 device-toolbar-right">
    <form action="{{ url('/device/search/') }}" method="GET" class="col-xl-12 device-toolbar-form">
      <div class="input-group">
          @csrf
          <input type="text" name="word" class="form-control" placeholder="端末ID or シリアル or メモ" value="{{ $searchKeyword ?? ''}}">
          <input type="hidden" name="hiddenType" value="{{ $hiddenType ?? ''}}">
          <button type="submit" id="search-device" class="btn btn-outline-dark">
              <i class="fas fa-search"></i> 検索
          </button>
      </div>
  </form>
</div>
