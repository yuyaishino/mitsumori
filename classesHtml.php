<?php

require_once("classesPageContainer.php");
require_once("classesBase.php");

/**
 * トップページ用Pageクラス
 * 
 */
class TopPage extends BasePage
{
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();
		$title1 = $this->prContainer->pbPageSetting['title'];
		//メンバ変数タイトル
		$this->prTitle = $title1;
	}
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		//親の処理を呼び出す
		$html = parent::makeScriptPart();
		//必要なHTMLを付け足す
		$html .='<script src="./js/tabscript.js"></script>';
		$html .= '<script language="JavaScript"><!--
			$(function()
			{
				pageLoad();
				tabClick();
			});
			--></script>';
		
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分のHTML文字列を作成する
	 *   リストでは一覧表示、入力では各入力フィールドの構築など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$post = array();
	
		$tabarray = $this->makeTabHtml('TOP_5', $this->prContainer->pbFormIni, $post);
		$this->prInitScript = $tabarray[1];
		$html = $tabarray[0];
		return $html;
		
	}
}



/**
 * リストぺージ用Pageクラス
 */
class ListPage extends BasePage
{
	
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();

		//PageID取得を_で分割
		$filename_array = explode('_',$this->prContainer->pbFileName);
		$filename_insert = $filename_array[0]."_1";     //insert時ファイル名
		if(isset($this->prContainer->pbInputContent['list']['limitstart']) == false)
		{
			$this->prContainer->pbInputContent['list']['limitstart'] = 0;
		}
		$this->prContainer->pbInputContent['list']['limit']	= ' LIMIT '.$this->prContainer->pbInputContent['list']['limitstart'].','
																		.$this->prContainer->pbPageSetting['limit'];	
			
		//変数をセット
		$this->prTitle = $this->prContainer->pbPageSetting['title'];					//メンバ変数タイトル
		$this->prMainTable = $this->prContainer->pbPageSetting['use_maintable_num'];					//main_table
		$this->prFileNameInsert = $filename_insert;			//新規作成
	}
	
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		$html = parent::makeScriptPart();
		
		$html .='<script language="JavaScript"><!--
				//history.forward();
				var isCancel = false;
				$(window).resize(function()
				{
				});
				$(function()
				{
					$(".button").corner();
					$(".free").corner();
					makeDatepicker();
				});
				function show_hide_row(row){
					$("[id="+row+"]").toggle(300);
				}
				--></script>';
			return $html;
			
	}
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{                
		if(!isset($_SESSION['list']))
		{
			$_SESSION['list'] = array();
		}
                
		//検索フォーム作成,日付フォーム作成
                if(isset($_SESSION['search']['flg']))
                {
                    if($_SESSION['search']['flg'] === 1)
                    {
                        $this->prContainer->pbInputContent = $_SESSION['search']['input'];
                        $_SESSION['search']["flg"] = 0;
                    }
                    else
                    {
                        $this->setSearchSession($this->prContainer->pbInputContent);
                    }
                }
                else
                {
                    $this->setSearchSession($this->prContainer->pbInputContent);
                }
		$formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
		$form = $formStrArray[0];			//0はフォーム用HTML
		$this->prInitScript = $formStrArray[1];	//1は構築用スクリプト
		
		//検索SQL
		$sql = array();
		$sql = joinSelectSQL($this->prContainer->pbInputContent, $this->prMainTable, $this->prContainer->pbFileName, $this->prContainer->pbFormIni);
		$sql = SQLsetOrderby($this->prContainer->pbInputContent, $this->prContainer->pbFileName, $sql);
		$limit = $this->prContainer->pbInputContent['list']['limit'];				// limit
		$limit_start = $this->prContainer->pbInputContent['list']['limitstart'];	// limit開始位置
		
		//リスト表示HTML作成
		$pagemove = intval( $this->prContainer->pbPageSetting['isPageMove'] );
		$list =  $this->makeListV2($sql, $_SESSION['list'], $limit, $limit_start, $pagemove);
		
		$checkList = $_SESSION['check_column'];
		
		//出力HTML作成
		$html ='<div class = "pad" >';
		$html .='<form name ="form" action="main.php" method="get"onsubmit = "return check(\''.$checkList.'\');">';
		$html .='<table><tr><td><fieldset><legend>検索条件</legend>';                
		$html .= $form;								//検索項目表示
                
                //--2019/06/06追加　filename取得　
                $filename = $this->prContainer->pbFileName;        
                $html .='<input type="hidden" id="clear_'.$filename.'" value = "'.$this->prContainer->pbPageSetting['sech_form_num'].'" >';
		$html .='</fieldset></td><td valign="bottom"><input type="submit" name="serch_'.$filename.'" value = "表示" class="free" ></td>';
                $html .='</fieldset></td><td valign="bottom"><input type="button" value = "クリア" class="free" onclick="clearSearch(\''.$filename.'\')"></td></tr></table>';
		$html .= $list;
		$html .= '</form>';
                
		return $html;
	}
	
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '<div class = "left" style = "HEIGHT : 30px"><form action="main.php" method="get">';
		//新規作成ボタン作成
		global $button_ini;
		if( $button_ini === null)
		{
			// ボタン設定読込み
			$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
		}
		//新規作成ページに指定されているIDがbutton.iniにあるか？
		if( array_key_exists( $this->prFileNameInsert, $button_ini ) === true )
		{
			$html .= '<input type ="submit" value = "新規作成" class = "free" name = "'.$this->prFileNameInsert.'_button">';
		}
		//CSVボタン
		$is_csv = $this->prContainer->pbPageSetting['isCSV'];
		if( $is_csv === '1' )
		{
			$html .='　<a href="csv_out.php?id='.$this->prContainer->pbFileName.'&'.$_SERVER['QUERY_STRING'].'" target="_blank" class="btn-radius">CSV出力</a>';
		}
		$html .= '</form></div>';
			
		return $html;
	}
	
	/**
	 * 関数名: makCsv
	 *   CSV出力用関数
	 * 
	 * @retrun CSV文字列
	 */
	function makCsv()
	{
		//SQL文をリストページと同じ手順で構築
		$sql = joinSelectSQL($this->prContainer->pbInputContent, $this->prMainTable, $this->prContainer->pbFileName, $this->prContainer->pbFormIni);
		$sql = SQLsetOrderby($this->prContainer->pbInputContent, $this->prContainer->pbFileName, $sql);
		
		// 項目変数
		$columns_array = explode(',',$this->prContainer->pbPageSetting['page_columns']);
		$labels_array = explode(',',$this->prContainer->pbPageSetting['column_labels']);

		//csv
		$csv_str = '';

		//------------------------//
		//          処理          //
		//------------------------//
		// db接続関数実行
		$con = dbconect();

		// クエリ発行(実データ取得)
		$judge = false;
		$result = $con->query($sql[0]) or ($judge = true);
		if($judge)
		{
			error_log($con->error,0);
			$judge = false;
		}
	
		//項目名（ここがヘッダの主要構成箇所）
		$column_count = count($labels_array);
		for($i = 0 ; $i < $column_count ; $i++)		{
			$label = str_replace('※', '', $labels_array[$i]);
			$label = str_replace('＊', '', $label);
			if($i !== 0){
				$csv_str .= ',';	//カンマを付け足す
			}
			$csv_str .= $label;
		}
		$csv_str .= "\r\n";	//改行

		//ここからデータ部分
		while($result_row = $result->fetch_array(MYSQLI_ASSOC))		{
			for($i = 0 ; $i < $column_count ; $i++)		{
				if($i !== 0){
					$csv_str .= ',';	//カンマを付け足す
				}
				//列名
				$field_name = $this->prContainer->pbParamSetting[$columns_array[$i]]['column'];
				//値
				$csv_str .= $result_row[$field_name];
			}
			$csv_str .= "\r\n";	//改行
		}
		//EXCELで開きやすいようにSJISに変換
		$result_str = mb_convert_encoding($csv_str, "SJIS");

		return $result_str;
	}
}

/** 
 *インサート
 *
 */
class InsertPage extends BasePage
{
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();
			
		$maxover = -1;
		if(isset($_SESSION['max_over']))
		{
			$maxover = $_SESSION['max_over'];
		}
		
