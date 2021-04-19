<?php

/**
 * 遷移先の判定を行い、Pageオブジェクトを生成する
 */
class PageFactory
{
	protected static $factory;
	public $pbFormIni;
	/**
	 * コンストラクタ
	 */
	protected function __construct()
	{
		$this->pbFormIni = parse_ini_file('./ini/form.ini', true);
		require_once("classesPageContainer.php");
		require_once("classesExecute.php");
	}
	
   /**
    * インスタンス生成と取得用関数
    * 
     * @return PageFactoryインスタンス
    */
	public static function getInstance()
	{
		if(!isset(self::$factory))
		{
			self::$factory = new PageFactory();
		}
		return self::$factory;
	}
	
    /**
    * Pageオブジェクトの生成
    * 
    * @param string $filename ページID指定文字列
    * @return BasePage
    */
	public function createPage($filename,$container)
	{
		$page = null;
		$pre_url = explode('_',$filename);
		
		//画面判定変数
		$step = $container->pbStep;
		
		//--↓↓↓↓↓ワンオフの特殊ページ↓↓↓↓↓--
		if($filename === 'ANKENSTATUS_1')
		{
			$page = new StatusChangePage($container);
		}
		elseif($filename === 'SEIKYUNYUKIN_2')
		{
			$page = new SeikyuNyukinListPage($container);
		}
		elseif($filename === 'NYUKIN_1')//入金
		{
			$page = new ListInputPage($container);
		}
		elseif($filename === 'MITSUMORIHOSOKU_1' || $filename === 'SEIKYUHOSOKU_1')
		{
			$page = new HosokuInputPage($container);
		}	
		elseif($filename === 'URIAGEPRINT_6')//売上管理表
		{
			$page = new UriageListCondisionPage($container);
		}
		elseif($filename === 'NYUKINPRINT_6')//入金管理表
		{
			$page = new NyukinListCondisionPage($container);
		}
		elseif($filename === 'TOP_5') //入金管理表
		{
			$page = new TopPage($container);
		}
		elseif($filename ==='MITSUMORICOPY_9')
		{
			//見積コピー時の選択した見積の内容を取得
			$page = new CopyPage($container);
		}	
		elseif($filename ==='ANKMITSUMORI_2')
		{
			//案件情報の見積タブ
			$page = new AnkenMitsumoriTabPage($container);
		}	
		elseif($filename ==='ANKSEIKYU_2')
		{
			//案件情報の請求タブ
			$page = new AnkenSeikyuTabPage($container);
		}	
		elseif($filename ==='SAISINANKEN_2')
		{
			//TOPの最新案件
			$page = new SaishinTabPage($container);
		}	
		elseif($filename ==='MITSUMORIINFO_2')
		{
			//TOPの見積情報タブ
			$page = new MitsumoriTabPage($container);
		}
		elseif($filename ==='MITSUMORIINFO_1' && $step == 1 || $filename ==='SEIKYUINFO_1' && $step == 1)
		{
			if($container->pbPageCheck === 'InsertCheck')
			{
				$page = new KeiriInsertCheck($container);
			}
			else
			{
				//見積コピー,請求コピーボタン作成
				$page = new KeiriInsert($container);
			}	
		}
		elseif($filename === 'MITSUMORIHOSOKU_2' || $filename === 'SEIKYUHOSOKU_2')
		{
			$page = new HosokuListPage($container);
		}
                elseif($filename === 'MITSUMORICOPY_2' || $filename === 'SEIKYUCOPY_2')
		{
			$page = new CopyHtmlPage($container);
		}
		elseif($filename === 'MITSUMORICOPY_2_M' || $filename === 'SEIKYUCOPY_2_M')
		{
			$page = new CopyListPage($container);
		}
                else if($filename === 'USERCSVIMPORT_1' || $filename === 'KOKYAKUCSVIMPORT_1' || $filename === 'MITSUMORICSVIMPORT_1')
                {
                        $page = new csvImport($container);
                }
		if($page !== null)
		{
			return $page;
		}
		//--↑↑↑↑↑ワンオフの特殊ページ↑↑↑↑↑--
		
		//汎用ページ
		if($pre_url[1] === '1')//登録、編集
		{
			if($step == STEP_INSERT)//データ登録
			{
				if($container->pbPageCheck === 'InsertCheck')
				{
					$page = new InsertCheckPage($container);
				}
				else
				{
					$page = new InsertPage($container);
				}
			}
			else if($step == STEP_EDIT)//データ編集
			{
				if($container->pbPageCheck === 'EditCheck')
				{	
					if($filename ==='USERMASTER_1' ){
						$page = new UserMasterCheckPage($container);
					}
					else{
						$page = new EditCheckPage($container);
					}
				}
				else
				{
					if($filename ==='USERMASTER_1' ){
						$page = new UserMasterPage($container);
					}
					else{
						$page = new EditPage($container);
					}
				}
			}
			else if($step == STEP_DELETE)//データ削除
			{
				$page = new DeletePage($container);
			}
		}
		else if($pre_url[1] === '2')//リスト
		{
			$page = new ListPage($container);
		}
		else if($pre_url[1] === '3')//編集のみ
		{
			/*if($container->pbPageCheck === 'Execute')//データ処理
			{
				$page = new BaseLogicExecuter($container);
			}
			else
			{*/
				if($container->pbPageCheck === 'EditCheck')
					{	
						$page = new EditCheckPage($container);
					}
					else
					{
						$page = new EditPage($container);
					}
			//}
			
		}	
		else if($pre_url[1] === '5')//印刷
		{
			if($filename === 'MITSUMORIPRINT_5' || $filename === 'SEIKYUPRINT_5')
			{
				$page = new PrintPage($container);
                        }
			else if($filename === 'URIAGEPRINT_5')
			{
				$page = new UriageListPrintPage($container);
			}
			else if($filename === 'NYUKINPRINT_5')
			{
				$page = new NyukinListPrintPage($container);
			}
			
		}
		else if($pre_url[1] === '6')//条件指定
		{
			$page = new CondisionPage($container);
		}
		else
		{
			$page = new TopPage($container);			
		}
		
		return $page;
	}
	
