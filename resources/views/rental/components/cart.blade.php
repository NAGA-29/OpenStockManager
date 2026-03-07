    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="m-4">
                @include('component.cart_list')
            </div>
            <div class="card-header bg-white text-black">{{ __('下記の記入欄を入力してください') }}</div>
            <div class="card-body m-5">
                <p>[ <span class="text-danger">*</span> ] は入力必須</p>

                <form action="{{ Route('device.rental.store') }}" method='POST'>
                    @csrf
                    <div id="device-list"></div>
                    <table class="table table-hover">
                        <tr>
                            <td>{{ __('貸出先企業') }} <span class="text-danger">*</span></td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <p class="search_result">選択されていません</p>
                                        <input id="client" class="client" type="hidden"
                                            class="form-control @error('client_id') is-invalid @enderror"
                                            name="client_id" required autofocus>
                                    </div>
                                </div>
                                {{-- 検索モーダル --}}
                                <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal"
                                    data-bs-target="#ClientSearchModal">
                                    検索
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('貸出先担当者') }} <span class="text-danger">*</span></td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <p class="select_personnel" name='select_personnel'>貸出先企業を先に選択してください</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('貸出日') }} <span class="text-danger">*</span></td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <input id="checkout_at" type="date"
                                            class="form-control @error('checkout_at') is-invalid @enderror"
                                            name="checkout_at" value="{{ old('checkout_at') }}" required>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('返却予定日') }}</td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <input id="schedule_return_at" type="date"
                                            class="form-control @error('schedule_return_at') is-invalid @enderror"
                                            name="schedule_return_at" value="{{ old('schedule_return_at') }}">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('レンタルノート') }}</td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <textarea id="note" type="textarea" rows="5" class="form-control @error('note') is-invalid @enderror"
                                            name="note">{{ old('note') }}</textarea>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class='text-center m-3'>
                        <button type="submit" class='btn btn-outline-dark mr-3'>登録</button>
                        <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // deviceIdにカートに入っているデバイスのIDを代入
        const sample = sessionStorage.getItem("OpenStockManagerCart");
        const List = JSON.parse(sample || "{}");
        const deviceInput = document.getElementById('device-list');
        Object.entries(List).forEach(([id, item]) => {
            deviceInput.innerHTML += `<input type="hidden" name="deviceIds[]" value="${item.id}">`;
        });
    </script>