		//メンバ変数設定
		$this->prTitle = $this->prContainer->pbPageSetting['title'];
		$this->prMainTable = $this->prContainer->pbPageSetting['use_maintable_num'];
	}
	
	
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		$html = parent::makeScriptPart();
		
		$html .='<script language="JavaScript"><!--
			//history.forward();
			var isCancel = false;
			$(function()
			{
                                    $("input"). keydown(function(e) {
                                        if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
                                            return false;
                                        } else {
                                            return true;
                                        }
                                    });
				$("input").blur(function()
				{
					var idx = this.name.lastIndexOf( "_0_" );
					if(idx !== -1)
					{
						var poststr = this.name.substr(idx);
						calculateKingaku( poststr );
                                                calculateTotal();
					}
				});
				$("select").blur(function()
				{
					var idx = this.name.lastIndexOf( "_0_" );
					if(idx !== -1)
					{
						var poststr = this.name.substr(idx);
						calculateKingaku( poststr );
                                                calculateTotal();
					}
				});
                                $(".cp_ipselect").change(function()
				{
                                        //　自社マスタ時は処理をしない
					var idx = this.name.indexOf( "jsy" );
					if(idx === -1)
					{
                                            calculateReturn()
                                            calculateTotal();
					}
				});
                                $("#print_btn").on("click", function() {
                                    //印刷ボタン押下時
                                    $.cookie("back", "戻る", {});
                                    calculateTotal();
                                    saveStorage();
                                    submitaction();
                                });

				makeDatepicker();
                                copyImportSummary();
                                importStorage();
			});
			--></script>';
		return $html;
	}
	
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentTop()
	{

		$html = '<div class = "center"><a class = "title_edit">';
		$html .= $this->prTitle;  //タイトル表示
		$html .= '</a>';

		//遷移ボタン
		$linkValue = '';
		if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
		}
		else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
		}	
		
		$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'top', STEP_INSERT, $linkValue);
		
		$html .= '</div>';
		
		if($this->prContainer->pbPageSetting['message'] != "")
		{	
			$html .= '<div class = "message">';
			$html .= '<p>';
			$html .= $this->prContainer->pbPageSetting['message'];
			$html .= '</p>';
			$html .= '</div>';
		}	
		return $html;
	}

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$_SESSION['pre_post'] = null;
		$out_column ='';
			
		//入力項目作成
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $out_column, '', "insert", $this->prContainer);
		$form = $form_array[0];
		$this->prInitScript =  $form_array[1];
		
		//----明細入力作成----//
		$header_array = $this->makeList_itemV2('', $this->prContainer->pbSecondInputContent);
		if(isset($header_array))
		{
			$header = $header_array[0];
			$this->prInitScript .=  $header_array[1];
		}
		
		//--tab作成--//
		$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
		$tab = $tabarray[0];
		$this->prInitScript .= $tabarray[1];
		
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		
		//2019/03/25パラメーター追加
		//hidden作成
		$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
		
		$send = '<form name ="insert" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.
				'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		
		//出力HTML
		$html = '<br>';
		$html .= $send;
		$html .= '<div class = "edit_table">';
		$html .= $form;
		$html .= $hidden;
		$html .= $header;
		$html .= '</div>';
		$html .= $tab;
		
		return $html;
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';
		if( isPermission($this->prContainer->pbFileName) )
		{
			//ダイアログ
			$html .= '<div id="dialog" title="入力確認" style="display:none;">
						<p>この内容でよろしいでしょうか？</p>
						</div>';
			// 読取指定
			if($this->prContainer->pbPageSetting['form_type'] !== '2')
			{
				//通常は更新ボタンを表示
				$html .= '<div class = "pad">';
				$html .= '<input type="submit" name = "insert" value = "登録" class="free">';
//                                $html .= '<input type="button" name = "insert" value = "登録" class="free" onClick = submit()>';
                                //<input type="reset" name = "cancel" value = "クリア" class="free" onClick ="isCancel = true;">';
				if($this->prContainer->pbFileName == 'MITSUMORIINFO_1' || $this->prContainer->pbFileName == 'SEIKYUINFO_1')
				{	
//                                  $html .= '<input type="submit" id="print_btn" name = "print" value="印刷" class="free" data-action="1" >';
                                    $html .= '<input type="button" id="print_btn" name = "print" value="印刷" class="free" data-action="1" >';
				}
				$html .='</div>';
			}

			//遷移ボタン
			$linkValue = '';
			if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
			}
			else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
			}
			$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'bottom', STEP_INSERT, $linkValue);
		}
		$html .='</div>';
		$html .= '</form>';
		return $html;
	}
        

}

/**
 * インサートチェック
 */
class InsertCheckPage extends InsertPage
{
	
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$this->prJudge = false;
		//$errorinfo = existCheck($_SESSION['insert'],$this->prMainTable,1);
		$errorinfo = $this->existCheck($this->prContainer->pbInputContent,'',$this->prMainTable,1);
		if(count($errorinfo) == 1 && $errorinfo[0] == "")
		{
			$this->prJudge = true;
			$this->prContainer->pbInputContent['true'] = true;
		}
		//$form_array = makeformInsert_setV2($_SESSION['insert'],$errorinfo[0],'',"insert",$this->prContainer);
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent,$errorinfo[0], '', 'insert', $this->prContainer);
		$form = $form_array[0];
		$makeDatepicker =  $form_array[1];
		//----↓明細作成----//
		$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
		if(isset($header_array))
		{	
			$header = $header_array[0];
			$makeDatepicker .=  $header_array[1];
		}
		//----↑明細作成----//
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		
		//2019/03/25パラメーター追加
		//input hidden作成関数 
		//$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
		$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
		$send = '<form name ="insert" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" id="send" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		$this->prInitScript = $makeDatepicker;//メンバ変数に保存
		$html = $send;
		$html .= '<br>';
		$html .= '<div class = "edit_table">';
		$html .= $form;
		$html .= $hidden;
		$html .= $header;
		$html .= '</div>';
		//----↓tab追加----//
		$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
		$html .= $tabarray[0];
		$this->prInitScript .= $tabarray[1];
		//----↑tab追加----//
		
		//$html .= '</form>';
		return $html;
		
	}
	
	/**
	 * 入力内容確認
	 * @return string $html jQuery日付 datepicker作成
	 */
	function makeAfterScript()
	{
                $judge = "0";
                if($this->prJudge){
                    $judge = "1";
                }
                
		$html = '<script language="JavaScript"><!-- 	
		';		
		$html.= ' $("#contents .sub-menu > a").click(function (e) {
					$("#contents ul ul").slideUp(), $(this).next().is(":visible") || $(this).next().slideDown(),
					e.stopPropagation();
				});';
		$html .='function makeDatepicker()
			{' ;
		$html.= $this->prInitScript;
		$html.= '}'; 
                
                $html.= 'var judge = ';
                $html.= $judge;
                $html.= ';';
		$html .='jQuery (function() 
                                {
                                    // エラーチェック時
                                    if(judge === 0){
                                        return false;
                                    }

                                    if($.cookie("back") == undefined)
                                    {
                                        //ダイアログ作成
                                        jQuery( "#dialog" ) . dialog( {
                                        //×ボタン隠す
                                        open:$(".ui-dialog-titlebar-close").hide(),
                                        autoOpen: true,
                                        buttons:
                                                {
                                                    "ＯＫ": function()
                                                    {
                                                        // ボタン非活性
                                                        $(".ui-dialog-buttonpane button").addClass("ui-state-disabled").attr("disabled", true);
                                                        //エレメント作成
                                                        var ele = document.createElement("input");
                                                        //データを設定
                                                        ele.setAttribute("type", "hidden");
                                                        ele.setAttribute("name", "Comp");
                                                        ele.setAttribute("value", "");
                                                        // 要素を追加
                                                        //document.send.appendChild(ele);
                                                        $("#send").append(ele);
                                                        $("#send").submit();//submit処理

                                                    },
                                                    "キャンセル": function() {$(this).dialog("close");}
                                                }
                                       });
                                    }
                                    else
                                    {
                                        $.removeCookie("back");
                                    }  
                                });			
			';
		$html.= '--></script>';
		return $html;
		
	}
}

/**
 * 編集 インサートページを継承
 * 
 */
