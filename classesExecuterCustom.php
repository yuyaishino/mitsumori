<?php
require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");
require_once("f_DB.php");

/**
 * ステータスの更新クラス
 * 
 */
class StatusUpdate extends BaseLogicExecuter
{
	/**
	 * executeSQL
	 * ステータス更新
	 */
	public function executeSQL()
	{
		//DB接続、トランザクション開始
		$con = beginTransaction();
		
		$judge = false;
		$edit = $this->prContainer->pbInputContent;
		//$tablenum = $this->prFormIni['use_maintable_num'];
		$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
		//$listid = $_SESSION['list']['id'];
		if(isset($_SESSION['list']['uniqe']))
		{
			$edit['uniqe'] = $_SESSION['list']['uniqe'];
		}
			
		$edit[$tablenum.'ID'] = $edit['edit_list_id'];
			
		$result = update($this->prContainer->pbFileName, $edit,$con);
		
		//ステータス変更時引き合い、見積の場合
		if(intval($this->prContainer->pbInputContent['form_ankSTATUS_0']) < 3)
		{
			$updateid = $this->prContainer->pbListId;
			$sql = "UPDATE mitsumoriinfo SET SAIYO = '0' WHERE ANKID = $updateid;";
			$result = $con->query($sql) or ($judge = true);																		// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge =false;
			}
		}
		
		//ステータスが受注時見積が選択されている場合
		if(isset($this->prContainer->pbInputContent['frmSAIYO']))
		{
			$updateid = $this->prContainer->pbInputContent['frmSAIYO'];
			
			$sql = "UPDATE mitsumoriinfo SET SAIYO = '1' WHERE MMHID = $updateid;";
			$result = $con->query($sql) or ($judge = true);																		// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge =false;
			}
		}

		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		
		//指定ページへ遷移
		$this->PageJump("ANKENSHOW_1_button", $edit['edit_list_id'], 2,"","");
	}
	
}

/**
 * 見積コピー登録値取得
 * 
 */
class CopyExecuter extends BaseLogicExecuter
{
	/**
	 * 見積のデータを取得し案件コードを変更する
	 * 
	 */
	public function executeSQL()
	{
		$list_id = $this->prContainer->pbListId;
		
		$insert_code = "";
		$content = "";
		$blankcolumns = $this->prContainer->pbPageSetting['page_columns'];
		$blankcolumns_array = explode(',',$blankcolumns);
		$history = $_SESSION['history'];
		//見積,案件,請求のデータ取り出し
		$content = make_post("",$list_id);
                
		//明細値取得
                $contents = make_headerpost("MITSUMORIINFO_1_M", $list_id, "mmh");
		//値をブランクにする
		for($i = 0 ; $i < count($blankcolumns_array) ; $i++)
		{
			$column = $blankcolumns_array[$i];
			$fild_column = $this->prContainer->pbFormIni[$column]['column'];
			
			$serch = "form_".$blankcolumns_array[$i]."_0";
			if(!isset($content[$serch]))
			{
				$serch = $fild_column;
				$content[$serch] = "";
				continue;
			}
				
			$content[$serch] = "";
		}
		//----------見積コピー時----------//
		if($this->prContainer->pbFileName == "MITSUMORICOPY_9")
		{
			//案件コード保持
			//関数makeTabHtmlで作成されたANKCODEを使用
			/*if(isset($_SESSION['ANKID']))
			{
				$insert_code = $_SESSION['ANKID'];
				unset($_SESSION['ANKID']);
			}*/
			if(isset($this->prContainer->pbInputContent['ANKID']))
			{
				$insert_code = $this->prContainer->pbInputContent['ANKID'];
			}
			
			if($insert_code != "")
			{
				$content['form_mmhANKID_0'] = $insert_code;
			}
			
			$count = count($history);
			$content_array = array_keys($content);
			for($i = $count-1; $i >= 0 ; $i-- )
			{
				//請求登録から見積コピーの場合
				if($history[$i] === "SEIKYUINFO_1")
				{
                                    
//                                    foreach ($content_array as $key) {
//                                        $str = str_replace("mm", "se", $key);
//                                        $newcontent[$str] = $content[$key];
//                                    }
//                                    
                                    $contents = $this->selectDate("seh");
                                    // 備考セット
                                    if(isset($content["form_mmhBIKO_0"])){
                                        $contents["form_sehBIKO_0"] = $content["form_mmhBIKO_0"];
                                    }else{
                                        $contents["form_sehBIKO_0"] = "";
                                    }
                                    //明細値取得
                                    $second = make_headerpost("MITSUMORIINFO_1_M", $list_id, "mmh");
                                    $content_array = array_keys($second);
                                    //semへ置換
                                    foreach ($content_array as $key) {
                                        $str = str_replace("mmm", "sem", $key);
                                        $secondcontent[$str] = $second[$key];
                                    }
                                    //請求ページへ遷移
                                    $this->PageJump("SEIKYUINFO_1", $list_id, 1,$contents,$secondcontent);
                                }
				else if($history[$i] === "MITSUMORIINFO_1")
				{
                                    if(isset($this->prContainer->pbInputContent['ANKID']))
                                    {
                                        $contents = $this->selectDate("mmh");
                                    }
                                    if(isset($content["form_mmhBIKO_0"])){
                                        $contents["form_mmhBIKO_0"] = $content["form_mmhBIKO_0"];
                                    }else{
                                        $contents["form_mmhBIKO_0"] = "";
                                    }
                                    
                                    //見積ページへ遷移
                                    $this->PageJump("MITSUMORIINFO_1", $list_id, 1, $contents, "");
                                }
			}
			
		}
		
		//----------請求コピー時----------//
		if($this->prContainer->pbFileName == "SEIKYUCOPY_9")
		{
                    $contents = $this->selectDate("seh");
                    //案件コード保持
                    if (isset($this->prContainer->pbInputContent['ANKID'])) {
                        $insert_code = $this->prContainer->pbInputContent['ANKID'];
                    }
                    // 備考、振込先追加
                    $contents["form_sehBIKO_0"] = $content["form_sehBIKO_0"];
                    $contents["form_sehTRANSFER_0"] = $content["form_sehTRANSFER_0"];
                    if ($insert_code != "") {
                        $content['form_sehANKID_0'] = $insert_code;
                    }
                    //指定ページへ遷移
                    $this->PageJump("SEIKYUINFO_1", $list_id, 1, $contents, "");
                }
		
		//---------案件流用---------//
		if($this->prContainer->pbFileName == "ANKENREUSE_9")
		{
			//参考案件
			$content['SANKOANKID'] = $list_id;
			//指定ページへ遷移
			$this->PageJump("ANKENINFO_1", "", 1, $content,"");
		}
	}
        
