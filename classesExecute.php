<?php

require_once("classesBase.php");
/**
 * ビジネスロジック層用のクラス
 * データ処理
 */
class BaseLogicExecuter extends BaseObject
{

	/**
	 * executeSQL
	 * データ操作の実行
	 */
	public function executeSQL()
	{
                // fileneme、idを初期化
                $fileneme = "";
                $id = "";
            
		//処理判定変数
		$step = $this->prContainer->pbStep;
		
		//DB接続、トランザクション開始
		$con = beginTransaction();
		
		if($step == STEP_INSERT)//データ登録
		{
			$result = insert($this->prContainer->pbFileName, $this->prContainer->pbInputContent,$con);
		}
		else if($step == STEP_EDIT)//データ編集
		{
			$edit = $this->prContainer->pbInputContent;
			//$tablenum = $this->prFormIni['use_maintable_num'];
			$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
			if(isset($_SESSION['list']['uniqe']))
			{
				$edit['uniqe'] = $_SESSION['list']['uniqe'];
			}
			$edit[$tablenum.'ID'] = $this->prContainer->pbListId;
			
			$result = update($this->prContainer->pbFileName, $edit,$con);
		}
		else if($step == STEP_DELETE)//データ削除
		{
			$delete = $this->prContainer->pbInputContent;
			$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
			if(isset($_SESSION['list']['uniqe']))
			{	
				$delete['uniqe'] = $_SESSION['list']['uniqe'];
			}
			$delete[$tablenum.'ID'] = $this->prContainer->pbListId;
			//$result = delete($this->prContainer->pbFileName, $delete,$_SESSION['data'],$con);
			$result = delete($this->prContainer->pbFileName, $delete,'',$con);
		}
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		//セッション情報など初期化
		//unsetSessionParam();
		$history = $_SESSION['history'];
		$count = count($history);
		for($i = $count-1; $i >= 0 ; $i-- )
		{
			$filearray = explode("_",$history[$i]);
			//案件登録,更新時
			if($filearray[0] == "ANKENINFO")
			{
				if($step == 2)
				{
					$filename = "ANKENSHOW_1";
					$step = 2;
					$id = $this->prContainer->pbListId;
				}
				else
				{
					$filename = $filearray[0]."_2";
					$step = STEP_NONE;
					$id = STEP_NONE;
				}
				
				break;
			}
			//見積登録,更新時
			if($filearray[0] == "MITSUMORIINFO")
			{
				$filename = "ANKENSHOW_1";
				$step = 2;
				$id = $this->prContainer->pbInputContent['form_mmhANKID_0'];
				break;
			}	
			//請求登録,更新時
			if($filearray[0] == "SEIKYUINFO")
			{
				$filename = "ANKENSHOW_1";
				$step = 2;
				$id = $this->prContainer->pbInputContent['form_sehANKID_0'];
				break;
			}
			//主にマスタ系の処理実行時
			if($filearray[1] == "2")
			{
				$filename = $history[$i];
				$step = STEP_NONE;
				$id = STEP_NONE;
                                
//                               if(isset($_SESSION['search']['flg']))
//                               {
//                                    $_SESSION['search']['flg'] = 1;
//                               }
                                
				break;
			}
                        //主にマスタ系の処理実行時
			if($filearray[0] === "JISYAMASTER")
			{
				$filename = "TOP_5";
				$step = STEP_NONE;
				$id = STEP_NONE;
				break;
			}
		}
                
		$this->PageJump($filename, $id, $step, "", "");

	}
	
	function refreshSession($filename, $id, $step)
	{
	   $keep['USRID'] = $_SESSION['USRID'];
	   $keep['USERMEI'] = $_SESSION['USERMEI'];
	   $keep['KENGEN'] = $_SESSION['KENGEN'];
	   $keep['HYOJIMEI'] = $_SESSION['HYOJIMEI'];
	   $keep['STAMPNAME'] = $_SESSION['STAMPNAME'];
	   $keep['PSUKEY'] = $_SESSION['PSUKEY'];
	   $keep['userid'] = $_SESSION['userid'];
           // 検索セッション保持
           if(isset($_SESSION['search']))
           {
               $keep['search'] = $_SESSION['search'];
           }
	   //SESSION初期化
	   $_SESSION = array();
	   $_SESSION = $keep;
	   $_SESSION['filename'] = $filename;
	   $_SESSION['step'] = $step;
	   $_SESSION['list'] = array();
	   $_SESSION['list']['id'] = $id;
          
	}
   /*
     *指定のページへ呼ばせる関数
     *  
   */
   function PageJump($filename,$id,$step,$Content,$secondContent)
   {
	   //item.iniから保存すべきSESSIONの項目を抽出し変数keepに保存
	   $this->refreshSession($filename, $id, $step);

	   $url = "";
	   //見積の入力値
	   if($Content != "")
	   {
		   $_SESSION['Content'] = $Content;
	   }
	   if($secondContent != "")
	   {
		   $_SESSION['SecondContent'] = $secondContent;
	   }
	   //指定IDが入力されていたらURLに追加
	   if(isset($id) && isset($filename))
	   {
			$url = "?".$filename."&edit_list_id=".$id;
			if($filename == "MITSUMORIHOSOKU_1" )
			{
				$url .= "&MMHID=".$this->prContainer->pbInputContent['form_mmhMMHID_0'];
			}   
	   }

	   header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			   .$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/main.php$url");

	   exit();	
   }
   
}