class EditPage extends InsertPage
{
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentTop()
	{

		$html = '<div class = "center"><a class = "title_edit">';
		$html .= $this->prTitle;  //タイトル表示
		$html .= '</a>';

		//遷移ボタン
		$linkValue = '';
		if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
		}
		else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
		}
		
		$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'top', STEP_EDIT, $linkValue);
		
		$html .= '</div>';
		if($this->prContainer->pbPageSetting['message'] != "")
		{	
			$html .= '<div class = "message">';
			$html .= '<p>';
			$html .= $this->prContainer->pbPageSetting['message'];
			$html .= '</p>';
			$html .= '</div>';
		}
		return $html;
	}
	
	/**
	* @param string $prInitScript jQuery日付
	* @param $isexist 編集確認
	* @return string $html 編集項目作成
	*/
	function makeBoxContentMain()
	{
		//$_SESSION['post'] = $_SESSION['pre_post'];
		//$_SESSION['pre_post'] = null;
		$isMaster = false;
		$isReadOnly = false;
		
		$isexist = true;
		//$checkResultarray = existID($_SESSION['list']['id']);
		$checkResultarray = existID($this->prContainer->pbListId);
		if(count($checkResultarray) == 0)
		{
			$isexist = false;
		}
		
		if($isexist)
		{
			$out_column ='';
			
			if(isset($_SESSION['data']))
			{
				$data = $_SESSION['data'];
			}
			else
			{
				$data = "";
			}
			$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $out_column, $isReadOnly, "edit",$this->prContainer);
			$form = $form_array[0];
			$makeDatepicker =  $form_array[1];
			
			//--↓明細作成--//
			$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
			if(isset($header_array))
			{
				$header = $header_array[0];
				$makeDatepicker .=  $header_array[1];
			}
			//--↑明細作成--//
			
			$checkList = $_SESSION['check_column'];
			$notnullcolumns = $_SESSION['notnullcolumns'];
			$notnulltype = $_SESSION['notnulltype'];
			
			//2019/03/25パラメーター追加
			//hidden作成
			//$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
			$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
			$send = '<form name ="edit" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" enctype="multipart/form-data" 
					onsubmit = "return check(\''.$checkList.
					'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
			$this->prInitScript = $makeDatepicker;//メンバ変数に保存
			$html = '<br>';
			$html .= '<div style="clear:both;"></div>';
			$html .= $send;
			$html .= $hidden;
			$html .= '<div class = "edit_table">';
			$html .= $form;
			$html .= $header;
			$html .= '</div>';
			
			//--↓tab追加--//
			$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
			$html .= $tabarray[0];
			$this->prInitScript .= $tabarray[1];
			//--↑tab追加--//
			
		}
		else
		{
			//エラー時共通出力
			$html = $this->makeErrorNotExist();
		}
		return $html;
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';
		$is_permission = isPermission($this->prContainer->pbFileName);
		//ユーザーマスタで権限がない
		if( $is_permission === false && $this->prContainer->pbFileName === 'USERMASTER_1' ){
			//自分のIDのみ許可する
			$is_permission =( $this->prContainer->pbListId == $_SESSION['userid'] );
		}
		if( $is_permission ){
			// 読取指定
			if($this->prContainer->pbPageSetting['form_type'] !== '2')
			{
                                if($this->prContainer->pbFileName === "JISYAMASTER_3")
                                {
                                    $html .= '<div class = "pad">
					<input type="submit" name = "kousinn" value = "更新" class="free" onclick="document.getElementByName(\'edit\').action=\'main.php?'.$this->prContainer->pbFileName.'_button=\'" >';
                                }
                                else
                                {
                                    $html .= '<div class = "pad">
                                            <input type="submit" name = "kousinn" value = "更新" class="free" onclick="document.getElementByName(\'edit\').action=\'main.php?'.$this->prContainer->pbFileName.'_button=\'" >';
                                    $html .='<input type="submit" name = "delete" value = "削除" class="free" onClick = "ischeckpass = false;">';
                                }
                                
				if($this->prContainer->pbFileName == 'MITSUMORIINFO_1' || $this->prContainer->pbFileName == 'SEIKYUINFO_1')
				{	
//					$html .= '<input type="submit" id="print" name = "print" value="印刷" class="free" onclick="document.getElementByName(\'edit\').action=\'main.php?MITSUMORIPRINT_5_button=\'" >';
                                        $html .= '<input type="button" id="print_btn" name = "print" value="印刷" class="free" data-action="2">';
				}

				$html .='</div>';
			}

			//遷移ボタン
			$linkValue = '';
			if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
			}
			else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
			}
			$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'bottom', STEP_EDIT, $linkValue);
		}
		$html .= '</form>';
		return $html;
	}
}

/**
 * 編集チェック 編集ページを継承
 * 
 */
class EditCheckPage extends EditPage
{
	
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		//$_SESSION['post'] = $_SESSION['pre_post'];
		//$_SESSION['pre_post'] = null;
		$isReadOnly = false;
		$main_table = $this->prMainTable;
		
		$isexist = true;
		//$checkResultarray = existID($_SESSION['list']['id']);
		$checkResultarray = existID($this->prContainer->pbListId);
		if(count($checkResultarray) == 0)
		{
			$isexist = false;
		}
		
		if($isexist)
		{
			//make_post($this->prContainer,$_SESSION['list']['id']);
			//$errorinfo = existCheck($this->prContainer->pbInputContent,$_SESSION['list']['id'],$main_table,2);
			$errorinfo = $this->existCheck($this->prContainer->pbInputContent,$this->prContainer->pbListId,$main_table,2);
			if(count($errorinfo) == 2 && $errorinfo[0] == "" && $errorinfo[1] == "")
			{
				//$_SESSION['edit']['true'] = true;
				//$_SESSION['pre_post'] = $_SESSION['post'];
				$this->prJudge = true;
				$this->prContainer->pbInputContent['true'] = true;
			}
			if(isset($_SESSION['data']))
			{
				$data = $_SESSION['data'];
			}
			else
			{
				$data = "";
			}
			//$form_array = makeformInsert_setV2($_SESSION['edit'], $errorinfo[0], $isReadOnly, "edit",$this->prContainer);
			$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $errorinfo[0], $isReadOnly, "edit",$this->prContainer);
			$form = $form_array[0];
			$makeDatepicker =  $form_array[1];
			//--↓明細作成--//
			$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
			if(isset($header_array))
			{	
				$header = $header_array[0];
				$makeDatepicker .=  $header_array[1];
			}
			//--↑明細作成--//
			$checkList = $_SESSION['check_column'];
			$notnullcolumns = $_SESSION['notnullcolumns'];
			$notnulltype = $_SESSION['notnulltype'];
			
			//2019/03/25パラメーター追加
			//hidden作成
			//$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
			$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
			$send = '<form name ="edit" id="send" action="main.php" method="post" enctype="multipart/form-data" 
					onsubmit = "return check(\''.$checkList.
					'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
			$this->prInitScript = $makeDatepicker;//メンバ変数に保存
			$html = '<br>';
			$html .= '<div style="clear:both;"></div>';
			$html .= $send;
			$html .= $hidden;
			if($errorinfo[1] != "")
			{
				$html .='<a class = "error">';
				$html .=$errorinfo[1];
				$html .='</a><br>';
			}
			for($i = 2 ; $i < count($errorinfo) ; $i++)
			{
				$html .='<a class = "error">';
				$html .=$errorinfo[$i];
				$html .='</a><br>';
			}
			$html .= '<div class = "edit_table">';
			$html .= $form;
			$html .= $header;
			$html .= '</div>';
			//--↓tab追加--//
			$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
			$html .= $tabarray[0];
			$this->prInitScript .= $tabarray[1];
			//--↑tab追加--//
			$html .= '<div class = "pad">
				<input type="submit" name = "kousinn" value = "更新" class="free"';
			
			if($errorinfo[1] != "")
			{
				$html .= 'disabled>';
			}
			else
			{
				$html .= '>';
			}
			
			$html .='</div>';
			$html .= '</form>';
		}
		else
		{
			//エラー時共通出力
			$html = $this->makeErrorNotExist();
		}	
		return $html;
		
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';
		//ダイアログ
		$html .= '<div id="dialog" title="入力確認" style="display:none;">
					<p>この内容でよろしいでしょうか？</p>
					</div>';
		return $html;
	}

	/**
	 * 入力内容確認
	 * @return string $html jQuery日付 datepicker作成
	 */
	function makeAfterScript()
	{
                $judge = "0";
                if($this->prJudge){
                    $judge = "1";
                }
                
		$html = '<script language="JavaScript"><!-- 	
		';
		$html.= ' $("#contents .sub-menu > a").click(function (e) {
					$("#contents ul ul").slideUp(), $(this).next().is(":visible") || $(this).next().slideDown(),
					e.stopPropagation();
				});';
		$html .='function makeDatepicker()
			{' ;
		$html.= $this->prInitScript;
		$html.= '}';
                
                $html.= 'var judge = ';
                $html.= $judge;
		$html.= ';';
		$html .='jQuery (function() 
                                {
                                    // エラーチェック時
                                    if(judge === 0){
                                        return false;
                                    }
                                    
                                    if($.cookie("back") == undefined)
                                    {
                                        //ダイアログ作成
                                        jQuery( "#dialog" ) . dialog( {
                                                                            //×ボタン隠す
                                        open:$(".ui-dialog-titlebar-close").hide(),
                                        autoOpen: true,
                                        buttons:
                                                {
                                                    "ＯＫ": function()
                                                    {
                                                        // ボタン非活性
                                                        $(".ui-dialog-buttonpane button").addClass("ui-state-disabled").attr("disabled", true);
                                                        //エレメント作成
                                                        var ele = document.createElement("input");
                                                        //データを設定
                                                        ele.setAttribute("type", "hidden");
                                                        ele.setAttribute("name", "Comp");
                                                        ele.setAttribute("value", "");
                                                        // 要素を追加
                                                        //document.send.appendChild(ele);
                                                        $("#send").append(ele);
                                                                                                            //submit処理
                                                        $("#send").submit();

                                                    },
                                                    "キャンセル": function() {$(this).dialog("close");}

                                                }
                                       } );
                                    }
                                    else
                                    {
                                        $.removeCookie("back");
                                    }  
                                } );
								
			';
		$html .='		--></script>';
		return $html;
		
	}
}