        /**
         * コピー時案件情報の取得を行う
         */
        function selectDate($setName){
            
            $contents = make_post("", $this->prContainer->pbInputContent['ANKID'],"ANKENINFO_1");
            $content['form_'.$setName.'ANKID_0'] = $contents['ANKID'];
            $content['form_'.$setName.'KENMEI_0'] = $contents['form_ankANKENMEI_0'];
            // $content['form_'.$setName.'USRID_0'] = $contents['USRID'];
            $content['form_'.$setName.'KOKYAKUTANTO_0'] = $contents['form_ankTANTOMEI_0'];
            //DB接続
            $con = dbconect();
            $sql = 'SELECT KOKYAKUMEI FROM kokyakumaster WHERE KYAID ='.$contents['KYAID'].'';
            //SQL実行
            $result = $con->query($sql);
            //行数ループ
            while($result_row = $result->fetch_array(MYSQLI_ASSOC))
            {
                    $content['form_'.$setName.'ATE_0'] = $result_row['KOKYAKUMEI'];
            }
            $result->close();
            
            
            return $content;
    }
	
}

/**
 * 入金処理
 * 
 */
class NyukinExecuter extends BaseLogicExecuter
{
	/**
	 * 登録処理
	 * 
	 */
	public function executeSQL()
	{
		$judge = false;
		//抽出ID
		$listId = $this->prContainer->pbListId;
		//ステップ
		$step = $this->prContainer->pbStep;
		//iniファイル読み込み
		$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
		//"sub"の設定値を取得
		$tablenum = $this->prContainer->pbPageSetting['sub_use_maintable_num'];
		$tablename = $this->prContainer->pbFormIni[$tablenum]['table_name'];
		$sub_columns = $this->prContainer->pbPageSetting['sub_page_columns'];
		$sub_columns_array = explode(',',$sub_columns);
		$update_columns = $this->prContainer->pbPageSetting['sub_auto_ins_column_num'];
		$update_columns_array = explode(',',$update_columns);
		$update_value = $this->prContainer->pbPageSetting['sub_auto_ins_column_value'];
		$update_value_array = explode(',',$update_value);
		$ankcode = $this->prContainer->pbInputContent["edit_list_id"];
		
		//DB接続、トランザクション開始
		$con = beginTransaction();
		
		if($step == STEP_DELETE)
		{	
			$sql = "DELETE FROM nyuukininfo WHERE SEHID = '$listId';" ;
		}
		else
		{
			$value = loadDBRecord("seh", $listId);
			$this->prContainer->pbInputContent['KINGAKUKEI'] = $value['KINGAKUKEI'];
			$this->prContainer->pbInputContent['SEHID'] = $value['SEHID'];
			$this->prContainer->pbInputContent['USRID'] = $value['USRID'];
			//SQL作成
			$sql = "INSERT INTO ".$tablename." (";

			//sub_page_columnsの部分
			for($i = 0 ; $i < count($sub_columns_array) ; $i++)
			{
				if(isset($this->prContainer->pbFormIni[$sub_columns_array[$i]]['column']) == true)
				{
					//フォーマット指定を取得
					//$format = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['form1_format'];
					//DBによるAutoIncrement項目は含めない
					//列名を取得してつけたし
					$columnName = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['column'];
					$sql .= $columnName.",";
				}
			}
			//自動更新項目
			if($update_columns != '')
			{
				for( $i = 0 ; $i < count($update_columns_array) ; $i++)
				{
					//表示項目に含まれているなら処理しない
					if(in_array( $update_columns_array[$i], $sub_columns_array ) )
					{
						continue;
					}
					$columnName = $this->prContainer->pbFormIni[$update_columns_array[$i]]['column'];
					$sql .= $columnName.",";
				}
			}

			//項目名末尾の「,」を取る
			$sql = substr($sql,0,-1);

			//ここから値
			$sql .= ")VALUES(";

			//sub_page_columnsの部分
			for($i = 0 ; $i < count($sub_columns_array) ; $i++)
			{
				$columnValue = '';
				//型を取得
				if(isset($this->prContainer->pbFormIni[$sub_columns_array[$i]]['form1_type']) == true)
				{
					//フォーマット指定を取得
					//$format = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['form1_format'];

					for($j = 0; $j < 5 ; $j++)
					{
						$serch_str = "form_".$sub_columns_array[$i]."_".$j;

						if(isset($this->prContainer->pbInputContent[$serch_str]))
						{
							$columnValue .= $this->prContainer->pbInputContent[$serch_str];
						}
					}
					$columnValue = rtrim($columnValue,'');

					$fieldtype = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['fieldtype'];
					$singleQute = $fieldtype_ini[$fieldtype];
					$sql .= $singleQute.$columnValue.$singleQute.",";
				}
			}

			//自動更新項目
			if($update_columns != '')
			{
				for( $i = 0 ; $i < count($update_value_array) ; $i++)
				{
					//指定値
					$columnValue = getAutoUpdateValue( $update_value_array[$i], $this->prContainer->pbInputContent );

					$sql .= $columnValue.",";
				}
			}

			//末尾の「,」を取る
			$sql = substr($sql,0,-1);

			// )で閉める
			$sql .= ");";
		}	
		
		$result = $con->query($sql) or ($judge = true);																		// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge =false;
		}
		////////////////////操作履歴///////////////////////
		//$result = addSousarireki($this->prContainer->pbFilename, STEP_INSERT, $insertsql, $con);
		addSousarireki($this->prContainer->pbFileName, $step, $sql, $con);
		////////////////////操作履歴///////////////////////
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		//指定ページへ遷移
		$this->refreshSession("NYUKIN_1", '', '2');		
		$url = '?NYUKIN_1_button=';
		$url .= '&form_sehANKID_0='.$this->prContainer->pbInputContent['edit_list_id'];
		$url .= '&ANKID='.$this->prContainer->pbInputContent['edit_list_id'];
		header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://").$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/main.php$url");
		exit();	
		
	}
	
}

