// ver 1.0 2018/11/21

var HINMEI_NAME = '';//品目
var TANKA_NAME = '';//単価
var SURYO_NAME = '';//数量
var TANNI_NAME = '';//単位
var ZEIRISTU_NAME = '';//税率
var KINGAKU_NAME = '';//金額項目
var KINGAKUKEI_NAME = '';//税抜金額合計
var ZEI_NAME = '';//税額
var TOTAL_NAME = '';//税込み金額合計
var FRACTION = '';//税抜き金額端数処理
var ZEIFRACTION = '';//税込み金額端数処理
var ZEI8 ='';//消費税8%
var ZEI10 = '';//消費税10%

//AutoCompleteの制御
function updateAutocompleteHIMValue(ctrlname,identifier,poststr)
{
	$(ctrlname).autocomplete(
	{
		source    : function(request, response){
			$.ajax(
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
                                        hashData['TANNI'] = dataArray[i].TANNI;
                                        hashData['TANKA'] = dataArray[i].TANKA;
                                        hashData['ZEI'] = dataArray[i].ZEIRITSU;

                                        arrayData[counter] = hashData;
                                        counter++;
                                    });
                                    response(arrayData);
                                }
			});
		},
		autoFocus : true,
		delay     : 100,
		minLength : 0,
		
		select : function(e, ui)
		{
                    if (ui.item)
                    {
                        $('#' + TANKA_NAME + poststr).val(ui.item.TANKA);
                        $('#' + TANNI_NAME + poststr).val(ui.item.TANNI);
                        $('#' + ZEIRISTU_NAME + poststr).val(ui.item.ZEI);
                    }
		},
                
                change : function( event, ui )
                {
                    if(!ui.item){
                        $('#'+ZEIRISTU_NAME+poststr).val(10);
                    }    
                }
	}).focus(function() {
		$(this).autocomplete('search', '');
	});
}

//金額の計算
function calculateKingaku( poststr )
{
    //値の取得
    var tanka = $('#' + TANKA_NAME + poststr).val();
    var suryo = $('#' + SURYO_NAME + poststr).val();
    var fraction = $('#' + FRACTION + '_0').val();// 税抜き金額端数処理
    var kingaku;

    if (tanka !== '' && suryo !== '') {
        //計算
        kingaku = tanka * suryo;
        //金額セット
        kingaku = fractionProcess(kingaku, fraction);
        $('#' + KINGAKU_NAME + poststr).val(kingaku);
    }else{
        $('#' + KINGAKU_NAME + poststr).val("");
    }
}

//金額の再計算
function calculateReturn()
{
    var kingaku;
    var fraction = $('#' + FRACTION + '_0').val();// 税抜き金額端数処理
    for (var i = 0; i < 15; i++) {
        //値の取得
        var tanka = $('#' + TANKA_NAME + '_0_' + i).val();
        var suryo = $('#' + SURYO_NAME + '_0_' + i).val();
        
        if (tanka !== '' && suryo !== '') {
            //計算
            kingaku = tanka * suryo;
            //金額セット
            kingaku = fractionProcess(kingaku, fraction);
            $('#' + KINGAKU_NAME + '_0_' + i).val(kingaku);
        }
    }
}

// 合計金額計算処理
function calculateTotal(){
    //合計金額計算
    var goukei = 0;
    var kingaku;
    var kingaku_zeibetsu = {0: 0, 8: 0, 10: 0};//コピー用
    var fraction = $('#' + FRACTION + '_0').val();// 税抜き金額端数処理    
    var zeifraction = $('#' + ZEIFRACTION + '_0').val();// 税込み金額端数処理
    
    for (var i = 0; i < 15; i++) {

        kingaku = $('#' + KINGAKU_NAME + '_0_' + i).val();
        var zei = $('#' + ZEIRISTU_NAME + '_0_' + i).val();

        if (kingaku == '') {
            kingaku = 0;
        }
        kingaku = parseInt(kingaku);
        goukei = goukei + kingaku;
        // 税率セット
        kingaku_zeibetsu[zei] = kingaku_zeibetsu[zei] + parseInt(kingaku);
    }
    
    goukei = fractionProcess(goukei, fraction);
    //税抜き金額セット
    $('#' + KINGAKUKEI_NAME + '_0').val(goukei);
    //消費税
    var tax8 = fractionProcess(kingaku_zeibetsu[8] * 0.08, zeifraction);
    var tax10 = fractionProcess(kingaku_zeibetsu[10] * 0.1, zeifraction);
    $('#' + ZEI8 + '_0').val(tax8);
    $('#' + ZEI10 + '_0').val(tax10);
    var taxtotal = tax8 + tax10;
    $('#' + ZEI_NAME + '_0').val(taxtotal);
    //税込み金額
    var total = goukei + taxtotal;
    $('#' + TOTAL_NAME + '_0').val(total);
}