/**
 * 削除
 * 
 */
class DeletePage extends EditCheckPage
{
	
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親呼び出し
		parent::executePreHtmlFunc();
		
		//$_SESSION['post'] = $_SESSION['pre_post'];
		//$filename = $_SESSION['filename'];
		$main_table = $this->prContainer->pbPageSetting['use_maintable_num'];
		$title1 = $this->prContainer->pbPageSetting['title'];
		$title2 = '';
		$isMaster = false;
		$isReadOnly = false;
		switch ($this->prContainer->pbPageSetting['delete_type'])
		{
			case 0:
				$title1 = '削除確認';
				$isReadOnly = true;
				break;
			case 1:
				$title1 = '削除確認';
				$isMaster = true;
				$isReadOnly = true;
				break;
			default:
				$title2 = '';
		}
		$this->prTitle = $title1.$title2;
		$this->prMainTable = $main_table;
	}

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分のHTML文字列を作成する
	 *   リストでは一覧表示、入力では各入力フィールドの構築など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$isReadOnly = true;
		$_SESSION['edit']['true'] = true;
		
		$isexist = true;
		//$checkResultarray = existID($_SESSION['list']['id']);
		$checkResultarray = existID($this->prContainer->pbListId);
		if(count($checkResultarray) == 0)
		{
			$isexist = false;
		}
		
		if($isexist)
		{
			$this->prJudge = true;
			$out_column ='';
			//make_post($_SESSION['list']['id']);
			if(isset($_SESSION['data']))
			{
				$data = $_SESSION['data'];
			}
			else
			{
				$data = "";
			}
			
			$checkList = $_SESSION['check_column'];
			$notnullcolumns = $_SESSION['notnullcolumns'];
			$notnulltype = $_SESSION['notnulltype'];
			//$form_array = makeformInsert_setV2($_SESSION['edit'], $out_column, $isReadOnly, "edit",$this->prContainer);
			$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $out_column, $isReadOnly, "delete",$this->prContainer);
			$form = $form_array[0];
			$makeDatepicker =  $form_array[1];
			//--↓明細作成--//
			$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
			if(isset($header_array))
			{	
				$header = $header_array[0];
				$makeDatepicker .=  $header_array[1];
			}
			//--↑明細作成--//
			
			$send = '<form name ="edit" id="send" action="main.php?'.$this->prContainer->pbFileName.'=&comp" method="post" enctype="multipart/form-data" 
					onsubmit = "return check(\''.$checkList.
					'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
			$html = '<br>';
			$html .= '<div style="clear:both;"></div>';
			$html .= $send;
			$html .= '<div class = "edit_table">';
			$html .=$form;
			$html .= $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
			$html .=$header;
			$html .= '</div>';
			$html .= '<div class = "pad">';
			$html .= '<input type="submit" name = "delete" value = "削除" class="free">';
//			$html .= '<input type="submit" name = "cancel" value = "一覧に戻る" class="free" onClick ="isCancel = true;">';
			$html .='</div>';
			$html .= '</form>';
		}
		else
		{
			//エラー時共通出力
			$html = $this->makeErrorNotExist();
		}	
		return $html;

	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';
		//ダイアログ
		$html .= '<div id="dialog" title="削除確認" style="display:none;">
					<p>削除してよろしいでしょうか？</p>
					</div>';
		return $html;
	}
}

/**
 * ステータス変更用ページ
 * 
 */
class StatusChangePage extends EditPage
{

	/**
	 * 関数名: makeGeneralHeader
	 *   汎用のヘッダ文字列(HTML)を作成する関数
	 * 
	 * @retrun HTML文字列
	 */
	public function makeGeneralHeader()
	{
		//親の処理を呼び出す
		$html = parent::makeGeneralHeader();

		//$html .= $this->prTitle;
		$html .='<style>';
 		$html .='select{font-size:20px;border:1px;}';
		$html .='</style>';

		return $html;
		
	}
	
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		//親の処理を呼び出す
		$html = parent::makeScriptPart();
		//必要なHTMLを付け足す
		/*$html .= '<script language="JavaScript"><!--
			$(function()
			{
				$(\'#form_ankSTATUS_0\').change(function() {
					var r = $(\'option:selected\').val();
					if(r==\'3\')
					{
						$(\'#mitsumori\').show();
					}
					else
					{
						$(\'#mitsumori\').hide();
					}
				})
			});
			--></script>';*/
		
		return $html;
	}

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分のHTML文字列を作成する
	 *   リストでは一覧表示、入力では各入力フィールドの構築など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$isReadOnly = false;
		
		$isexist = true;
		//$checkResultarray = existID($_SESSION['list']['id']);
		$checkResultarray = existID($this->prContainer->pbListId);
		if(count($checkResultarray) == 0)
		{
			$isexist = false;
		}
		
		if($isexist)
		{	
			$out_column ='';
			
			if(isset($_SESSION['data']))
			{
				$data = $_SESSION['data'];
			}
			else
			{
				$data = "";
			}

			//設定値
			$columns_string = $this->prContainer->pbPageSetting['page_columns'];
			$readonly_string = $this->prContainer->pbPageSetting['readonly'];																	// 読取専用項目

			//V3でフォームを作成
			$form_array = $this->makeformInsert_setV3( $this->prContainer->pbInputContent, $columns_string, $out_column, $readonly_string, "edit",$this->prContainer->pbParamSetting);
			$td_array = $form_array[0];
			$makeDatepicker =  $form_array[1];	
			
			$checkList = $_SESSION['check_column'];
			$notnullcolumns = $_SESSION['notnullcolumns'];
			$notnulltype = $_SESSION['notnulltype'];
			
			//2019/03/25パラメーター追加
			//hidden作成
			//$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
			$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
			
			$send = '<form name ="edit" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" enctype="multipart/form-data" 
					onsubmit = "return check(\''.$checkList.
					'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
			$this->prInitScript = $makeDatepicker;//メンバ変数に保存
			$html = '<br>';
			$html .= '<div style="clear:both;"></div>';
			$html .= $send;
			$html .= $hidden;//画面ID 2019/03/25
			$html .= '<div class = "edit_table">';
			
			//自前でTable構築
			$html .= '<table name ="formedit" id ="edit">';
			//最終行まではそのまま出す
			$td_count = count($td_array) - 1;
			for($i = 0 ; $i < $td_count; $i++)
			{
				$html .= '<tr>'.$td_array[$i].'</td>';
			}
			//最終行の処理
			$td = $td_array[$td_count];
			//<selectで切断
			$iPos = strpos($td, '<select');
			if( $iPos )
			{
				//<selectの前後に分ける
				$strFirst = substr( $td, 0, $iPos);
				$strSecond =  substr( $td, $iPos);
				//選択項目を調べる
				$iPos = strpos($td, 'selected');
				$iFrom = strpos($td, '>', $iPos)+1;
				$iTo =  strpos($td, '<', $iPos);
				//位置を特定して切り出し
				$strValue = substr( $td, $iFrom, ($iTo - $iFrom) );
				
				//全部つなげる
				$td = $strFirst.$strValue.'　==>　'.$strSecond;
				
				//テーブルに加える
				$html .= '<tr>'.$td.'</td>';
			}
			$html .= '</table>';
			
			//見積ﾘｽﾄ作成
			$page_id = 'MISTUMORISEL_2';
			$limit = "LIMIT 0, 15";
			$limit_start = 0;
			$post = array();
			
			$container = new PageContainer($this->prContainer->pbFormIni);
			//$container->ReadPage($page_id);
			$container->ReadPage($page_id,$this->prContainer->pbListId, $this->prContainer->pbStep);//変更2019/03/25
			$page = new BasePage($container);
			//SQLを取得
			$sql = getSelectSQL($post, $page_id);
			//案件IDを条件に付け足す
			$sql_where = 'WHERE ANKID='.$this->prContainer->pbListId.'  ';
			$sql[0] = $sql[0].$sql_where;
			$sql[1] = $sql[1].$sql_where;
			$sql = setSQLOrderby($page_id, $container->pbFormIni, $sql);
			$list =  $page->makeListV2($sql, $post, $limit, $limit_start, PAGE_NONE);

			$html .= '<table id="mitsumori" '.substr ( $list, 7 );
			
			$html .= '';
			$this->prInitScript .= " $('#mitsumori').hide();";
//			$this->prInitScript .= " $('#mitsumori').css('display','none');";
			
			$html .= '</div>';

			$html .= '<div class = "pad">
				<input type="submit" name = "Comp" value = "更新" class="free">';
			$html .='</div>';
			$html .= '</form>';
		}
		else
		{
			//エラー時共通出力
			$html = $this->makeErrorNotExist();
		}
		return $html;
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';
		return $html;
	}

}