/**
 * 
 * 見積補足登録
 */
class HosokuExecuter extends BaseLogicExecuter
{
	/**
	 * 補足登録処理
	 * 
	 */
	public function executeSQL()
	{
		$judge = false;
		//抽出ID
		$listId = $this->prContainer->pbListId;
		//iniファイル読み込み
		$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
		//"sub"の設定値を取得
		$tablenum = $this->prContainer->pbPageSetting['sub_use_maintable_num'];
		$tablename = $this->prContainer->pbFormIni[$tablenum]['table_name'];
		$sub_columns = $this->prContainer->pbPageSetting['sub_page_columns'];
		$sub_columns_array = explode(',',$sub_columns);
		$update_columns = $this->prContainer->pbPageSetting['sub_auto_ins_column_num'];
		$update_columns_array = explode(',',$update_columns);
		$update_value = $this->prContainer->pbPageSetting['sub_auto_ins_column_value'];
		$update_value_array = explode(',',$update_value);
		
		//SQL作成
		$insertsql = "INSERT INTO ".$tablename." (";

		//sub_page_columnsの部分
		for($i = 0 ; $i < count($sub_columns_array) ; $i++)
		{
			if(isset($this->prContainer->pbFormIni[$sub_columns_array[$i]]['column']) == true)
			{
				//フォーマット指定を取得
				$format = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['form1_format'];
				//DBによるAutoIncrement項目は含めない
				//列名を取得してつけたし
				$columnName = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['column'];
				$insertsql .= $columnName.",";
			}
		}
		//自動更新項目
		if($update_columns != '')
		{
			for( $i = 0 ; $i < count($update_columns_array) ; $i++)
			{
				//表示項目に含まれているなら処理しない
				if(in_array( $update_columns_array[$i], $sub_columns_array ) )
				{
					continue;
				}
				$columnName = $this->prContainer->pbFormIni[$update_columns_array[$i]]['column'];
				$insertsql .= $columnName.",";
			}
		}
		
		//項目名末尾の「,」を取る
		$insertsql = substr($insertsql,0,-1);
		
		//ここから値
		$insertsql .= ")VALUES(";
		
		//sub_page_columnsの部分
		for($i = 0 ; $i < count($sub_columns_array) ; $i++)
		{
			$columnValue = '';
			//型を取得
			if(isset($this->prContainer->pbFormIni[$sub_columns_array[$i]]['form1_type']) == true)
			{
				//フォーマット指定を取得
				$format = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['form1_format'];
				
				for($j = 0; $j < 5 ; $j++)
				{
					$serch_str = "form_".$sub_columns_array[$i]."_".$j;
					
					if(isset($this->prContainer->pbInputContent[$serch_str]))
					{
						$columnValue .= $this->prContainer->pbInputContent[$serch_str];
					}
				}
				$columnValue = rtrim($columnValue,'');

				$fieldtype = $this->prContainer->pbFormIni[$sub_columns_array[$i]]['fieldtype'];
				$singleQute = $fieldtype_ini[$fieldtype];
				$insertsql .= $singleQute.$columnValue.$singleQute.",";
			}
		}
		
		//自動更新項目
		if($update_columns != '')
		{
			for( $i = 0 ; $i < count($update_value_array) ; $i++)
			{
				//指定値
				$columnValue = getAutoUpdateValue( $update_value_array[$i], $this->prContainer->pbInputContent );

				$insertsql .= $columnValue.",";
			}
		}
		//末尾の「,」を取る
		$insertsql = substr($insertsql,0,-1);

		// )で閉める
		$insertsql .= ");";
		
		//DB接続、トランザクション開始
		$con = beginTransaction();
		
		$result = $con->query($insertsql) or ($judge = true);			// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge =false;
		}
		////////////////////操作履歴///////////////////////
		addSousarireki($this->prContainer->pbFileName, STEP_INSERT, $insertsql, $con);
		////////////////////操作履歴///////////////////////
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		
		//ページ遷移
		$this->PageJump($this->prContainer->pbFileName, $this->prContainer->pbListId, '2', "","");
		
	}
	
}