	/**
    * データ処理オブジェクトの生成
    * 
    * @param string $filename
    * @return BaseLogicExecuter
    */
	public function createExecuter($filename,$container)
	{
		$executer = null;
		
		
		if($container->pbPageCheck === 'Execute')//データ処理
		{
			//--特殊処理--//
			if($filename === 'ANKENSTATUS_1')
			{
				$executer = new StatusUpdate($container);
			}
			if($filename === 'NYUKIN_1')
			{
				$executer = new NyukinExecuter($container);
			}
			if($filename === 'MITSUMORIINFO_1' || $filename === 'SEIKYUINFO_1')
			{
				$executer = new MitsumoriInfoExecuter($container);
			}
			if($filename === 'MITSUMORIHOSOKU_1' || $filename === 'SEIKYUHOSOKU_1')
			{
				if($container->pbStep == STEP_DELETE)
				{
					$executer = new DeleteHosokuExecuter($container);
				}
				else
				{
					$executer = new HosokuExecuter($container);
				}	
				
			}
                        else if($filename === 'USERCSVIMPORT_1')
                        {
                                $executer = new UserCsvImportExecute($container);
                        }
                        else if($filename === 'KOKYAKUCSVIMPORT_1')
                        {
                                $executer = new CustomerCsvImportExecute($container);
                        }
                        else if($filename === 'MITSUMORICSVIMPORT_1')
                        {
                                $executer = new MitsumoriCsvImportExecute($container);
                        }
                        
			if($executer !== null)
			{
				return $executer;
			}
			//--特殊処理--//
			
			$executer = new BaseLogicExecuter($container);
		}
		
		if($filename == 'RIREKIDEL_9' )
		{
			$executer = new DeleteRirekiExecuter($container);
		}

		if($filename == 'MITSUMORICOPY_9' || $filename == 'ANKENREUSE_9'|| $filename == 'SEIKYUCOPY_9' )
		{
			$executer = new CopyExecuter($container);
		}
		return $executer;
	}		
}
