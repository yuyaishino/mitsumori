<?php

   //ファイル出力
    $name = $_GET['filename'];
   $fileName = "./file/$name";
   //$fileName = "見積修正.xlsx";
   $fileName = mb_convert_encoding($fileName, "SJIS", "UTF-8");
   //$fileName = "sample.xlsx";
   /* ファイルの存在確認 */
    if (!file_exists($fileName)) {
        die("Error: File(".$fileName.") does not exist");
    }
    /* オープンできるか確認 */
    if (!($fp = fopen($fileName, "r"))) {
        die("Error: Cannot open the file(".$fileName.")");
    }
    fclose($fp);
    /* ファイルサイズの確認 */
    if (($content_length = filesize($fileName)) == 0) {
        die("Error: File size is 0.(".$fileName.")");
    }
    // ダウンロード開始
    header('Content-Type: application/octet-stream');
    // ここで渡されるファイルがダウンロード時のファイル名になる
    header('Content-Disposition: attachment; filename='.$name.''); 
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($fileName));
    //ob_end_clean();
    //ob_clean();
    readfile($fileName);
    return;
 
?>