class DeleteHosokuExecuter extends BaseLogicExecuter
{
	/**
	 * 補足削除処理
	 * 
	 */
	public function executeSQL()
	{
		$judge = false;
		//抽出ID
		$listId = $this->prContainer->pbListId;
		$step = $this->prContainer->pbStep;
		$use_main = $this->prContainer->pbPageSetting['use_maintable_num'];
		$value = loadDBRecord("mmf",$listId);
		
		
		//DB接続、トランザクション開始
		$con = beginTransaction();
		$sql = "DELETE FROM mitsumorihosokuinfo WHERE MMFID = '$listId';";
		$result = $con->query($sql) or ($judge = true);																		// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge =false;
		}
		
		if($result == true)
		{
			$deletefile = mb_convert_encoding($value['FILE'], "SJIS", "AUTO");
			$result = unlink('./file/'.$deletefile);
		}	

		////////////////////操作履歴///////////////////////
		//$result = addSousarireki($this->prContainer->pbFilename, STEP_INSERT, $insertsql, $con);
		addSousarireki($this->prContainer->pbFileName, $step, $sql, $con);
		////////////////////操作履歴///////////////////////
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		
		//ページ遷移
		$this->PageJump($this->prContainer->pbFileName, $this->prContainer->pbInputContent['form_'.$use_main.'ANKID_0'], '2', "","");
	}
	
	
	
}

