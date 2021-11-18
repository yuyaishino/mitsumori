<?php

/**
 * 印刷用Pageクラス
 *  見積
 */
class PrintPage extends BasePage {

    /**
     * 見積コード/請求コード
     *  string 
     */
    protected $Code;
    /**
     * 見積宛、請求宛
     * string
     */
    protected $Company;
    /**
     * 顧客担当者
     * @var type 
     */
    protected $Customer;
    /**
     * 見積日、請求日
     * @var type 
     */
    protected $LimitDate;
    /**
     * ユーザー情報
     * @var type 
     */
    protected $UserValue;
    /**
     * 期限
     * @var type 
     */
    protected $TimeLimit;
    /**
     * 備考
     * @var type 
     */
    protected $Biko;
    /**
     * 税抜き合計
     * @var type 
     */
    protected $kingakukei;
    /**
     * 税込み総合計
     * @var type 
     */
    protected $total;
    /**
     * 税金8%
     * @var type 
     */
    protected $tax8;
    /**
     * 税金10%
     * @var type 
     */
    protected $tax10;
    /**
     * 振込先
     * @var type 
     */
    protected $Transfer;
    /**
     * 画面名
     * @var type 
     */
    protected $filename;
    /**
     * 見出し
     * @var type 
     */
    protected $HeadTitle;
    

    /**
     * 関数名: makeStylePart
     *   CSS定義文字列(HTML)を作成する関数
     * (基本的にはCSSファイルへのリンクを作成)
     * 
     * @retrun HTML文字列
     */
    function makeStylePart() {
        
        $html = '<link rel="stylesheet" type="text/css" href="./list_css.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./display.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./stamp.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./workresult.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./print.css" media="print">';
//        $html .= '<link rel="stylesheet" type="text/css" href="./workresult.css">';
        return $html;
    }