/**
 * 入金処理ページ
 * 見積補足ページ
 * 請求補足ページ
 * 
 */
class ListInputPage extends EditPage
{
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$_SESSION['pre_post'] = null;
		$out_column ='';
			
		//入力項目作成
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $out_column, '', "insert", $this->prContainer);
		$form = $form_array[0];
		$this->prInitScript =  $form_array[1];
			
		$page_id = $this->prContainer->pbPageSetting['list_page'];
		$limit = '';
		$limit_start = 0;
		$post = array();
		
		//--2019/04/02 追加--//
		$listId = $this->prContainer->pbListId;
		$step = $this->prContainer->pbStep;
		//--2019/04/02 追加--//
		
		$factory = PageFactory::getInstance();
		//フォーム設定情報の読込み
		$container = new PageContainer( $factory->pbFormIni );
		//指定IDの情報をメンバ変数に
		$container->ReadPage( $page_id, $listId, $step );
		
		$page = $factory->createPage($page_id,$container);
		
		//SQLを取得
		$sql = getSelectSQL($post, $page_id);
		//専用処理として、リスト抽出に対する条件を付け足す
		$list_page_key = $this->prContainer->pbPageSetting['list_page_key'];
		$key_value = '';
		if( array_key_exists('form_'.$list_page_key.'_0', $this->prContainer->pbInputContent ) )
		{
			$key_value = $this->prContainer->pbInputContent['form_'.$list_page_key.'_0'];
		}
		else 
		{
			$key_value = $this->prContainer->pbInputContent[$this->prContainer->pbFormIni[$list_page_key]['column']];			
		}
		$sql_where = 'WHERE '.$this->prContainer->pbFormIni[$list_page_key]['column'].'='.$key_value.'  ';
		$sql[0] = $sql[0].$sql_where;
		
		$sql = setSQLOrderby($page_id, $page->prContainer->pbFormIni, $sql);
		$list =  $page->makeListV2($sql, $post, $limit, $limit_start, PAGE_NONE);
		
		$sub_column_string = $this->prContainer->pbPageSetting['sub_page_columns'];
		$sub_readonly_string = $this->prContainer->pbPageSetting['sub_readonly'];
		//( $post, $columns_string, $out_err_string, $readonly_string, $form_name ,&$Container)
		$sub_form_array = $this->makeformInsert_setV3($this->prContainer->pbInputContent, $sub_column_string, '', $sub_readonly_string, "insert", $this->prContainer->pbFormIni);
		$td_array = $sub_form_array[0];
		//自前でTable構築
		$sub_form = '<table name ="formedit" id ="edit">';
		//最終行まではそのまま出す
		foreach($td_array as $td)
		{
			$sub_form .= '<tr>'.$td.'</td>';
		}
		$sub_form .= '</table>';
		
		$this->prInitScript .=  $sub_form_array[1];	

		
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		
		//--2019/04/02 変更 getからpostへ--//
		$send = '<form name ="insert" action="main.php?step=1" method="post" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.
				'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		//--2019/04/02 変更 getからpostへ--//
		
		//出力HTML
		$html = '<br>';
		$html .= $send;
		$html .= '<div class = "edit_table">';
		$html .= $form;
		$html .= $list;
		$html .=  $sub_form;	
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';

		return $html;
	}

}
/**
 * 入金処理用のリスト用ページ
 * 
 */
class SeikyuNyukinListPage extends ListPage
{
	
	function makeTableTd( $id, &$columns_array, &$column_width_array, &$herf_link_array, &$result_row, $table_id, $rowNo )
	{
		//親の処理を呼ぶ
		$parent_html = parent::makeTableTd( $id, $columns_array, $column_width_array, $herf_link_array, $result_row, $table_id, $rowNo );

		//一意のIDを取る
		$code = $result_row['SEHID'];
		//入金済み？
		$disabled_nyukin = '';
		$disabled_kaijo = '';
		if(strpos($parent_html,'>入金済<') !== false){
			$disabled_nyukin = ' disabled="disabled"';
		}
		else {
			$disabled_kaijo = ' disabled="disabled"';
		}
		$button = '<input type="submit"  name="edit_'.$code.'_Comp" value="入金確認"'.$disabled_nyukin.'>';
		$button .= '<input type="submit"  name="edit_'.$code.'_Del" value="確認解除"'.$disabled_kaijo.'>';
		//ダミー文字列sp01をボタンに置き換える
		$html = str_replace( '>sp01<', '>'.$button.'<', $parent_html );

		
		return $html;
	}
	
	/************************************************************************************************************
	function makeList($sql,$post)

	引数1	$sql						検索SQL
	引数2	$post						ページ移動時のポスト

	戻り値	list_html					リストhtml
	************************************************************************************************************/
	function makeListV2( $sql, &$post, $limit, $limitstart, $page_move )
	{	
		$html = parent::makeListV2( $sql, $post, $limit, $limitstart, $page_move );
		//hidden情報をつけたし
		
		$html .= $this->makeHiddenParam($this->prContainer->pbListId,0);
		return $html;
	}
}

/**
 * 印刷用Pageクラス
 * 
 */
class CondisionPage extends InsertPage
{
	/** 画面識別 */
	protected $prRcall;
	/** 画面の設定値 */
	protected $prExecute;

	/**
	 * コンストラクタ
	 */
	public function __construct(&$container)
	{
		parent::__construct($container);
		$this->prRcall = '表示';
		$this->prExecute = '処理実行';
	}

	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		$html = parent::makeScriptPart();
		
		$html .='<script language="JavaScript"><!--
				function switchAction( page_id ){
					$("form").attr("action", page_id);
					$("form").submit();
				}
				--></script>';
		
		return $html;
	}

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$_SESSION['pre_post'] = null;
		$recall = $this->prContainer->pbPageSetting['recall_type'];
		$exec_id = $this->prContainer->pbPageSetting['list_page'];
		
		//入力項目作成
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, '', '', "insert", $this->prContainer);
		$form = $form_array[0];
		$this->prInitScript =  $form_array[1];
				
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		$send = '<form name ="print" id ="print" action="main.php?'.$this->prContainer->pbFileName.'_button=" method="post" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.
				'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		
		//出力HTML
		$html = '<br>';
		$html .= $send;
		$html .= '<div class = "edit_table">';
		$html .= '<table><tr><td>';
		$html .= $form;
		$html .= '</td><td>';

		//権限が十分な場合だけボタンを表示
		if( isPermission($this->prContainer->pbFileName) ){
			if( $recall !== '0' )
			{
				$html .= '<button type="button" name = "recall" onclick="switchAction( \'main.php?'.$this->prContainer->pbFileName.'_button=\' );" class="free">'.$this->prRcall."</button>";
			}
			$html .= '<button type="button" name = "execute" onclick="switchAction( \'main.php?'.$exec_id.'_button=\' );" class="free">'.$this->prExecute."</button>";
		}
		$html .= '</td></tr></table>';
		
		$html .= '</div>';

		$html .= '</form>';
		
		return $html;
	}
	
}

/**
 * 印刷用Pageクラス
 * 
 */
class UriageListCondisionPage extends CondisionPage
{
	/**
	 * コンストラクタ
	 */
	public function __construct(&$container)
	{
		parent::__construct($container);
		$this->prRcall = '画面表示';
		$this->prExecute = '　帳票　';
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		//帳票フォーム
		$html = '<div class = "center">';
		$html .= $this->makePrintFormTable();
		$html .='</div>';
		
		return $html;
	}

	
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makePrintFormTable()
	{
		//通常は更新ボタンを表示
		$html = '';

		//指定があるか？
		if( array_key_exists('form_dmySTARTYM_0', $this->prContainer->pbInputContent) )
		{
			$startYM = $this->prContainer->pbInputContent['form_dmySTARTYM_0'];
			//SQLを取得
			$post = array();
                        //db接続
			$con = dbconect();
                        // 自社情報取得
                        $result_jsy = $con->query('SELECT * FROM jisyamaster');
                        //行数ループ
                        while($result_row = $result_jsy->fetch_array(MYSQLI_ASSOC))
                        {
                            $tax = $result_row['TAXSALES'];
                        }
                        $result_jsy->close();
                        // SQL取得
			$sql = getSelectSQL($post, 'URIAGEPRINT_5');
			if($tax === "1"){
                            //指定値を置換 税抜き
                            $selectSQL = str_replace('@01', $startYM, $sql[0]);
                        }else{
                            //指定値を置換 税込み
                            $selectSQL = str_replace('@01', $startYM, $sql[1]);
                        }
			
			//金額配列初期化
			$kingakuArray = array();

			//SQL実行
			$result = $con->query($selectSQL);				// クエリ発行
			//終端までループ
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))
			{
				//年月をキーに、金額を連想配列に登録していく
				//※ここで取れるのはデータが存在している年月のみ
				$kingakuArray[$result_row['SEIKYUYM']] = $result_row['KINGAKU'];				
			}
			