// 端数処理
function fractionProcess(value,math) {
    var total;
    var result;
    // 正か負の判定
    var _sign = (value < 0) ? -1 : 1;
    // 負の場合正に一時変換
    var val = value * _sign;
    // 値計算(四捨五入、切り上げ、切り捨て)
    if (math === "1") {
        total = Math.round( val );
    } else if(math === "2") {
        total = Math.ceil( val );
    } else {
        total = Math.floor( val );
    }
    // 値が負の場合マイナスになる
    result = total * _sign;
    return result;
}

//行操作
var row_copy = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'', ZEIRISTU:'0', KINGAKU:'' };//コピー用
var row_init = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'', ZEIRISTU:'0', KINGAKU:'' };//クリア用
var color_copy = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'' };//カラーコピー用
var color_init = { HINMEI:'#fff', TANKA:'#fff', SURYO:'#fff', TANNI:'#fff' };//カラークリア用
//行のデータを連想配列に入れて返す
function getRowData(pos){
	var row = {};
	row.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).val();
	row.TANKA =  $('#'+TANKA_NAME+'_0_'+pos).val();
	row.SURYO =  $('#'+SURYO_NAME+'_0_'+pos).val();
	row.TANNI =  $('#'+TANNI_NAME+'_0_'+pos).val();
	row.ZEIRISTU =  $('#'+ZEIRISTU_NAME+'_0_'+pos).val();
	row.KINGAKU =  $('#'+KINGAKU_NAME+'_0_'+pos).val();	
	
	return row;
}
//指定行に連想配列のデータをセットする
function setRowData(pos,row){
	$('#'+HINMEI_NAME+'_0_'+pos).val(row.HINMEI );
	$('#'+TANKA_NAME+'_0_'+pos).val( row.TANKA );
	$('#'+SURYO_NAME+'_0_'+pos).val( row.SURYO );
	$('#'+TANNI_NAME+'_0_'+pos).val( row.TANNI );
	$('#'+ZEIRISTU_NAME+'_0_'+pos).val( row.ZEIRISTU );
	$('#'+KINGAKU_NAME+'_0_'+pos).val( row.KINGAKU );	
}
//行の色データを連想配列に入れて返す
function getColor(pos){
    var color = {};
    color.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).hasClass('colorChange');
    color.TANKA = $('#'+TANKA_NAME+'_0_'+pos).hasClass('colorChange');
    color.SURYO = $('#'+SURYO_NAME+'_0_'+pos).hasClass('colorChange');
    color.TANNI = $('#'+TANNI_NAME+'_0_'+pos).hasClass('colorChange');	
    
    return color;
}
//指定行に連想配列の色データをセットする
function setColor(pos,color){
    if(color.HINMEI === true)
    {
        color.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).addClass('colorChange');
    }
    else if(color.HINMEI === false)
    {
        color.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).removeClass('colorChange');     
    }
    
    if(color.TANKA === true)
    {
       color.TANKA = $('#'+TANKA_NAME+'_0_'+pos).addClass('colorChange');
    }
    else if(color.TANKA === false)
    {
        color.TANKA = $('#'+TANKA_NAME+'_0_'+pos).removeClass('colorChange');    
    }
    
    if(color.SURYO === true)
    {
       color.SURYO = $('#'+SURYO_NAME+'_0_'+pos).addClass('colorChange');
    }
    else if(color.SURYO === false)
    {
        color.SURYO = $('#'+SURYO_NAME+'_0_'+pos).removeClass('colorChange');     
    }
    
    if(color.TANNI === true)
    {
        color.TANNI = $('#'+TANNI_NAME+'_0_'+pos).addClass('colorChange');
    }
    else if(color.TANNI === false)
    {
        color.TANNI = $('#'+TANNI_NAME+'_0_'+pos).removeClass('colorChange');  
    }    
}
//指定行を白色にする（insert時）
function changeColor(pos){
    $('#'+HINMEI_NAME+'_0_'+pos).removeClass('colorChange');  
    $('#'+TANKA_NAME+'_0_'+pos).removeClass('colorChange');   
    $('#'+SURYO_NAME+'_0_'+pos).removeClass('colorChange');
    $('#'+TANNI_NAME+'_0_'+pos).removeClass('colorChange');  
}
//指定行をグローバル変数にコピーする
function copyRow( pos ){
	row_copy = getRowData(pos);
        color_copy = getColor(pos);
}
//指定行にグローバル変数のデータをコピーする
function pasteRow( pos ){
	setRowData(pos,row_copy);
        setColor(pos,color_copy);
	calculateKingaku( '_0_'+String(pos) );
}
//指定箇所に空白行を挿入する
function insertRow( pos ){
	var pos_copy_to=14;
	for( ; pos_copy_to > pos; pos_copy_to-- )	{	//下から順に指定行まで処理
		var row = getRowData(pos_copy_to-1);
                var color = getColor(pos_copy_to-1);
		setRowData(pos_copy_to,row);
                setColor(pos_copy_to,color);
	}
	setRowData(pos,row_init);	//指定行は空白にする
        changeColor(pos);         //指定行は白色にする
	calculateKingaku( '_0_'+String(pos) );
}
//指定箇所の行を削除してつめる
function removeRow( pos ){
	var pos_copy_to = pos;
	for( ; pos_copy_to < 14; pos_copy_to++ )	{	//上から順に14行目まで処理
		var row = getRowData(pos_copy_to+1);
                var color = getColor(pos_copy_to+1);
		setRowData(pos_copy_to,row);
                setColor(pos_copy_to,color);
                }
	setRowData(pos_copy_to,row_init);	//15行目は空白にする
        setColor(pos_copy_to,color_init);	//15行目は白色にする
	calculateKingaku( '_0_'+String(pos) );
}