/**
 * 見積入力処理
 * 
 * 
 */
class MitsumoriInfoExecuter extends BaseLogicExecuter
{
	/**
	 * DB更新処理
	 * 
	 */
	public function executeSQL()
	{
		
		$step = $this->prContainer->pbStep;
		//DB接続、トランザクション開始
		$con = beginTransaction();
		if($step == STEP_INSERT)//データ登録
		{
			
			//案件IDを見て
			if(isset($this->prContainer->pbInputContent['form_mmhANKID_0']))
			{	
				if( $this->prContainer->pbInputContent['form_mmhANKID_0'] === '' )
				{

					//案件が未登録状態であれば登録を行う
					$post_ank = array();
					$post_ank['form_ankANKUCODE_0'] = '';
					$post_ank['form_ankANKENMEI_0'] = $this->prContainer->pbInputContent['form_mmhKENMEI_0'];
					$post_ank['form_ankRYAKUMEI_0'] = '';
					$post_ank['form_ankUSRID_0'] = $_SESSION['userid'];
					$post_ank['form_ankKYAID_0'] = '';
					$post_ank['form_ankTANTOMEI_0'] = $this->prContainer->pbInputContent['form_mmhKOKYAKUTANTO_0'];	
					$post_ank['form_ankSTATUS_0'] = 2;
					$post_ank['form_ankGAIYO_0'] = '';
					$post_ank['form_ankJISSIBI_0'] = '';
					$post_ank['form_ankSANKOANKID_0'] = '';
					$post_ank['form_ankANKID_0'] = '';
					//案件情報作成
					$result = insert('ANKENINFO_1', $post_ank,$con);

					//紐付けるためのユニークキーを取得
					$ucode = $post_ank['form_ankANKUCODE_0'];
					$unique_sql = 'SELECT * FROM ankeninfo WHERE ANKUCODE='.$ucode;
					//SQL実行
					//$con = dbconect();
					$unique_result = $con->query($unique_sql);				// クエリ発行
					//終端までループ
					while($result_row = $unique_result->fetch_array(MYSQLI_ASSOC))
					{
						$this->prContainer->pbInputContent['form_mmhANKID_0'] = $result_row['ANKID'];
						break;
					}

					//トランザクションコミットまたはロールバック
					//commitTransaction($result,$con);
					$unique_result->close();
				}
			}
			
			//ステータスを更新する案件IDを取得
			$use_main = $this->prContainer->pbPageSetting['use_maintable_num'];
			$key_column = 'form_'.$use_main.'ANKID_0';
			$id = $this->prContainer->pbInputContent[$key_column];
			$value = loadDBRecord("ank", $id);
			foreach($value as $key =>$keyvalue)
			{
				$coloum = 'form_ank'.$key.'_0';
				$edit[$coloum] = $keyvalue;
			}
			
			if($use_main === "mmh")
			{	
				//見積時
				$edit['form_ankSTATUS_0'] = "2";
			}
			else if($use_main === "seh")
			{
				//請求時
				$edit['form_ankSTATUS_0'] = "4";
			}	
			//見積、請求登録
			$result = insert($this->prContainer->pbFileName, $this->prContainer->pbInputContent,$con);
			//ステータス変更
			$result = update("ANKENSTATUS_1", $edit,$con);
			
			
		}
		else if($step == STEP_EDIT)//データ編集
		{
			$edit = $this->prContainer->pbInputContent;
			//$tablenum = $this->prFormIni['use_maintable_num'];
			$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
			if(isset($_SESSION['list']['uniqe']))
			{
				$edit['uniqe'] = $_SESSION['list']['uniqe'];
			}	
			//$edit[$tablenum.'ID'] = $_SESSION['list']['id'];
			$edit[$tablenum.'ID'] = $this->prContainer->pbListId;
			
			$result = update($this->prContainer->pbFileName, $edit,$con);
		}
		else if($step == STEP_DELETE)//データ削除
		{
			$delete = $this->prContainer->pbInputContent;
			//$tablenum = $this->prFormIni['use_maintable_num'];
			$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
			if(isset($_SESSION['list']['uniqe']))
			{	
				$delete['uniqe'] = $_SESSION['list']['uniqe'];
			}
			//$delete[$tablenum.'ID'] = $_SESSION['list']['id'];
			$delete[$tablenum.'ID'] = $this->prContainer->pbListId;
			//$result = delete($this->prContainer->pbFileName, $delete,$_SESSION['data'],$con);
                        $result = delete($this->prContainer->pbFileName, $delete,"",$con);
			
		}
		
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		
		//historyを見て遷移するページを判定
		$history = $_SESSION['history'];
		$count = count($history);
		for($i = $count-1; $i >= 0 ; $i-- )
		{
			$filearray = explode("_",$history[$i]);
			//案件登録,更新時
			if($filearray[0] == "ANKENINFO")
			{
				if($step == 2)
				{
					$filename = "ANKENSHOW_1";
					$step = 2;
					$id = $this->prContainer->pbListId;
				}
				else
				{
					$filename = $filearray[0]."_2";
					$step = STEP_NONE;
					$id = STEP_NONE;
				}
				
				break;
			}
			//見積登録,更新時
			if($filearray[0] == "MITSUMORIINFO")
			{
				$filename = "ANKENSHOW_1";
				$step = 2;
				$id = $this->prContainer->pbInputContent['form_mmhANKID_0'];
				break;
			}	
			//請求登録,更新時
			if($filearray[0] == "SEIKYUINFO")
			{
				$filename = "ANKENSHOW_1";
				$step = 2;
				$id = $this->prContainer->pbInputContent['form_sehANKID_0'];
				break;
			}
			//主にマスタ系の処理実行時
			if($filearray[1] == "2")
			{
				$filename = $history[$i];
				$step = STEP_NONE;
				$id = STEP_NONE;
				break;
			}
		}
		$this->PageJump($filename, $id, $step, "", "");
	}
}

