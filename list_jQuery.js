
// 
$(document).ready(function () {
    // 印刷画面チェックボックス変更時
    $('#copy').change(function () {
        if ($(this).prop("checked") === true) {
            $("#copyprint").removeClass("dispNone");
        } else {
            // 画面表示
            $("#copyprint").addClass("dispNone");
        }
    });
    // 納品書チェックボックス変更時
    $('#delively').change(function () {
        if ($(this).prop("checked") === true) {
            $("#delivelyprint").removeClass("dispNone");

        } else {
            $("#delivelyprint").addClass("dispNone");
        }
    });

    // プラスマイナスアイコンクリック時
    $('.icon').click(function () {

        // メニュー表示/非表示
        $(this).next('div').animate({
            width: 'toggle'
        }, 'fast');

        // プラスマイナスアイコン動作
        if ($(this).hasClass('icon--plus')) {
            $(this).removeClass('icon--plus');
        } else {
            $(this).addClass("icon--plus");
        }
    });
    
    // 上部ボタンクリック時
    $('.btn-radius').click(function () {
        var copySearch = $(this).attr('href');
        if(copySearch.indexOf( 'SEIKYUCOPY' ) !== -1 )
        {
            copySaveSummary();
        }
        if(copySearch.indexOf( 'MITSUMORICOPY' ) !== -1)
        {
            copySaveSummary();
        }
        
        return;
    });

});
/**
 * クラスチェンジ
 * id {string} 対象id
 * delClass {string} 削除クラス
 * adClass {string} 追加クラス
 */
function classChange(id, delClass, adClass) {
    $(id).removeClass(delClass);
    $(id).addClass(adClass);
}

//CSV登録ボタン押下時
function csvCheck(){
    var csv = $('#csvSelect').val();
    if(csv === '')
    {
        alert('ファイルが選択されていません。');
    }
    else
    {
        $('<input>').attr({
            type: 'hidden',
            name: 'Comp'
        }).appendTo('.center');
        document.fileinsert.submit();
    }
}

// 印刷画面遷移
function submitaction() {
    // $('form').attr('action', 'main.php?MITSUMORIPRINT_5_button=');
    $('<input>').attr({
        type: 'hidden',
        name: 'print'
    }).appendTo('.pad');
    
    let printcheck = $('#print_btn').attr('data-action');
    if(printcheck =="1")
    {
        var con = confirm("印刷内容は保存されませんが、印刷しますか？");
        if(con == false)
        {
            $.removeCookie("back");
            return false;
        }
    }
    else if(printcheck == "2")
    {
        $('<input>').attr({
            type: 'hidden',
            name: 'updateprint',
            value: '1'
        }).appendTo('.pad');
    } 
    
    $('form').submit();
}