/**
 * セッションストレージにデータを保持
 * 
 */
function saveStorage() {
    
    saveSummary();
    //データを保存
    for (var i = 0; i< 15;i++) {
        sessionStorage.setItem('HINMEI' + i, $('#'+HINMEI_NAME+'_0_' + i).val());
        sessionStorage.setItem('TANKA' + i, $('#'+TANKA_NAME+'_0_' + i).val());
        sessionStorage.setItem('SURYO' + i, $('#'+SURYO_NAME+'_0_' + i).val());
        sessionStorage.setItem('TANNI' + i, $('#'+TANNI_NAME+'_0_' + i).val());
        sessionStorage.setItem('KINGAKU' + i, $('#'+KINGAKU_NAME+'_0_' + i).val());
        sessionStorage.setItem('ZEIRISTU' + i, $('#'+ZEIRISTU_NAME+'_0_' + i).val());
    }
    sessionStorage.setItem('FRACTION_VAL',$('#'+FRACTION+'_0').val());
    sessionStorage.setItem('ZEIFRACTION_VAL',$('#'+ZEIFRACTION+'_0').val());
    sessionStorage.setItem('HINMEI_NAME',HINMEI_NAME);
    sessionStorage.setItem('TANKA_NAME',TANKA_NAME);
    sessionStorage.setItem('SURYO_NAME',SURYO_NAME);
    sessionStorage.setItem('TANNI_NAME',TANNI_NAME);
    sessionStorage.setItem('KINGAKU_NAME',KINGAKU_NAME);
    sessionStorage.setItem('FRACTION',FRACTION);
    sessionStorage.setItem('ZEIFRACTION',ZEIFRACTION);
    sessionStorage.setItem('ZEIRISTU_NAME',ZEIRISTU_NAME);
    sessionStorage.setItem('KINGAKUKEI_NAME',KINGAKUKEI_NAME);
    sessionStorage.setItem('ZEI_NAME',ZEI_NAME);
    sessionStorage.setItem('TOTAL_NAME',TOTAL_NAME);
    sessionStorage.setItem('ZEI8',ZEI8);
    sessionStorage.setItem('ZEI10',ZEI10);
    sessionStorage.setItem('FLG','1');
}
/**
 * セッションストレージの情報から一覧にセットする
 * 
 */
