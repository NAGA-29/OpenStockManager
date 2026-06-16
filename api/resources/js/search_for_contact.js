$(function() {// 検索ボタン押下
    $('#search_client').on('click', function() {
        $('#result').text('検索中...');
        $("#search_table").empty();
        let search_word = document.getElementById('word').value;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "client/search",
            dataType:'json',
            type: 'POST',
            data: JSON.stringify({'search_word': search_word}),
            contentType: "application/json",
            timeout: 5000
        })
        // Ajaxリクエストが成功した場合
        .done(function(datas) {

            if(datas.success == 0){
                $('#result').text('該当する企業はありませんでした。');
            }else{
                let count = 0;
                for(data in datas.data){
                    count+=1;
                    let input_selector = "<input type='radio' name='client_id' value="
                    input_selector += "'" + datas.data[data].client_id +"@"+datas.data[data].company + "' id='" + datas.data[data].client_id + "'/>";
                    console.log(input_selector);

                    $("#search_table").append(
                        $("<tr></tr>")
                            .append($("<td></td>").append(input_selector))
                            .append($("<td></td>").append('<label for="' + datas.data[data].client_id + '">' + datas.data[data].company + '</label>'))
                            .append($("<td></td>").append('<label for="' + datas.data[data].client_id + '">' + datas.data[data].url + '</label>'))
                            .append($("<td></td>").append('<label for="' + datas.data[data].client_id + '">' + datas.data[data].tel + '</label>'))
                            .append($("<td></td>").append('<label for="' + datas.data[data].client_id + '">' + datas.data[data].street_address + '</label>'))
                            .append($("<td></td>").append('<label for="' + datas.data[data].client_id + '">' + datas.data[data].note + '</label>'))
                    );
                }
                $('#result').text(count + '件見つかりました。');
            }
        })
        // Ajaxリクエストが失敗した場合
        .fail(function(datas) {
            // alert(data.responseJSON);
            alert('失敗');
        });
    });

    $('#client_select_btn').on('click', function() {// 選択ボタン押下
        let id_company = $('input:radio[name="client_id"]:checked').val();
        console.log(id_company);
        let split_list = id_company.split('@');
        $('#search_result').text(split_list[1]);
        $('#client_id').val(split_list[0]);
        console.log(split_list[0]);

        // モーダルclose
        $('#ClientSearchModal').modal('hide');
    });
});