function updateAutocomplete(ctrlname, identifier)
{
    var ctrlname_show = ctrlname + "_show";
    var jqxhr;
    $(ctrlname_show).autocomplete(
            {
                source: function (request, response) {
                    $.ajax(
                            {
                                url: 'json.php',
                                scriptCharset: 'utf-8',
                                type: 'GET',
                                data: {
                                    key: '',
                                    search: $(ctrlname_show).val(),
                                    table_id: identifier
                                },
                                dataType: 'json',
                                timeout: 5000,
                                cache: false,
                                success: function (data)
                                {
                                    var dataArray = data.results;
                                    var arrayData = [];
                                    var counter = 0;
                                    $.each(dataArray, function (i)
                                    {
                                        var hashData = {};
                                        hashData['label'] = dataArray[i].LABEL;
                                        hashData['value'] = dataArray[i].VALUE;
                                        hashData['code'] = dataArray[i].KEY;
                                        arrayData[counter] = hashData;
                                        counter++;
                                    });
                                    response(arrayData);
                                }

                            });
                },
                autoFocus: true,
                delay: 100,
                minLength: 0,
                select: function (e, ui)
                {
                    if (ui.item)
                    {
                        var _CODE = ui.item.code;
                        $(ctrlname).val(_CODE);
                    }
                },
                change: function (event, ui) {
                    if (!ui.item) {
                        $(ctrlname).val('');
                    }
                }
            }).focus(function () {
        $(this).autocomplete('search', '');
    });

    // ajax処理
    $(ctrlname).autocomplete(
            {

                source: function (request, response) {

                    jqxhr = $.ajax(
                            {
                                url: 'json.php',
                                scriptCharset: 'utf-8',
                                type: 'GET',
                                data: {
                                    key: '',
                                    search: $(ctrlname).val(),
                                    table_id: identifier
                                },
                                dataType: 'json',
                                timeout: 5000,
                                cache: false,

                                success: function (data)
                                {
                                    var dataArray = data.results;
                                    var arrayData = [];
                                    var counter = 0;
                                    $.each(dataArray, function (i)
                                    {
                                        var hashData = {};
                                        hashData['label'] = dataArray[i].LABEL;
                                        hashData['value'] = dataArray[i].VALUE;
                                        hashData['code'] = dataArray[i].KEY;
                                        arrayData[counter] = hashData;
                                        counter++;
                                    });
                                    response(arrayData);
                                }
                            });
                },
                autoFocus: true,
                delay: 100,
                minLength: 0

            }).focus(function () {
        $(this).autocomplete('search', '');
        // 入力項目がreadonlyの場合ajax中断
        var className = $(ctrlname).attr("class");
        if (className.indexOf('readOnly') > 0)
        {
            jqxhr.abort();
        }
    });
}

function updateShowValue(ctrlname, identifier)
{
    var ctrlname_show = ctrlname + "_show";
    // 2020/08/07追加
    var val = "";
    if(ctrlname.indexOf("ANKID") > -1){
        val = $(ctrlname).val();
    } else{
        if(sessionStorage.getItem('TANTOU') === null) {
            val = $(ctrlname).val();
        } else {
            val = sessionStorage.getItem('TANTOU');
        }
    }
    
    $.ajax(
            {
                url: 'json.php',
                scriptCharset: 'utf-8',
                type: 'GET',
                data: {
                    key: val,
                    search: '',
                    table_id: identifier
                },
                dataType: 'json',
                timeout: 5000,
                cache: false,

                success: function (data)
                {
                    var dataArray = data.results;

                    $.each(dataArray, function (i)
                    {
                        $(ctrlname_show).val(dataArray[i].VALUE);
                    });
                }

            });
}

function updateAutocompleteByID(ctrl_auto, ctrl_id, identifier)
{
    $(ctrl_auto).autocomplete(
            {
                source: function (request, response) {
                    $.ajax(
                            {
                                url: 'json.php',
                                scriptCharset: 'utf-8',
                                type: 'GET',
                                data: {
                                    key: $(ctrl_id).val(),
                                    search: '',
                                    table_id: identifier
                                },
                                dataType: 'json',
                                timeout: 5000,
                                cache: false,
                                success: function (data)
                                {
                                    var dataArray = data.results;
                                    var arrayData = [];
                                    var counter = 0;
                                    $.each(dataArray, function (i)
                                    {
                                        var hashData = {};
                                        hashData['label'] = dataArray[i].LABEL;
                                        hashData['value'] = dataArray[i].VALUE;
                                        hashData['code'] = dataArray[i].KEY;
                                        arrayData[counter] = hashData;
                                        counter++;
                                    });
                                    response(arrayData);
                                }

                            });
                },
                autoFocus: true,
                delay: 100,
                minLength: 0,
                select: function (e, ui)
                {
                }
            }).focus(function () {
        $(this).autocomplete('search', '');
    });
}

/*
 * 検索条件クリア
 */
function clearSearch(filename)
{
    var i;
    var len = 0;
    
    var searchForms = $('#clear_'+ filename).val();
    var searchForm = searchForms.split(",");
    
    len = searchForm.length;
    
    for(i=0; i<len; i++)
    {
        $('#form_'+ searchForm[i] +'_0').val("");
    }    
}