function importStorage() {
    
    //データをセット
    if(sessionStorage.getItem('FLG') === "1") {
        HINMEI_NAME = sessionStorage.getItem('HINMEI_NAME');
        TANKA_NAME = sessionStorage.getItem('TANKA_NAME');
        SURYO_NAME = sessionStorage.getItem('SURYO_NAME');
        TANNI_NAME = sessionStorage.getItem('TANNI_NAME');
        KINGAKU_NAME = sessionStorage.getItem('KINGAKU_NAME');
        FRACTION = sessionStorage.getItem('FRACTION');
        ZEIFRACTION = sessionStorage.getItem('ZEIFRACTION');
        ZEIRISTU_NAME = sessionStorage.getItem('ZEIRISTU_NAME');
        ZEI_NAME = sessionStorage.getItem('ZEI_NAME');
        TOTAL_NAME = sessionStorage.getItem('TOTAL_NAME');
        KINGAKUKEI_NAME = sessionStorage.getItem('KINGAKUKEI_NAME');
        ZEI8 = sessionStorage.getItem('ZEI8');
        ZEI10 = sessionStorage.getItem('ZEI10');
        $('#'+ FRACTION +'_0').val(sessionStorage.getItem('FRACTION_VAL'));
        $('#'+ ZEIFRACTION +'_0').val(sessionStorage.getItem('ZEIFRACTION_VAL'));
        importSummary();
        for (var i = 0; i< 15;i++){
            $('#'+ sessionStorage.getItem('HINMEI_NAME')+'_0_'+ i).val(sessionStorage.getItem('HINMEI' + i));
            $('#'+ sessionStorage.getItem('TANKA_NAME')+'_0_' + i).val(sessionStorage.getItem('TANKA' + i));
            $('#'+ sessionStorage.getItem('SURYO_NAME')+'_0_' + i).val(sessionStorage.getItem('SURYO' + i));
            $('#'+ sessionStorage.getItem('TANNI_NAME')+'_0_' + i).val(sessionStorage.getItem('TANNI' + i));
            $('#'+ sessionStorage.getItem('KINGAKU_NAME')+'_0_' + i).val(sessionStorage.getItem('KINGAKU' + i));
            $('#'+ sessionStorage.getItem('ZEIRISTU_NAME')+'_0_' + i).val(sessionStorage.getItem('ZEIRISTU' + i));
        }
    }
    calculateTotal();
    sessionStorage.clear();
}
/*
 * 印刷画面遷移時案件情報保持
 * 
 */
function saveSummary(){
    var code = $('#form_mmhMMHUCODE_0').val();
    if(code !== undefined) {
        sessionStorage.setItem('KENMEI',$('#form_mmhKENMEI_0').val());
        sessionStorage.setItem('ATE',$('#form_mmhATE_0').val());
        sessionStorage.setItem('KOKYAKUTANTOU',$('#form_mmhKOKYAKUTANTO_0').val());
        sessionStorage.setItem('DAY',$('#form_mmhMITUMORIBI_0').val());
        sessionStorage.setItem('TANTOU',$('#form_mmhUSRID_0').val());
        sessionStorage.setItem('TANTOU_VIEW',$('#form_mmhUSRID_0_show').val());
        sessionStorage.setItem('DEADLINE',$('#form_mmhYUKOKIGEN_0').val());
        sessionStorage.setItem('BIKOU',$('#form_mmhBIKO_0').val());
    }
    else{
        sessionStorage.setItem('KENMEI',$('#form_sehKENMEI_0').val());
        sessionStorage.setItem('ATE',$('#form_sehATE_0').val());
        sessionStorage.setItem('KOKYAKUTANTOU',$('#form_sehKOKYAKUTANTO_0').val());
        sessionStorage.setItem('DAY',$('#form_sehSEIKYUBI_0').val());
        sessionStorage.setItem('TANTOU',$('#form_sehUSRID_0').val());
        sessionStorage.setItem('TANTOU_VIEW',$('#form_sehUSRID_0_show').val());
        sessionStorage.setItem('DEADLINE',$('#form_sehSIHARAIKIGEN_0').val());
        sessionStorage.setItem('TRANSFER',$('#form_sehTRANSFER_0').val());
        sessionStorage.setItem('BIKOU',$('#form_sehBIKO_0').val());
    }
    
}
/*
 * 印刷画面遷移時案件情報セット
 * 
 */
function importSummary(){
    var transfer = sessionStorage.getItem('TRANSFER');
    if( transfer === null) {
        $('#form_mmhKENMEI_0').val(sessionStorage.getItem('KENMEI'));
        $('#form_mmhATE_0').val(sessionStorage.getItem('ATE'));
        $('#form_mmhKOKYAKUTANTO_0').val(sessionStorage.getItem('KOKYAKUTANTOU'));
        $('#form_mmhMITUMORIBI_0').val(sessionStorage.getItem('DAY'));
        $('#form_mmhUSRID_0').val(sessionStorage.getItem('TANTOU'));
        $('#form_mmhUSRID_0_show').val(sessionStorage.getItem('TANTOU_VIEW'));
        $('#form_mmhYUKOKIGEN_0').val(sessionStorage.getItem('DEADLINE'));
        $('#form_mmhBIKO_0').val(sessionStorage.getItem('BIKOU'));
    }
    else
    {
        $('#form_sehKENMEI_0').val(sessionStorage.getItem('KENMEI'));
        $('#form_sehATE_0').val(sessionStorage.getItem('ATE'));
        $('#form_sehKOKYAKUTANTO_0').val(sessionStorage.getItem('KOKYAKUTANTOU'));
        $('#form_sehSEIKYUBI_0').val(sessionStorage.getItem('DAY'));
        $('#form_sehUSRID_0').val(sessionStorage.getItem('TANTOU'));
        $('#form_sehUSRID_0_show').val(sessionStorage.getItem('TANTOU_VIEW'));
        $('#form_sehSIHARAIKIGEN_0').val(sessionStorage.getItem('DEADLINE'));
        $('#form_sehTRANSFER_0').val(sessionStorage.getItem('TRANSFER'));
        $('#form_sehBIKO_0').val(sessionStorage.getItem('BIKOU'));
    }
    
}