			//ここから、固定で1年分の列を作成する
			$th = '<th></th>';
			$td = '<td>金額計</td>';
			$sum = 0;

			//時刻型変数に
			$startTime =  strtotime($startYM.'/01 00:00:00');

			//1年ループ
			for($i = 0; $i < 12; $i++)
			{
				//Xヶ月後の年月を取得
				$strYm = date('Y/m', strtotime($i.' month', $startTime));

				//取った年月をヘッダ行に追加
				$th .= '<th>　'.$strYm.'　</th>';
				
				//続いて金額行
				$td .= '<td class="right" >';
				//DBから取った金額配列に、指定の年月が含まれているか
				if(array_key_exists( $strYm, $kingakuArray ))
				{
					//含まれているなら、文字列に追加
					$td .= number_format( $kingakuArray[ $strYm ] );
					//金額計に加算
					$sum += $kingakuArray[ $strYm ];
				}
				else
				{
					//ない場合は0
					$td .= '0';
				}
				$td .= '</td>';
			}
			//最後に「計」列を追加
			$th .= '<th>　計　</th>';
			$td .= '<td class="right" >'.number_format($sum).'</td>';
			
			//作成した文字列を使ってtableを書き出す
			$html .= '<table class ="list">';
			$html .= '<thead><tr>'.$th.'</tr></thead>';
			$html .= '<tbody><tr class="stripe_none">'.$td.'</tr></tbody>';		
			$html .= '</table>';
		}
				
		return $html;
	}

}

/**
 * 入金管理表用の印刷条件指定画面クラス
 * 
 */
class NyukinListCondisionPage extends CondisionPage
{		
	/**
	 * コンストラクタ
	 */
	public function __construct(&$container)
	{
		parent::__construct($container);
		$this->prRcall = '画面表示';
		$this->prExecute = '　帳票　';
	}
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		//帳票フォーム
		$html = '<div class = "center">';
		$html .= $this->makePrintFormTable();
		$html .='</div>';
		
		return $html;
	}

	
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makePrintFormTable()
	{
		//通常は更新ボタンを表示
		$html = '';

		//指定があるか？
		if( array_key_exists('form_dmySTARTYM_0', $this->prContainer->pbInputContent) )
		{
			$startYM = $this->prContainer->pbInputContent['form_dmySTARTYM_0'];
			
			$post = array();
			//SQLを取得
			$sql = getSelectSQL($post, 'NYUKINPRINT_5');
			
			$startTime =  strtotime($startYM.'/01 00:00:00');			

			//指定月の1日
			$startDate = date('Y-m-d', $startTime);
			//1個目のパラメータ置換
			$selectSQL = str_replace('@01', $startDate, $sql[0]);
			
			//指定月の6ヵ月後の末日
			$endDate = date('Y-m-d', strtotime('-1 day', strtotime('6 month', $startTime) ) );
			//2個目のパラメータ置換
			$selectSQL = str_replace('@02', $endDate, $selectSQL);

			
			//金額集計用変数
			$kingakuArray = array();
			
			//テーブル作成開始
			$html .= '<table class ="list" border="5" rules="groups">';
			
			// SQL置換 + ヘッダ行の出力
			$html .= '<thead><tr><th>案件名</th>';
			for($i = 0; $i < 6; $i++)
			{
				//Xヵ月後
				$strYm = date('Y/m', strtotime($i.' month', $startTime));
				//ヘッダにする
				$html .= '<th>　'.$strYm.'　</th>';
				//3?8個目のパラメータ置換
				$selectSQL = str_replace('@0'.($i+3), $strYm, $selectSQL);
				
				//ついでに金額計を初期化
				$kingakuArray[$i] = 0;				
			}
			$html .= '</tr></thead>';
			

			//DB接続
			$con = dbconect();	
			//SQL実行
			$result = $con->query($selectSQL);				// クエリ発行
			
			//データ行作成開始
			$html .= '<tbody>';
			//カウント初期化
			$row_count = 0;
			//行数ループ
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))
			{
				//行カウントで色を分岐
				if( ($row_count % 2) === 0 )
				{
					$html .= '<tr  class="stripe_none">';
				}
				else
				{
					$html .= '<tr  class="stripe">';
				}
				//案件名
				$html .= '<td>'.$result_row['ANKENMEI'].'</td>';
				//金額1?6
				for($i = 0; $i < 6; $i++ )
				{
					//ラベルを作って参照
					$lable = 'KINGAKU0'.($i+1);
					$html .= '<td class="right">'.number_format( $result_row[$lable] ).'</td>';
					//金額計
					$kingakuArray[$i] += $result_row[ $lable ];				
				}
				$html .= '</tr>';
				
				$row_count++;
			}
			$html .= '</tbody>';

			//フッタ（合計行）
			$html .= '<tfoot>';
			//行カウントで色を分岐
			if( ($row_count % 2) === 0 )
			{
				$html .= '<tr  class="stripe_none">';
			}
			else
			{
				$html .= '<tr  class="stripe">';
			}
			$html .= '<td class="center">入金計</td>';

			//金額計
			for($i = 0; $i < 6; $i++ )
			{
				$html .= '<td class="right">'.number_format( $kingakuArray[$i] ).'</td>';
			}
			$html .= '</tr>';
			$html .= '</tfoot>';

			$html .= '</table>';
		}
				
		return $html;
	}

}

/**
 * 売上管理表（帳票）用Pageクラス
 * 
 */
class UriageListPrintPage extends UriageListCondisionPage
{
		
	/**
	 * 関数名: makeStylePart
	 *   CSS定義文字列(HTML)を作成する関数
	 * (基本的にはCSSファイルへのリンクを作成)
	 * 
	 * @retrun HTML文字列
	 */
	function makeStylePart()
	{
		$html = '<link rel="stylesheet" type="text/css" href="./list_css.css">';			
		$html .= '<link rel="stylesheet" type="text/css" href="./display_horizontal.css">';
		$html .= '<link rel="stylesheet" type="text/css" href="./print_horizontal.css" media="print">';
		$html .= '<link rel="stylesheet" type="text/css" href="./print_uriage.css">';
		return $html;
	}
	
	
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();
		$title1 = $this->prContainer->pbPageSetting['title'];
		//メンバ変数タイトル
		$this->prTitle = $title1;
	}
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		//親の処理を呼び出す
		$html = parent::makeScriptPart();
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentTop()
	{
		$html = "";
	
		return $html;
	}
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分のHTML文字列を作成する
	 *   リストでは一覧表示、入力では各入力フィールドの構築など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		
		//印刷用
		$html = '<div class="printpage">';
		$html .= '<h2>'.$this->prTitle.'</h2>';
		
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = $this->makePrintFormTable();
		$html .= '</div>';
		$html .= '<div class="print"><input type="button" value="印刷" id="print" class="print2"  onClick="window.print()"></div>';
		return $html;
	}
}

/**
 * 売上管理表（帳票）用Pageクラス
 * 
 */
class NyukinListPrintPage extends NyukinListCondisionPage
{
		
	/**
	 * 関数名: makeStylePart
	 *   CSS定義文字列(HTML)を作成する関数
	 * (基本的にはCSSファイルへのリンクを作成)
	 * 
	 * @retrun HTML文字列
	 */
	function makeStylePart()
	{
		$html = '<link rel="stylesheet" type="text/css" href="./list_css.css">';			
		$html .= '<link rel="stylesheet" type="text/css" href="./display_horizontal.css">';
		$html .= '<link rel="stylesheet" type="text/css" href="./print_horizontal.css" media="print">';
		return $html;
	}
	
	
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();
		$title1 = $this->prContainer->pbPageSetting['title'];
		//メンバ変数タイトル
		$this->prTitle = $title1;
	}
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		//親の処理を呼び出す
		$html = parent::makeScriptPart();
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentTop()
	{
		$html = "";
	
		return $html;
	}
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分のHTML文字列を作成する
	 *   リストでは一覧表示、入力では各入力フィールドの構築など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		
		//印刷用
		$html = '<div class="printpage">';
		$html .= '<h2>'.$this->prTitle.'</h2>';
		
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = $this->makePrintFormTable();
		$html .= '</div>';
		$html .= '<div class="print"><input type="button" value="印刷" id="print" class="print2"  onClick="window.print()"></div>';
		return $html;
	}
}