    /**
     * 関数名: exequtePreHtmlFunc
     *   ページ用のHTMLを出力する前の処理
     */
    public function executePreHtmlFunc() {
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
    function makeScriptPart() {
        //親の処理を呼び出す
        $html = parent::makeScriptPart();
        //必要なHTMLを付け足す
        $html .= '<script src="./js/tabscript.js"></script>';
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
	 * 関数名: makeBoxMenu
	 *   画面左に表示されるメニュー部分のHTML文字列を作成する
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxMenu()
	{
            //画面左側ボタン作成
            $html = '';
//            $html .= '<div class="pkg_contents" id="print">';
//            $html .= '<div  class="main_menu">';
//            $html .= makeAllMenu();
//            $html .= '</div></div>';
            return $html;
    }

    /**
     * 関数名: makeBoxContentTop
     *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
     *   機能名の表示など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentTop() {
        $this->filename = $this->prContainer->pbFileName;
        if (strstr($this->filename, '_5') != false) {
            $this->prTitle = "";
            $html = "";
        }
        $columns = $this->prContainer->pbPageSetting['page_columns'];   //画面設定値
        $columns_array = explode(',', $columns);                        //画面設定値
        $Code = "form_" . $columns_array[0] . "_" . "0";
        //見積,請求コピー画面時、見積書表示選択時
        //GET情報がないためデータ取得する必要あり
        if (!isset($this->prContainer->pbInputContent[$Code])) {
            $this->prContainer->pbInputContent = make_post("", $this->prContainer->pbListId);
        }
        //見積コード/請求コード
        $frm_code = "form_" . $columns_array[0] . "_" . "0";
        $this->Code = $this->prContainer->pbInputContent[$frm_code];
        //見積宛	、請求宛
        $company = "form_" . $columns_array[3] . "_" . "0";
        $this->Company = $this->prContainer->pbInputContent[$company];
        //顧客担当者
        $customer = "form_" . $columns_array[4] . "_" . "0";
        $this->Customer = $this->prContainer->pbInputContent[$customer];
        //見積日、請求日
        $limitDate = "form_" . $columns_array[5] . "_" . "0";
        $this->LimitDate = $this->prContainer->pbInputContent[$limitDate];
        //ログインユーザー情報取得
        $usercolumn = "form_" . $columns_array[6] . "_" . "0";
        if (isset($this->prContainer->pbInputContent[$usercolumn])) {
            $userid = $this->prContainer->pbInputContent[$usercolumn];
        }else{
            $userid = $this->prContainer->pbInputContent['USRID'];
        }
        // ログイン情報取得
        $this->UserValue = loginUserValue($userid);
        // 見積請求期限
        $timeLimit = "form_" . $columns_array[8] . "_" . "0";
        $this->TimeLimit = $this->prContainer->pbInputContent[$timeLimit];
        // 備考
        $biko = "form_" . $columns_array[9] . "_" . "0";
        if(isset($this->prContainer->pbInputContent[$biko])){
            $this->Biko = $this->prContainer->pbInputContent[$biko];
        } else {
            $this->Biko = "";
        }
        // 税抜き金額
        $kingakukei = "form_" . $columns_array[10] . "_" . "0";
        $this->kingakukei = $this->prContainer->pbInputContent[$kingakukei];
        // 税込み金額
        $total = "form_" . $columns_array[11] . "_" . "0";
        $this->total = $this->prContainer->pbInputContent[$total];
        // 税率8%
        $tax8 = "form_" . $columns_array[12] . "_" . "0";
        $this->tax8 = $this->prContainer->pbInputContent[$tax8];
        // 税率10%
        $tax10 = "form_" . $columns_array[13] . "_" . "0";
        $this->tax10 = $this->prContainer->pbInputContent[$tax10];
        // 振込先が記入してあるか確認
        if (isset($columns_array[14])) {
            $transfer = "form_" . $columns_array[14] . "_" . "0";
            $this->Transfer = $this->prContainer->pbInputContent[$transfer];
        }
        
        // 見出し設定
        if($this->filename === "MITSUMORIPRINT_5") {
            $this->HeadTitle = "見積書";
        } else {
            $this->HeadTitle = "請求書";
        }
        //自社情報取得
        if (array_key_exists('SYAMEI', $_SESSION) === false) {
            loadJisyaMaster();
        }
        
        return $html;
    }

    /**
     * 関数名: makeBoxContentMain
     *   メインの機能提供部分のHTML文字列を作成する
     *   リストでは一覧表示、入力では各入力フィールドの構築など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain() {
        
        // 印刷画面作成
        $html = $this->makePrintTopPage($this->LimitDate,$this->Code,$this->Company,$this->HeadTitle);
        $html .= $this->makePrintDetailPage($this->LimitDate, $this->prContainer);
        return $html;
    }

    /**
     * 関数名: makeBoxContentBottom
     *   メインの機能提供部分下部のHTML文字列を作成する
     *   他ページへの遷移ボタンなどを作成
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentBottom() {
        
        $html = "";
        if($this->filename !== "MITSUMORIPRINT_5"){
//            $html .= '<div id="print" class = "print">';
//            $html .= '<label><input type="checkbox" id="copy" checked ><span class="checkbox">控</span></label><br>';
//            $html .= '<label><input type="checkbox" id="delively"><span class="checkbox">納品書</span></label><br>';
//            $html .= '<label><input type="checkbox" id="Confirm"><span class="checkbox">作業実績報告書兼確認書</span></label><br>';
//            $html .= '<input type="button" value="印刷" id="print" class="print"  onClick="window.print()">';
//            $html .= '</div>';
            $html .= '<div id="print" class = "print">';
            $html .= '<table style="margin-left: 3%;margin-bottom: 10%;width: 87%">';
            $html .= '<tbody>';
            $html .= '<tr style="height: 50px">';
            $html .= '<td style="width: 60px"><input type="checkbox" id="copy" checked ></td>';
            $html .= '<td style="font-size: 18px"><label for="copy">控</label></td>';
            $html .= '</tr>';
            $html .= '<tr style="height: 50px">';
            $html .= '<td style="width: 60px"><input type="checkbox" id="delively"></td>';
            $html .= '<td style="font-size: 18px"><label for="delively">納品書</label></td>';
            $html .= '</tr>';
            $html .= '<tr style="height: 50px">';
            $html .= '<td style="width: 60px"><input type="checkbox" id="Confirm"></td>';
            $html .= '<td style="font-size: 18px"><label for="Confirm">作業実績報告書兼<br>確認書</label></td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<input type="button" value="印刷" id="print" class="print"  onClick="window.print()">';
            $html .= '</div>';
            // 控え印刷作成
            $html .= $this->makePrintTopPage($this->LimitDate, $this->Code, $this->Company, $this->HeadTitle, $flg = 2);
            $html .= $this->makePrintDetailPage($this->LimitDate, $this->prContainer, $flg = 2);
            $html .= $this->makePrintTopPage($this->LimitDate, $this->Code, $this->Company, $this->HeadTitle, $flg = 3);
            $html .= $this->makePrintDetailPage($this->LimitDate, $this->prContainer, $flg = 3);
            $html .= $this->makePrintConfirmPage($this->LimitDate,$this->Company,$this->prContainer);
        } else {
            $html .= '<div id="print" class = "print">';
            $html .= '<input type="button" value="印刷" id="print" class="print"  onClick="window.print()">';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * 関数名: makePrintMainPage
     * 印刷画面上部作成
     * 
     * @retrun HTML文字列
     */
    function makePrintTopPage($datetitle, $code, $mitsumoriAte,$headtitle,$flg = 1) {
         
        // 控えか判定
        if($flg === 2){
            $id = "id='copyprint'";
            $class = "printpage2";
        }else if($flg === 3){
            $id = "id='delivelyprint'";
            $class = "printpage3 dispNone";
        }else{
            $id="";
            $class = "printpage";
        }

        $print = "<div $id class='$class'>";
        $print .= "<div class='top-part'>";
        //日付分解
        $datearray = explode('/', $datetitle);
        //配列になっていなかったら
        if (count($datearray) != 3) {
            $datearray = explode('-', $datetitle);
        }
        
        /* 日付、請求No */
        // 控えか判定
        $print .= "<div class='firstDateBox'>";
        $print .= "<table class='date'>";
        $print .= "<tbody>";
        $print .= "<tr>";
        $print .= "<td>日付:</td>";
        $print .= "<td>$datearray[0]年$datearray[1]月$datearray[2]日</td>";
        $print .= "</tr>";
        $print .= "<tr>";
        if($flg === 1 || $flg === 2)
        {
            $print .= "<td>$headtitle";
            $print .= "No:</td>";
        } else if($flg === 3) {
            $print .= "<td>納品書No:</td>";
        }
        if(substr($code, 0, 1) == "'")
        {
            $code = trim($code, "'");
        }
        $print .= "<td>$code</td>";
        $print .= "</tr>";
        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "</div>";

        //見出し
        if($flg === 2){
            // 控え記入あり
            $print .= "<h2>$headtitle</h2>";
            $print .= "<p class='hikae'>（控）</p>";
        }else if($flg === 3){
            $print .= "<h2>納品書</h2>";
        }else{
            $print .= "<h2>$headtitle</h2>";
        }

        ///件名や金額
        $print .= "<div class ='second-part'>";
        //企業名
        $print .= "<div class='kigyo'>";
        $print .= "<span>$mitsumoriAte 　御中</span>";
        $print .= "</div>";
        /* 固定部分 */
//        $print .= "<div class='kotei'>";
//        $print .= "<span>御中</span>";
//        $print .= "</div>";

        //金額、有効期限、自社情報、印鑑部分の枠
        $print .= "<div class='box'>";
        $print .= "<div class='goukeibox'>";
        
        //有効期限,支払期限
        $messeage = "";
        if ($this->filename ==="MITSUMORIPRINT_5") {
            $print .= "<span style='display: block;margin-top: 2%;'>下記の通りお見積り申し上げます。</span>";
            $Kigen = "有効期限";
            $messeage = "<div class='messege'>交通費等は別途、実費を申し受けます。</div>";
            $Yukoukigen = $this->TimeLimit;
        } else {
            $print .= "<span style='display: block;margin-top: 2%;'>下記の通りご請求申し上げます。</span>";
            $Kigen = "お支払期限";
            if (strstr($this->TimeLimit, '-')) {
                $Yukoukigen = date('Y年n月j日', strtotime($this->TimeLimit));
                $date = 1;
            }
            if (strstr($this->TimeLimit, '/')) {
                $Yukoukigen = date('Y年n月j日', strtotime($this->TimeLimit));
                $date = 1;
            }
        }
        $total = number_format($this->total);
        $print .= "<div class='kingaku'>金額計(税込)　　　&yen $total</div>";
        if ($flg === 1 || $flg === 2){
            $print .= "<div class='kigen'>" . $Kigen . "　　　            " . $Yukoukigen . "</div>";
        }
        
        $print .= $messeage;
        $print .= "</div>";
        
        $print .= "<div class='addressBox'>";
        $print .= "<p class='name'>" . $_SESSION['SYAMEI'] . "</p>";
        $print .= "<p class='address'>〒" . $_SESSION['YUBIN'];
        $print .= "</br>";
        $print .= $_SESSION['JYUSHO1'];
        $print .= "</br>";
        $print .= $_SESSION['JYUSHO2'];
        $print .= "</br>";
        $print .= "TEL：" . $_SESSION['TEL'] . " FAX：" . $_SESSION['FAX'] . "";
        $print .= "</p>";
        $print .= "<p class='tantousya'>担当者：" . $this->UserValue['HYOJIMEI'] . " </p>";
        $print .= "</div>";
        $print .= "<img src='./image/newHANKO.png' class='png'>";
        $print .= "</div>";
        $print .= "</div>";
        $print .= "</div>";
        
        return $print;
    }

    /**
     * 関数名: makePrintDetailPage
     * 印刷画面詳細部作成
     * 
     * @retrun HTML文字列
     */
    function makePrintDetailPage($MitusumoriDate, &$content, $flg = 1) {

        //------------------------//
        //        初期設定        //
        //------------------------//
        require_once ("f_DB.php");
        require_once ("f_Form.php");
        require_once ("f_SQL.php");
        //------------------------//
        //          定数          //
        //------------------------//
        $post = $content->pbInputContent;
        $form_ini = $content->pbFormIni;
        $filename = $content->pbFileName;
        $filename_M = $filename . "_M";                                 //ヘッダ明細画面名
        $columns_M = $form_ini[$filename_M]['page_columns'];            //ヘッダ明細設定値	
        $columns_M_array = explode(',', $columns_M);                    //ヘッダ明細設定値
         //軽減税率用※変数
        $taxmark = "※";
         //日付
        $stampdate = date('Y.n.j', strtotime($MitusumoriDate));
        $stamp01 = '<div class="stamp stamp-approve"><span>' . $stampdate . '</span><span>' . $_SESSION['STAMPNAME'] . '</span></div>'; //承認
        $stamp02 = '<div class="stamp stamp-audit"><span></span><span></span></div>';    //審査
        $stamp03 = '<div class="stamp stamp-write"><span>' . $stampdate . '</span><span>' . $this->UserValue['STAMPNAME'] . '</span></div>'; //担当
        
        //見積、請求コピー画面時、見積表示選択時 明細の入力値を取得
        $hinmei = "form_" . $columns_M_array[1] . "_" . "0" . "_0";
        if (!isset($post[$hinmei])) {
            $use_code = $form_ini[$filename]['use_maintable_num'];
            $post = make_headerpost($filename_M, $content->pbListId, $use_code);
        }
        
        //明細部分
        $print = "<div class='meiaibox' >";
        $print .= "<table class='meisai' border='1' align='center'>";
        $print .= "<tbody>";
        $print .= "<tr class='color'>";
        $print .= "<td width='300' align='center'>品名</td>";
        $print .= "<td width='120' align='center'>単価</td>";
        $print .= "<td width='120' align='center'>数量</td>";
        $print .= "<td width='120' align='center'>金額</td>";
        $print .= "</tr>";

        for ($i = 0; $i < 15; $i++) {
            //品名
            $hinmei = "form_" . $columns_M_array[1] . "_" . "0" . "_" . $i;
            $hinmei = $post[$hinmei];
            //単価
            $tanka = "form_" . $columns_M_array[2] . "_" . "0" . "_" . $i;
            $tanka = $post[$tanka];
            //数量
            $suryo = "form_" . $columns_M_array[3] . "_" . "0" . "_" . $i;
            $suryo = $post[$suryo];
            //単位
            $tani = "form_" . $columns_M_array[4] . "_" . "0" . "_" . $i;
            $tani = $post[$tani];
            //金額
            $money = "form_" . $columns_M_array[5] . "_" . "0" . "_" . $i;
            $money = $post[$money];
            //税率
            $zei = "form_" . $columns_M_array[6] . "_" . "0" . "_" . $i;
            $zei = $post[$zei];

            if (($i % 2) == 1) {
                $id = 'class = "color"';
            } else {
                $id = 'class = "backcolor"';
            }

            $print .= "<tr $id>";
            //品名
            if ($zei == "8") {//税率判定
                //軽減税率の場合　※記入 半角スペース10行
                $print .= "<td height='27' align='left' >$taxmark$hinmei</td>";
            } else {
                $print .= "<td height='27' align='left' >　$hinmei</td>";
            }
            //単価
            if ($tanka != 0) {
                $tanka_print = number_format($tanka);
                $print .= "<td  align='right'>&yen $tanka_print</td>";
            } else {
                $tanka = "";
                $print .= "<td align='right'></td>";
            }
            //数量
            if ($suryo != 0) {
//                $suryo_print = number_format($suryo, 2);
                $suryo_print = $suryo;
                //単位が円なら表示しない、一式なら感じで表示する
                if ($tani == "円") {
                    $print .= "<td  align='center'>$suryo_print</td>";
                } else if ($tani == "一式") {
                    $print .= "<td  align='center'>一式</td>";
                } else {
                    $print .= "<td  align='center'>$suryo_print$tani</td>";
                }
            } else {
                $suryo = "";
                $print .= "<td  align='center'></td>";
            }
            //金額
            if ($money != 0) {
                $money = number_format($money);
                $print .= "<td  align='right'>&yen $money</td>";
            } else {
                $money = "";
                $print .= "<td  align='right'></td>";
            }
            $print .= "</tr>";
        }

        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "<table border='1'  align='center' class='goukei'>";
        $print .= "<tbody>";
        $kingakukei = number_format($this->kingakukei);
        $tax8 = number_format($this->tax8);
        $tax10 = number_format($this->tax10);
        $total = number_format($this->total);
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>小計</td><td class='backcolor' align='right'>&yen $kingakukei</td></tr>";
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>消費税（8％）</td><td class='backcolor' align='right'>&yen $tax8</td></tr>";
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>消費税（10％）</td><td class='backcolor' align='right'>&yen $tax10</td></tr>";
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>総合計</td><td class='backcolor' align='right'>&yen $total</td></tr>";
        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "</div>";
        
        // 見積、請求時の備考
        if ($this->filename === "MITSUMORIPRINT_5" && $flg === 1) {
            $print .= "<div class='goannaiBox'>";
            $print .= "<p class='bikou'>$this->Biko</p>";
            $print .= "</div>";
            $print .= $this->createStamp($stamp01, $stamp03);
        } elseif ($this->filename === "SEIKYUPRINT_5" && $flg === 2) {
            $print .= "<div class='goannaiBox'>";
            $print .= "<p class='bikou'>$this->Transfer</p>";
            $print .= "</div>";
            $print .= $this->createStamp($stamp01, $stamp03);
        } else if ($this->filename === "SEIKYUPRINT_5" && $flg === 1) {
            $print .= "<div class='goannaiBox'>";
            $print .= "<p class='bikou'>$this->Transfer</p>";
            $print .= "</div>";
            $print .= "<div class='bikouBox'><p class='bikou'>$this->Biko</p></div>";
        } else {
            $print .= "<div class='bikouBox'><p class='bikou'>$this->Biko</p></div>";
            $print .= "</div>";
        }
        $print .= "</div>";
        return ($print);
    }
    /**
     * 判子ボックス作成
     * @param type $stamp01
     * @param type $stamp03
     */
    function createStamp($stamp01,$stamp03)
    {
        $print = "";
        $print .= "<div class='kaishahanko' >";
        $print .= "<table class='inkan' border='1' align='right' >";
        $print .= "<tbody>";
        $print .= "<tr>";
        $print .= "<td width='80' align='center'>承認</td>";
        $print .= "<td width='80' align='center'>審査</td>";
        $print .= "<td width='80' align='center'>担当</td>";
        $print .= "</tr>";
        $print .= "<tr>";
        $print .= "<td height='80'>$stamp01</td>"; //承認
        //$print .= "<td>$stamp02</td>";
        $print .= "<td></td>"; //審査
        $print .= "<td>$stamp03</td>"; //担当
        $print .= "</tr>";
        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "</div>";
        $print .= "</div>";
        return $print;
    }
    
    /**
     *  作業結果報告作成
     * @param type $datetitle
     * @param type $mitsumoriAte
     * @param type $content
     * @return string
     */
    function makePrintConfirmPage($datetitle,$mitsumoriAte,&$content)
    {
        //------------------------//
        //        初期設定        //
        //------------------------//
        require_once ("f_DB.php");
        require_once ("f_Form.php");
        require_once ("f_SQL.php");
        //------------------------//
        //          定数          //
        //------------------------//
        $post = $content->pbInputContent;
        $value = $content->pbPageSetting;
        $form_ini = $content->pbFormIni;
        $filename = $content->pbFileName;
        $columns = $value['page_columns'];                              //画面設定値
        $columns_array = explode(',', $columns);                        //画面設定値
        $filename_M = $filename . "_M";                                 //ヘッダ明細画面名
        $columns_M = $form_ini[$filename_M]['page_columns'];            //ヘッダ明細設定値	
        $columns_M_array = explode(',', $columns_M);                    //ヘッダ明細設定値
        //軽減税率用※変数
        $taxmark = "※";
        $date = 0;
        
        //見積、請求コピー画面時、見積表示選択時 明細の入力値を取得
        $hinmei = "form_" . $columns_M_array[1] . "_" . "0" . "_0";
        if (!isset($post[$hinmei])) {
            $userid = $post['USRID'];
            $use_code = $form_ini[$filename]['use_maintable_num'];
            $post = make_headerpost($filename_M, $content->pbListId, $use_code);
        }
        
        $total = number_format($this->total);
        
        $id="id='printConfirm' ";
        $class = "printpage4 dispNone";
        
        $print = "<div $id class='$class'>";
        //日付分解
        $datearray = explode('/', $datetitle);
        //配列になっていなかったら
        if (count($datearray) != 3) {
            $datearray = explode('-', $datetitle);
        }
            //自社情報
        if (array_key_exists('SYAMEI', $_SESSION) === false) {
            loadJisyaMaster();
        }

        /* 日付 */
        $print .= "<table class='hinichi'>";
        $print .= "<tbody>";
        $print .= "<tr>";
        $print .= "<td>$datearray[0]年$datearray[1]月$datearray[2]日</td>";
        $print .= "</tr>";
        $print .= "</tbody>";
        $print .= "</table>";

        /* 見積り宛 */
        $print .= "<div class='atesaki'>";
        $print .= "<div class='kaisya'><span>&thinsp; $mitsumoriAte</span></div>";
        $print .= "<div class='onchu'><span>&emsp; 御中 &emsp; </span></div>";
        $print .= "</div>";

        /* 自社情報 */
        $print .= "<div class='jisyaBox'>";
        $print .= "<p>" . $_SESSION['SYAMEI'] . "</p>";
        $print .= "<p>" . $_SESSION['YAKUSYOKU'] . "　" . $_SESSION['NAME'] . "</p>";
        $print .="<img src='./image/newHANKO.png' class='resultpng'>";
        $print .= "</div>";

        /* 作業実績報告書兼確認書 */
        $print .= "<h3>作業実績報告書兼確認書</h3>";
        $print .= "<div class='aisatsu'>";
        $print .= "<span>&thinsp; 平素は格別のご高配に預かり、厚く御礼申し上げます。ご下命を賜っております<br />下記の弊社作業が終了した事をご報告いたしますので、ご確認願います。</span>";
        $print .= "</div>";
        $print .= "<br />";

        /* 作業実績報告 */
        $print .= "<p class='meisaititle'>作業実績報告</p>";
        $print .= "<center>";
        $print .= "<div class='meisaibubun'>";
        $print .= "<table class='sagyoumeisai'>";
        $print .= "<tr>";
        $print .= "<td>作業形態</td><td>：</td><td>請負</td>";
        $print .= "</tr>";
        $print .= "<tr>";
        $print .= "<td>作業終了日</td><td>：</td><td>$datearray[0]年$datearray[1]月$datearray[2]日</td>";
        $print .= "</tr>";
        $print .= "</table>";
        $print .= "<br />";

        $print .= "<table class='meisaibubun' border='1' align='center'>";
        $print .= "<tbody>";
        $print .= "<tr class='color'>";
        $print .= "<td width='300' align='center'>品名</td>";
        $print .= "<td width='120' align='center'>単価</td>";
        $print .= "<td width='120' align='center'>数量</td>";
        $print .= "<td width='120' align='center'>金額</td>";
        $print .= "</tr>";

        for ($i = 0; $i < 15; $i++) {
                //品名
                $hinmei = "form_" . $columns_M_array[1] . "_" . "0" . "_" . $i;
                $hinmei = $post[$hinmei];
                //単価
                $tanka = "form_" . $columns_M_array[2] . "_" . "0" . "_" . $i;
                $tanka = $post[$tanka];
                //数量
                $suryo = "form_" . $columns_M_array[3] . "_" . "0" . "_" . $i;
                $suryo = $post[$suryo];
                //単位
                $tani = "form_" . $columns_M_array[4] . "_" . "0" . "_" . $i;
                $tani = $post[$tani];
                //金額
                $money = "form_" . $columns_M_array[5] . "_" . "0" . "_" . $i;
                $money = $post[$money];
                //税率
                $zei = "form_" . $columns_M_array[6] . "_" . "0" . "_" . $i;
                $zei = $post[$zei];

                if (($i % 2) == 1) {
                    $id = 'class = "color"';
                } else {
                    $id = 'class = "backcolor"';
                }

                $print .= "<tr $id>";
                //品名
                if ($zei == "8") {//税率判定
                    //軽減税率の場合　※記入 半角スペース10行
                    $print .= "<td height='27' align='left' >$taxmark$hinmei</td>";
                } else {
                    $print .= "<td height='27' align='left' >　$hinmei</td>";
                }
                //単価
                if ($tanka != 0) {
                    $tanka_print = number_format($tanka);
                    $print .= "<td  align='right'>&yen $tanka_print</td>";
                } else {
                    $tanka = "";
                    $print .= "<td  align='right'></td>";
                }
                //数量
                if ($suryo != 0) {
                    $suryo_print = $suryo;
                    //単位が円なら表示しない、一式なら感じで表示する
                    if ($tani == "円") {
                        $print .= "<td  align='center'>$suryo_print</td>";
                    } else if ($tani == "一式") {
                        $print .= "<td  align='center'>一式</td>";
                    } else {
                        $print .= "<td  align='center'>$suryo_print$tani</td>";
                    }
                } else {
                    $suryo = "";
                    $print .= "<td  align='center'></td>";
                }
                //金額
                if ($money != 0) {
                    $money = number_format($money);
                    $print .= "<td  align='right'>&yen $money</td>";
                } else {
                    $money = "";
                    $print .= "<td  align='right'></td>";
                }
                $print .= "</tr>";
            }

            $print .= "</tbody>";
            $print .= "<tbody>";
            $kingakukei = number_format($this->kingakukei);
            $tax8 = number_format($this->tax8);
            $tax10 = number_format($this->tax10);
            $print .= "<tr class='frame'>";
            $print .= "<td colspan='3' height='30' align='center' class='color'>売上額（税抜き）</td>";
            $print .= "<td class='backcolor' align='right'>&yen $kingakukei</td></tr>";
            $print .= "</tr>";
            $print .= "<tr class='frame'>";
            $print .= "<td colspan='3' height='30' align='center' class='color'>消費税（8％）</td>";
            $print .= "<td class='backcolor' align='right'>&yen $tax8</td></tr>";
            $print .= "</tr>";
            $print .= "<tr class='frame'>";
            $print .= "<td colspan='3' height='30' align='center' class='color'>消費税（10％）</td>";
            $print .= "<td class='backcolor' align='right'>&yen $tax10</td></tr>";
            $print .= "</tr>";
            $print .= "<tr class='frame'>";
            $print .= "<td colspan='3' height='30' align='center' class='color'>売上額（税込）合計</td>";
            $print .= "<td class='backcolor' align='right'>&yen $total</td></tr>";
            $print .= "</tr>";
            $print .= "</tbody>";
            $print .= "</table>";

            /* 作業実績確認 */
            $print .= "<div class='kakunin'>";
            $print .= "<p align='left'>作業実績確認</p>";
            $print .= "<table class='kakunin'>";
            $print .= "<tr>";
            $print .= "<td>確認日</td><td>：</td><td>　　　　　年　　　月　　　日</td>";
            $print .= "</tr>";
            $print .= "<tr>";
            $print .= "<td>確認者</td><td>：</td><td>$mitsumoriAte</td>";
            $print .= "</tr>";
            $print .= "</table>";
            $print .= "<hr size='1px' width='50%' color='black'><p class='inn'>印</p>";
            $print .= "</div>";
            $print .= "</div>";
            $print .= "</center>";
            
            return $print;
    }
}