/**
 * コピー時案件情報保持
 * 
 */
function copySaveSummary(){
    var code = $('#form_mmhMMHUCODE_0').val();
    
    if(code !== undefined) {
        sessionStorage.setItem('KENMEI',$('#form_mmhKENMEI_0').val());
        sessionStorage.setItem('ATE',$('#form_mmhATE_0').val());
        sessionStorage.setItem('KOKYAKUTANTOU',$('#form_mmhKOKYAKUTANTO_0').val());
        sessionStorage.setItem('DAY',$('#form_mmhMITUMORIBI_0').val());
        sessionStorage.setItem('TANTOU',$('#form_mmhUSRID_0').val());
        sessionStorage.setItem('TANTOU_VIEW',$('#form_mmhUSRID_0_show').val());
        sessionStorage.setItem('DEADLINE',$('#form_mmhYUKOKIGEN_0').val());
        sessionStorage.setItem('COPY','m');
    }
    else{
        sessionStorage.setItem('KENMEI',$('#form_sehKENMEI_0').val());
        sessionStorage.setItem('ATE',$('#form_sehATE_0').val());
        sessionStorage.setItem('KOKYAKUTANTOU',$('#form_sehKOKYAKUTANTO_0').val());
        sessionStorage.setItem('DAY',$('#form_sehSEIKYUBI_0').val());
        sessionStorage.setItem('TANTOU',$('#form_sehUSRID_0').val());
        sessionStorage.setItem('TANTOU_VIEW',$('#form_sehUSRID_0_show').val());
        sessionStorage.setItem('DEADLINE',$('#form_sehSIHARAIKIGEN_0').val());
        // sessionStorage.setItem('TRANSFER',$('#form_sehTRANSFER_0').val());
        sessionStorage.setItem('COPY','s');
    }
    sessionStorage.setItem('COPYFLG','1');
    
}

/**
 * コピー時案件情報セット
 * 
 */
function copyImportSummary(){
    // コピーフラグがない場合return
    if(sessionStorage.getItem('COPYFLG') !== '1')
    {
        return;
    }
    var copy = sessionStorage.getItem('COPY');
    if( copy === 'm') {
        $('#form_mmhKENMEI_0').val(sessionStorage.getItem('KENMEI'));
        $('#form_mmhATE_0').val(sessionStorage.getItem('ATE'));
        $('#form_mmhKOKYAKUTANTO_0').val(sessionStorage.getItem('KOKYAKUTANTOU'));
        $('#form_mmhMITUMORIBI_0').val(sessionStorage.getItem('DAY'));
        $('#form_mmhUSRID_0').val(sessionStorage.getItem('TANTOU'));
        $('#form_mmhUSRID_0_show').val(sessionStorage.getItem('TANTOU_VIEW'));
        $('#form_mmhYUKOKIGEN_0').val(sessionStorage.getItem('DEADLINE'));
    }
    else
    {
        $('#form_sehKENMEI_0').val(sessionStorage.getItem('KENMEI'));
        $('#form_sehATE_0').val(sessionStorage.getItem('ATE'));
        $('#form_sehKOKYAKUTANTO_0').val(sessionStorage.getItem('KOKYAKUTANTOU'));
        $('#form_sehSEIKYUBI_0').val(sessionStorage.getItem('DAY'));
        $('#form_sehUSRID_0').val(sessionStorage.getItem('TANTOU'));
        $('#form_sehUSRID_0_show').val(sessionStorage.getItem('TANTOU_VIEW'));
        $('#form_sehSIHARAIKIGEN_0').val(sessionStorage.getItem('DEADLINE'));
        // $('#form_sehTRANSFER_0').val(sessionStorage.getItem('TRANSFER'));

    }
    //sessionStorage.clear();
}