class UserCsvImportExecute extends ImportCsvExecute
{
    
    /**
     * エラーチェック処理
     * 
     */
    public function executeSQL()
    {
        $filename = $this->prContainer->pbFileName;
        $readBody = $this->importCSV();

        for($i = 0; $i < count($this->a); $i++)
        {
            $keta[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['form1_length'];
            $param[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['column'];
            $blank[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['isnotnull'];
        }
        
        //DB接続、トランザクション開始
        $con = beginTransaction();

        
        //------------------------//
        //          変数          //
        //------------------------//
        $flg = 0;
        
        //------------------------//
        //       チェック処理     //
        //------------------------//
        for($i = 0; $i < count($readBody); $i++) 
        {
            
            for($j = 0; $j < count($param); $j++)
            {
                if($blank[$j] === "1")
                {
                    //ブランクチェック
                    if($this->blankCheck($readBody[$i][$param[$j]]))
                    {
                        $flg = -1;
                    }
                }
                //桁数のチェック
                if($this->ketaCheck($readBody[$i][$param[$j]],$keta[$j]))
                {
                    $flg = -1;
                }
            }
            
            //フォーマットチェック
            if($this->formatCheck($readBody[$i]['USERMEI'],"/^[!-~]+$/"))
            {
                $flg = -1;
            }
            else if($this->formatCheck($readBody[$i]['USERPASS'],"/^[!-~]+$/"))
            {
                $flg = -1;
            }
            else if($this->formatCheck($readBody[$i]['PSUKEY'],"/^[a-zA-Z0-9]+$/"))
            {
                $flg = -1;
            }
            
            //存在チェック
            if($this->sonzaiCheck($readBody[$i]['KENGEN'],$con,"AUTH"))
            {
                $flg = -1;
            }

            if($flg === -1)
            {
                $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
                //トランザクションコミットまたはロールバック
                commitTransaction($flg,$con);
            }
            else
            {
                //loginuserinfoに登録
                $sql = "INSERT INTO loginuserinfo (USERMEI,USERPASS,KENGEN,HYOJIMEI,STAMPNAME,PSUKEY,UPDATETIME,UPDATEUSER) "
                        . "VALUE ('" . $readBody[$i]['USERMEI'] . "','" . $readBody[$i]['USERPASS'] . "','" . $readBody[$i]['KENGEN'] . "','" . $readBody[$i]['HYOJIMEI'] . "','" . $readBody[$i]['STAMPNAME'] . "','" . $readBody[$i]['PSUKEY'] . "',NOW()," . $_SESSION['userid'] . ");";
                $result = $con->query($sql);
                addSousarireki($filename,STEP_INSERT,$sql,$con);
            }
        }
        
        //トランザクションコミットまたはロールバック
	commitTransaction($result,$con);
        
        $filename = 'USERMASTER_2';
        $this->PageJump($filename, $_SESSION['userid'], 1, "", "");
    }
}

class CustomerCsvImportExecute extends ImportCsvExecute
{

    /**
     * エラーチェック処理
     * 
     */
    public function executeSQL()
    {
        $filename = $this->prContainer->pbFileName;
        $readBody = $this->importCSV();
        
        for($i = 0; $i < count($this->a); $i++)
        {
            $keta[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['form1_length'];
            $param[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['column'];
            $blank[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['isnotnull'];
        }
    
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        //------------------------//
        //          変数          //
        //------------------------//
        $flg = 0;
        
        //------------------------//
        //       チェック処理     //
        //------------------------//
        for($i = 0; $i < count($readBody); $i++) 
        {

            for($j = 0; $j < count($param); $j++)
            {
                if($blank[$j] === "1")
                {
                    //ブランクチェック
                    if($this->blankCheck($readBody[$i][$param[$j]]))
                    {
                        $flg = -1;
                    }
                }
                //桁数のチェック
                if($this->ketaCheck($readBody[$i][$param[$j]],$keta[$j]))
                {
                    $flg = -1;
                }
            }
  
            //フォーマットチェック
//            if($this->formatCheck($readBody[$i]['YUBIN'],"/^[0-9\-]{0,13}$/"))
//            {
//                $flg = -1;
//            }
//            else if($this->formatCheck($readBody[$i]['TEL'],"/^[0-9\-]{0,13}$/"))
//            {
//                $flg = -1;
//            }
//            else if($this->formatCheck($readBody[$i]['FAX'],"/^[0-9\-]{0,13}$/"))
//            {
//                $flg = -1;
//            }
//            else if($this->formatCheck($readBody[$i]['EMAIL1'],"/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/"))
//            {
//                $flg = -1;
//            }
//            else if($this->formatCheck($readBody[$i]['EMAIL2'],"/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/"))
//            {
//                $flg = -1;
//            }
            
            if($flg === -1)
            {
                $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
                //トランザクションコミットまたはロールバック
                commitTransaction($flg,$con);
            }
            else
            {
                //kokyakumasterに登録
                $sql = "INSERT INTO kokyakumaster (KOKYAKUMEI,TANTO1,TANTO2,UPDATETIME,UPDATEUSER) "
                        . "VALUE ('" . $readBody[$i]['KOKYAKUMEI'] . "','" . $readBody[$i]['TANTO1'] . "','" . $readBody[$i]['TANTO2'] . "',NOW()," . $_SESSION['userid'] . ");";
                $result = $con->query($sql);
                addSousarireki($filename,STEP_INSERT,$sql,$con);
            }
            
        }
        
        //トランザクションコミットまたはロールバック
	commitTransaction($result,$con);
        
        $filename = 'KOKYAKUMASTER_2';
        $this->PageJump($filename, $_SESSION['userid'], 1, "", "");
    }
}

class MitsumoriCsvImportExecute extends ImportCsvExecute
{
    
    /**
     * エラーチェック処理
     * 
     */
    public function executeSQL()
    {
        $filename = $this->prContainer->pbFileName;
        $readBody = $this->importCSV();
        
        for($i = 0; $i < count($this->a); $i++)
        {
            $keta[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['form1_length'];
            $param[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['column'];
            $blank[$i] = $this->prContainer->pbParamSetting[$this->a[$i]]['isnotnull'];
        }
    
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        //------------------------//
        //          変数          //
        //------------------------//
        $flg = 0;
        
        //------------------------//
        //       チェック処理     //
        //------------------------//
        for($i = 0; $i < count($readBody); $i++) 
        {

            for($j = 0; $j < count($param); $j++)
            {
                if($blank[$j] === "1")
                {
                    //ブランクチェック
                    if($this->blankCheck($readBody[$i][$param[$j]]))
                    {
                        $flg = -1;
                    }
                }
                //桁数のチェック
                if($this->ketaCheck($readBody[$i][$param[$j]],$keta[$j]))
                {
                    $flg = -1;
                }
            }
            
            //フォーマットチェック
            if($this->formatCheck($readBody[$i]['TANKA'],"/^[0-9]{1,10}$/"))
            {
                $flg = -1;
            }
            
            //存在チェック
            if($this->sonzaiCheck($readBody[$i]['ZEIRITSU'],$con,"TAX"))
            {
                $flg = -1;
            }
            
            if($flg === -1)
            {
                $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
                //トランザクションコミットまたはロールバック
                commitTransaction($flg,$con);
            }
            else
            {
                //hinmokumasterに登録
                $sql = "INSERT INTO hinmokumaster (HINMOKUMEI,TANNI,TANKA,ZEIRITSU,BIKO,UPDATETIME,UPDATEUSER) "
                        . "VALUE ('" . $readBody[$i]['HINMOKUMEI'] . "','" . $readBody[$i]['TANNI'] . "','" . $readBody[$i]['TANKA'] . "','" . $readBody[$i]['ZEIRITSU'] . "','" . $readBody[$i]['BIKO'] . "',NOW()," . $_SESSION['userid'] . ");";
                $result = $con->query($sql);
                addSousarireki($filename,STEP_INSERT,$sql,$con);
            }
        }
        
        //トランザクションコミットまたはロールバック
	commitTransaction($result,$con);
        
        $filename = 'HINMOKUMASTER_2';
        $this->PageJump($filename, $_SESSION['userid'], 1, "", "");
    }
}