/**
 * 案件情報画面の見積タブ
 * 
 */
class SaishinTabPage extends ListPage
{
	/**
	 * 一覧の各行を成すtd文字列を構築する
	 *
	 * @param string $id
	 * @param array &$columns_array
	 * @param array &$column_width_array
	 * @param array &$herf_link_array
	 * @param array &$result_row
	 * @param string $table_id
	 * @param int $rowNo
	 * 
	 * @return array|bool
	 */
	function makeTableTd( $id, &$columns_array, &$column_width_array, &$herf_link_array, &$result_row, $table_id, $rowNo )
	{
		//親の処理を呼ぶ
		$parent_html = parent::makeTableTd( $id, $columns_array, $column_width_array, $herf_link_array, $result_row, $table_id, $rowNo );

		//一意のIDを取る
		$status = $result_row['STATUS'];	//請求
		$sky_count = intval($result_row['SCOUNT']);	//請求
		$nyu_count = intval($result_row['NCOUNT']);	//入金
		
		$url_param = '&form_sehANKID_0='.$result_row['ANKID'];
		$url_param .= '&ANKID='.$result_row['ANKID'];
		$url_param .= '&form_sehKENMEI_0='. urlencode($result_row['ANKENMEI']);
		$url_param .= '&form_sehKOKYAKUTANTO_0='. urlencode($result_row['TANTOMEI']);

		//ダミー文字列sp01をボタンに置き換える
		$sp01 = '未';
		$sp02 = '未';
		if( $status > 2 ){	//受注以上
			$sp01 = '<a href="main.php?';
			if( $sky_count === 0 ){
				$sp01 .= 'SEIKYUINFO_1_button='.$url_param.'">未</a>';
			}
			else{
				$sp01 .= 'NYUKIN_1_button='.$url_param.'">';
				$sp02 = $sp01;
				$sp01 .= '済</a>';
				if($sky_count === $nyu_count ){
					$sp02 .= '済</a>';
				}else{
					$sp02 .= '未</a>';				
				}
			}
		}
		$html = str_replace( '>sp01<', '>'.$sp01.'<', $parent_html );
		$html = str_replace( '>sp02<', '>'.$sp02.'<', $html );
		
		return $html;
	}

}

/**
 * 案件情報画面の見積タブ
 * 
 */
class MitsumoriTabPage extends ListPage
{
	/***************************************************************************
	function makebutton($fileName,$buttonPosition)
          ヘッダ部・フッタ部に表示するボタンを作成する処理

	引数1	$fileName			表示ファイル名
	引数2	$buttonPosition		表示位置

	戻り値	$con	mysql接続済みobject
	***************************************************************************/
	function makeButtonV2( $filename, $button_pos, $enable_step, $add_param='' )
	{
		// ボタン設定読込み
		$button_html = '';
		if( $button_pos === 'top' )
		{
			$button_html ='　<a href="main.php?ANKENINFO_2_button=&form_ankSTATUS_0=2" class="btn-radius">見積中案件一覧へ</a>';
		}
		return ($button_html);
	}
}

/**
 * 案件情報画面の見積タブ
 * 
 */
class AnkenMitsumoriTabPage extends ListPage
{
	/***************************************************************************
	function makebutton($fileName,$buttonPosition)
          ヘッダ部・フッタ部に表示するボタンを作成する処理

	引数1	$fileName			表示ファイル名
	引数2	$buttonPosition		表示位置

	戻り値	$con	mysql接続済みobject
	***************************************************************************/
	function makeButtonV2( $filename, $button_pos, $enable_step, $add_param='' )
	{
		//パラメータを切断
		$param_explode = explode('=', $add_param);
		$param = $param_explode[1];
		//レコードを取る
		$row_ank = loadDBRecord('ank', $param);
		if( $row_ank['STATUS'] > 2 )	{
			return '';	//受注以降は見積追加ボタンは表示しない
		}
		$url_param = $add_param;
		$url_param .= '&form_mmhKENMEI_0='. urlencode($row_ank['ANKENMEI']);
		// $url_param .= '&form_mmhUSRID_0='. urlencode($row_ank['USRID']);
		$url_param .= '&form_mmhKOKYAKUTANTO_0='. urlencode($row_ank['TANTOMEI']);
		
		//顧客
		if($row_ank['KYAID'] !== null)	{
			$row_kya = loadDBRecord('kya', $row_ank['KYAID']);
			$url_param .= '&form_mmhATE_0='.urlencode($row_kya['KOKYAKUMEI']);
		}
		// ボタン
		$button_html = '';
		if( $this->prListCount < 10 ){
			$button_html .='　<a href="main.php?MITSUMORIINFO_1_button=&'.$url_param.'" class="btn-radius">見積追加</a>';
		}

		return ($button_html);
	}
}

/**
 * 案件情報画面の請求タブ
 * 
 */
class AnkenSeikyuTabPage extends ListPage
{
	/***************************************************************************
	function makebutton($fileName,$buttonPosition)
          ヘッダ部・フッタ部に表示するボタンを作成する処理

	引数1	$fileName			表示ファイル名
	引数2	$buttonPosition		表示位置

	戻り値	$con	mysql接続済みobject
	***************************************************************************/
	function makeButtonV2( $filename, $button_pos, $enable_step, $add_param = '' )
	{
		//パラメータを切断
		$param_explode = explode('=', $add_param);
		$param = $param_explode[1];
		//レコードを取る
		$row_ank = loadDBRecord('ank', $param);
		$status = intval($row_ank['STATUS']);
		if( $status < 3 || 4 < $status )	{
			return '';	//受注未満はボタンは表示しない
		}
		$url_param = $add_param;
		$url_param .= '&ANKID='.$row_ank['ANKID'];
		$url_param .= '&form_sehKENMEI_0='. urlencode($row_ank['ANKENMEI']);
		$url_param .= '&form_sehKOKYAKUTANTO_0='. urlencode($row_ank['TANTOMEI']);

		//顧客
		if($row_ank['KYAID'] !== null)	{
			$row_kya = loadDBRecord('kya', $row_ank['KYAID']);
			$url_param .= '&form_sehATE_0='.urlencode($row_kya['KOKYAKUMEI']);
		}
		// ボタン
		$button_html = '';
		if( $this->prListCount < 10 ){
			$button_html .='　<a href="main.php?SEIKYUINFO_1_button=&'.$url_param.'" class="btn-radius">請求追加</a>';		
		}
		if( $this->prListCount > 0 ){
			$button_html .='　<a href="main.php?NYUKIN_1_button=&'.$url_param.'" class="btn-radius">入金処理</a>';
		}

		return ($button_html);
	}

}

/**
 * 見積コピーボタン作成
 * 
 */
class KeiriInsert extends InsertPage
{
	/***************************************************************************
	function makebutton($fileName,$buttonPosition)
          ヘッダ部・フッタ部に表示するボタンを作成する処理

	引数1	$fileName			表示ファイル名
	引数2	$buttonPosition		表示位置

	戻り値	$con	mysql接続済みobject
	***************************************************************************/
	function makeButtonV2( $filename, $button_pos, $enable_step, $add_param = '' )
	{
		// ボタン設定読込み
		global $button_ini;
		if( $button_ini === null)
		{
			// ボタン設定読込み
			$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
		}

		//------------------------//
		//          変数          //
		//------------------------//
		$button_html = '';
		//設定値
		$button_num = $button_ini[$filename]['set_button_'.$button_pos];
		if( $button_num === '' )
		{
			return $button_html;
		}
		$button_name = $button_ini[$filename]['set_button_'.$button_pos.'_name'];
		$button_enable = $button_ini[$filename]['set_button_'.$button_pos.'_enable'];
		//設定値を,で分割
		$button_num_array = explode(',',$button_num);
		$button_name_array = explode(',',$button_name);
		$button_enable_array = explode(',',$button_enable);
		$button_count = count($button_num_array);

		//------------------------//
		//     ボタン作成処理     //
		//------------------------//
		for ($i = 0; $i < $button_count; $i++ )
		{
			//IDを取り、今のページと同じなら追加しない
			$button_id = $button_num_array[$i];
			$param_string = $add_param;
			//有効？
			$enable = intval($button_enable_array[$i]);
			if( $enable == 0 || $enable == $enable_step )
			{
				//mmh、seh見積か請求
				$use_main = $this->prContainer->pbPageSetting['use_maintable_num'];
				//form_mmhANKID_0,form_sehANKID_0
				$key_column = 'form_'.$use_main.'ANKID_0';
				//案件IDが存在するか確認
				if(isset($this->prContainer->pbInputContent[$key_column]) )
				{
					if($this->prContainer->pbInputContent[$key_column] != "")
					{	
						$id = $this->prContainer->pbInputContent[$key_column];
						$value = loadDBRecord('ank',$id);
						//請求コピー時
						if($use_main == "seh")
						{
//							$param_string .= '&ANKID='.$value['ANKID'].'&form_ankANKUCODE_0='.$value['ANKUCODE'];
                                                        $param_string .= '&ANKID='.$value['ANKID'].'&form_ankANKUCODE_0=';
						}
						else
						{
							//見積コピー時
							if($value['SANKOANKID'] != "")//参考案件IDが入っていたら
							{
								$reference = loadDBRecord('ank',$value['SANKOANKID']);
								$param_string .= '&ANKID='.$value['ANKID'].'&form_ankANKUCODE_0='.$reference['ANKUCODE'];
							}
							else
							{
								$param_string .= '&ANKID='.$value['ANKID'].'&form_ankANKUCODE_0=';
							}
						}	
						
					}	
				}
				$button_html .='　<a href="main.php?'.$button_id.'_button=&'.$param_string.'" class="btn-radius">'.$button_name_array[$i].'</a>';
			}
		}
		return ($button_html);

	}
}
/*
 * 見積,請求新規登録Check処理
 * 
 */
