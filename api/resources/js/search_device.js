$(function() {// 検索ボタン押下
    $('#search-client').on('click', function() {
        $('#result').text('検索中...');
        $("#search_table").empty();
        let search_word = document.getElementById('word').value;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "device/search",
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
        let split_list = id_company.split('@');
        $('#search_result').text(split_list[1]);
        $('#client').val(split_list[0]);
        console.log(split_list[0]);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "client/search/contact",
            dataType:'json',
            type: 'POST',
            data: JSON.stringify({'contact_id': split_list[0]}),
            contentType: "application/json",
            timeout: 5000
        })
            // Ajaxリクエストが成功した場合
            .done(function(datas) {
                // console.log('成功！')
                if(datas.success == 0){
                    $('#select_contact').text('登録されている担当者がいません');
                    // $("#contact").empty()
                    if(document.getElementById("contact_selector")){
                        $('#contact_selector').remove()
                    }
                    if(document.getElementById("contact_btn")){
                        $('#contact_btn').remove()
                    }
                    $('#select_contact').after('<button id="contact_btn" type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#EditModal">担当者を登録する</button>')
                }else{
                    let count = 0;
                    if(document.getElementById("contact_selector")){
                        $('#contact_selector').remove()
                    }
                    if(document.getElementById("contact_btn")){
                        $('#contact_btn').remove()
                    }
                    $('#select_contact').after("<select id='contact_selector' name='contact' class='form-control'></select>")
                    for(data in datas.data){
                        count+=1;
                        console.log(datas.data[data].contact_id);
                        $("#contact_selector").append(
                            $("<option></option>")
                                .val(datas.data[data].contact_id)
                                .text(datas.data[data].name + ' : ' + datas.data[data].email)
                        );
                    }
                    $('#select_contact').text('登録されている担当者が'+ count +'名います');
                }
            })
            // Ajaxリクエストが失敗した場合
            .fail(function(datas) {
                // alert(data.responseJSON);
                // alert('失敗');
                console.log('失敗!')
            });
        // モーダルclose
        $('#ClientSearchModal').modal('hide');
    });
});

function search_loading(){
    const loading = document.querySelector( '.loading' );

    window.addEventListener( 'load', () => {
    //loading.classList.add( 'hide' );
    }, false );
}