/**
 * 操作履歴削除用のExecuter
 * 
 */
class DeleteRirekiExecuter extends BaseLogicExecuter
{
	
	/**
	 * 処理
	 * ここでは操作履歴を指定日時以前の条件で削除する
	 */
	public function executeSQL()
	{
		//指定日時
		$ssrUPDATETIME = $this->prContainer->pbInputContent['form_ssrUPDATETIME_0'];

		//DB接続、トランザクション開始
		$con = beginTransaction();
		
		//請求テーブルから情報を取得
		$sql = "DELETE FROM sousarireki WHERE UPDATETIME<'$ssrUPDATETIME'";
		$result = $con->query($sql) or ($judge = true);																		// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge =false;
		}
		////////////////////操作履歴///////////////////////
		addSousarireki($this->prContainer->pbFileName, STEP_DELETE, $sql, $con);
		////////////////////操作履歴///////////////////////
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		
		//指定ページへ遷移
		$this->PageJump( 'TOP_5', '', 0, '' );
	}
}

/**
 * CSV取込用のExecuter
 * 
 */
class ImportCsvExecute extends BaseLogicExecuter
{
    
    public $a;
  
    public function importCSV()
    {
        foreach($_FILES as $form => $value)
        {
            if ($value['size'] != 0) {
                $file_array = explode('.', $value['name']);
                $extention = $file_array[(count($file_array) - 1)];
                $tempfile = './temp/';
                $tempfile .= "tempfileinsert.txt";
                move_uploaded_file($value['tmp_name'], $tempfile);
            }
        }
        
        //------------------------//
        //          定数          //
        //------------------------//
        $FilePath = "temp/tempfileinsert.txt";
        
        //------------------------//
        //          変数          //
        //------------------------//
        $countrow = 0;
        $readBody = array();											//読み込み配列
        $columns = array();
        $column = array();
        $param = array();
        $item = "";
        
        //------------------------//
        //        取込処理        //
        //------------------------//
        
        $columns = $this->prContainer->pbPageSetting['page_columns'];
        $column = explode(',',$columns);
        $this->a = $column;
        
        for($i = 0; $i < count($column); $i++)
        {
            $param[$i] = $this->prContainer->pbParamSetting[$column[$i]]['column'];
        }
        
        //取込データを読み込み
        $file = fopen($FilePath, "r");
        if($file)
        {
            while($line = fgets($file))
            {
                $strsub = explode(",", trim($line)); //カンマ区切りのデータを取得
                
                //個数チェック
                if (count($strsub) !== count($column)) {
                    $filename = $this->prContainer->pbFileName;
                    $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
                }

                for($i = 0; $i < count($column); $i++)
                {
                    $item = mb_convert_encoding( $strsub[$i], "UTF-8","SJIS");
                    $readBody[$countrow][$param[$i]] = $item;
                }
                
                $countrow++;
            }
        }
        fclose($file);
        return $readBody;
    }
    
    /**
     * ブランクチェック
     * 
     */
    function blankCheck ($value)
    {
        if($value === "")
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 桁数チェック
     * 
     */
    function ketaCheck ($value,$count)
    {
        if(mb_strlen($value) > $count)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * フォーマットのチェック
     * 
     */
    function formatCheck ($value,$format)
    {
        $check = true;
        
        if($value === "")
        {
            $check = false;
        }
         else 
        {
            if(preg_match($format, $value))
            {
                $check = false;
            }
        }
        
        return $check;
    }
    
    /**
     * 存在チェック
     * 
     */
    function sonzaiCheck($value,$con,$hkey)
    {  
        $sql = "SELECT * FROM hanyoumaster WHERE HKEY='" . $hkey . "' AND HVALUE = '" . $value . "';";
        $result = $con->query($sql);
        $rownums = $result->num_rows;
        
        if($rownums === 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