class KeiriInsertCheck extends KeiriInsert
{
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$this->prJudge = false;
		//$errorinfo = existCheck($_SESSION['insert'],$this->prMainTable,1);
		$errorinfo = $this->existCheck($this->prContainer->pbInputContent,'',$this->prMainTable,1);
		if(count($errorinfo) == 1 && $errorinfo[0] == "")
		{
			$this->prJudge = true;
			$this->prContainer->pbInputContent['true'] = true;
		}
		//$form_array = makeformInsert_setV2($_SESSION['insert'],$errorinfo[0],'',"insert",$this->prContainer);
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent,$errorinfo[0], '', 'insert', $this->prContainer);
		$form = $form_array[0];
		$makeDatepicker =  $form_array[1];
		//----↓明細作成----//
		$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
		if(isset($header_array))
		{	
			$header = $header_array[0];
			$makeDatepicker .=  $header_array[1];
		}
		//----↑明細作成----//
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		
		//2019/03/25パラメーター追加
		//hidden作成
		//$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
		$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
		$send = '<form name ="insert" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" id="send" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		$this->prInitScript = $makeDatepicker;//メンバ変数に保存
		$html = $send;
		$html .= '<br>';
		$html .= '<div class = "edit_table">';
		$html .= $form;
		$html .= $hidden;
		$html .= $header;
		$html .= '</div>';
		//----↓tab追加----//
		$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
		$html .= $tabarray[0];
		$this->prInitScript .= $tabarray[1];
		//----↑tab追加----//
		
		//$html .= '</form>';
		return $html;
		
	}
	
	/**
	 * 入力内容確認
	 * @return string $html jQuery日付 datepicker作成
	 */
	function makeAfterScript()
	{
		$html = '<script language="JavaScript"><!-- 	
		';
		
		$html.= ' $("#contents .sub-menu > a").click(function (e) {
					$("#contents ul ul").slideUp(), $(this).next().is(":visible") || $(this).next().slideDown(),
					e.stopPropagation();
				});';
		$html .='function makeDatepicker()
			{' ;
		$html.= $this->prInitScript;
		$html.= '}';
				
		$html .='jQuery (function() 
                                {
                                    if($.cookie("back") == undefined)
                                    {
                                        //ダイアログ作成
                                        jQuery( "#dialog" ) . dialog( {
                                        //×ボタン隠す
                                        open:$(".ui-dialog-titlebar-close").hide(),
                                        autoOpen: true,
                                        buttons:
                                                {
                                                    "ＯＫ": function()
                                                    {
                                                        // ボタン非活性
                                                        $(".ui-dialog-buttonpane button").addClass("ui-state-disabled").attr("disabled", true);
                                                        //エレメント作成
                                                        var ele = document.createElement("input");
                                                        //データを設定
                                                        ele.setAttribute("type", "hidden");
                                                        ele.setAttribute("name", "Comp");
                                                        ele.setAttribute("value", "");
                                                        // 要素を追加
                                                        //document.send.appendChild(ele);
                                                        $("#send").append(ele);
                                                                                                            //submit処理
                                                        $("#send").submit();

                                                    },
                                                    "キャンセル": function() {$(this).dialog("close");}

                                                }
                                       } );
                                    }
                                    else
                                    {
                                        $.removeCookie("back");
                                    }     
                                } );
								
			';
		$html.= '--></script>';
		return $html;
		
        }
}

/**
 * 案件情報画面の見積タブ
 * 
 */
class HosokuListPage extends ListPage
{
	/**
	 * 一覧の各行を成すtd文字列を構築する
	 *
	 * @param string $class_origin
	 * @param array &$columns_array
	 * @param array &$column_width_array
	 * @param array &$herf_link_array
	 * @param array &$result_row
	 * @param string $table_id
	 * @param int $rowNo
	 * 
	 * @return string 
	 */
	function makeTableTd( $class_origin, &$columns_array, &$column_width_array, &$herf_link_array, &$result_row, $table_id, $rowNo )
	{
		//親の処理を呼ぶ
		$parent_html = parent::makeTableTd( $class_origin, $columns_array, $column_width_array, $herf_link_array, $result_row, $table_id, $rowNo );

		//一意のIDを取る
		$code = $result_row['MMFID'];
		$button = '<input type="submit"  name="edit_'.$code.'_Del" value="X">';
		//ダミー文字列sp01をボタンに置き換える
		$html = str_replace( '>sp01<', '>'.$button.'<', $parent_html );
		
		return $html;
	}
}


class CopyListPage extends ListPage
{
	/**
	 * 次の画面へ送るパラメーター作成
	 * 
	 * @param $column
	 * 
	 * return $getparam
	 */
	function makeGetAdditionalListParam($column)
	{
		$getparam = "";
		if(isset($this->prContainer->pbInputContent['ANKID']))
		{
			$link_id = $this->prContainer->pbInputContent['ANKID'];
			$getparam = "&ANKID=".$link_id;
		}
		return $getparam;
	}
}
class UserMasterPage extends EditPage{

	/**
	 * 登録フォーム用のHTMLを返す
	 * V3はtableのtdタグ配列で返す。何らかの特殊編集を行う場合はこちらを使用する
	 *
	 * @param  $post 入力内容
	 * @param  $columns_string
	 * @param  $out_err_string
	 * @param  $readonly_string
	 * @param $form_name 
	 * @param $param_setting 項目設定値 
	 * 
	 * @return array
	 */
	function makeformInsert_setV3( &$post, $columns_string, $out_err_string, $readonly_string, $form_name ,&$param_setting)
	{
		//戻り値
		$form_result = parent::makeformInsert_setV3($post, $columns_string, $out_err_string, $readonly_string, $form_name ,$param_setting);

		if( isPermission('USERMASTER_1') === false )
		{
			$form_td = array();
			$count = count($form_result[0]);
			for($i = 0; $i < $count; $i++)	{
				if( strpos( $form_result[0][$i],'KENGEN') === false ){
					$form_td[] = $form_result[0][$i];
				}
				else {
					$form_td[] = '<input type ="hidden" name = "form_usrKENGEN_0" id = "form_usrKENGEN_0" value = "'.$post['form_usrKENGEN_0'].'" >';
				}
			}
			$form_result[0] = $form_td;
		}
		
		return ($form_result);
	}
}

class UserMasterCheckPage extends EditCheckPage{

	/**
	 * 登録フォーム用のHTMLを返す
	 * V3はtableのtdタグ配列で返す。何らかの特殊編集を行う場合はこちらを使用する
	 *
	 * @param  $post 入力内容
	 * @param  $columns_string
	 * @param  $out_err_string
	 * @param  $readonly_string
	 * @param $form_name 
	 * @param $param_setting 項目設定値 
	 * 
	 * @return array
	 */
	function makeformInsert_setV3( &$post, $columns_string, $out_err_string, $readonly_string, $form_name ,&$param_setting)
	{
		//戻り値
		$form_result = parent::makeformInsert_setV3($post, $columns_string, $out_err_string, $readonly_string, $form_name ,$param_setting);

		if( isPermission('USERMASTER_1') === false )
		{
			$form_td = array();
			$count = count($form_result[0]);
			for($i = 0; $i < $count; $i++)	{
				if( strpos( $form_result[0][$i],'KENGEN') === false ){
					$form_td[] = $form_result[0][$i];
				}
				else {
					$form_td[] = '<input type ="hidden" name = "form_usrKENGEN_0" id = "form_usrKENGEN_0" value = "'.$post['form_usrKENGEN_0'].'" >';
				}
			}
			$form_result[0] = $form_td;
		}
		
		return ($form_result);
	}
}

	
