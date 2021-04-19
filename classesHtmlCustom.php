<?php

require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");

class HosokuInputPage extends ListInputPage
{
	
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
		// 読取指定
		if($this->prContainer->pbPageSetting['form_type'] !== '2')
		{
			//通常は更新ボタンを表示
			$html = '<div class = "center">
				<input type="submit" name = "Comp" value = "登録" class="free">';
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

		$html .='</div>';
		$html .= '</form>';
		return $html;
	}
	
	
}

class csvImport extends InsertPage
{
    /**
     * 関数名: makeBoxContentBottom
     *   CSV取込画面上部のHTML文字列を作成する
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain()
    {
        $html = '<form name ="fileinsert" action="main.php" method="post" enctype="multipart/form-data" 
				onsubmit = "return check();">';
        $html .= '<div style="margin-top:1%;margin-left:12%" >';
        $html .= '<input type="file" id="csvSelect" name="sansyo" value="" />';
        $html .= '<br>';
        
        if($_SESSION['filename'] == 'USERCSVIMPORT_1')
        {
            $html .= '<p>ユーザーの取込情報は、<br>ユーザー名,パスワード,権限,表示名,印鑑用名称,識別コードの形式でCSVを作成してください。</p>';            
        }
        else if($_SESSION['filename'] == 'KOKYAKUCSVIMPORT_1')
        {
            $html .= '<p>顧客の取込情報は、<br>顧客名,担当者1,担当者2の形式でCSVを作成してください。</p>';
        }
        else if($_SESSION['filename'] == 'MITSUMORICSVIMPORT_1')
        {
            $html .= '<p>見積品目の取込情報は、<br>品目名,単位,単価,消費税率,備考の形式でCSVを作成してください。</p>';
        }

        
        $content = $this->prContainer->pbInputContent;
        if($content == "error")
        {
            $html .= '<a class = "error">CSVを取り込めませんでした。</a>';
        }
        $html .= '<input type="hidden" name = "step" value = "1" class="free">';
        return $html;
    }
    
    /**
     * 関数名: makeBoxContentBottom
     *   CSV取込画面下部のHTML文字列を作成する
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentBottom() 
    {
        
        $html = '<div class = "center"><input type="button" name = "Comp" value = "登録" class="free" style="margin-top:2%;margin-left:-15%" onclick="csvCheck()" >';
        $html .= '</form>';
        
        return $html;
    }
}

/**
 * 見積、請求コピーページ作成
 * 
 */
class CopyHtmlPage extends ListPage {
    
    
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
                // 案件IDセット
                if(isset($this->prContainer->pbInputContent['ANKID']))
                {
                    $html .= '<input type="hidden" name = "ANKID" value = "'.$this->prContainer->pbInputContent['ANKID'].'" class="free">';
                }
                
                //検索項目表示
		$html .= $form;
                
                //--2019/06/06追加　filename取得　
                $filename = $this->prContainer->pbFileName;        
                $html .='<input type="hidden" id="clear_'.$filename.'" value = "'.$this->prContainer->pbPageSetting['sech_form_num'].'" >';
		$html .='</fieldset></td><td valign="bottom"><input type="submit" name="serch_'.$filename.'" value = "表示" class="free" ></td>';
                $html .='</fieldset></td><td valign="bottom"><input type="button" value = "クリア" class="free" onclick="clearSearch(\''.$filename.'\')"></td></tr></table>';
		$html .= $list;
		$html .= '</form>';
                
		return $html;
	}
}
        