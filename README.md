
## 🚧 X-update module was merged into XCL branch module/xupdate !
https://github.com/xoopscube/xcl/tree/module/xupdate

## ⚠️ This repository is planned to be archived !

# X-update (GUI Free Store)

**X-update** is a module with a user-friendly GUI for updates from your XCL dashboard.
You can quickly install and update XCL itself, modules and themes without any FTP client.

With the introduction of X-update, site administrators just need to log-in to the admin site and browser to complete the installation or update of XCL, modules, themes, libraries, preloads (extenions), etc. X-update will download from distribution sites such as GitHub or GitLab repositories. X-update also supports the installation and update of D3 modules (clone and change the public name).

X-update ensures the latest code is always deploying for the benefit of all, webmasters and developers of modules and themes.


# X-update （えっくす・あっぷでーと）

アップデートモジュール「X-update」をXCL (Xoops Cube Legacy) 2.2 以降にインストールすることで、XCL本体や他のモジュールやテーマのインストール、アップデートをFTPクライアントを使わずに迅速に行うことが可能になります。

X-update導入により、サイト管理者は管理サイトにログインして管理画面のアップデータモジュール操作をブラウザ上で行うだけで、モジュールのgitHubなど配布サイトからのダウンロードからインストールやアップデート、テーマのインストールなどが完了します。
もちろん、d3形式などの複製対応モジュールも、インストールモジュール名（＝公開側のフォルダ名称）を変更してインストールすることができます。

このモジュールを使うメリットは、ユーザー側だけに留まりません。　モジュールやテーマ作者も、常に最新の公開リポジトリからxupdateが取得してくれることと、複数の公開先を持つことによる２重管理の手間からも開放されます。

## 何ができるの？
* FTPクライアントソフトにノータッチで、対応モジュールやテーマをインストールできます。
* 対応モジュールやテーマの最新版を、xupdateの管理画面操作だけで各公開サーバーからダウンロード取得し、解凍・展開・アップロードまでできますから、その後で通常のモジュールインストール操作を行うだけです。
* モジュールだけではなく、テーマやプリロードもストアサイトからダウンロード取得～アップロードまでできます。

## 動作要件
X-updateを使うためには、以下の環境が必須となります。

* PHP 5.1.0 以上
* PHP cURLエクステンション
* MySQL 5.0 以上
* Xoops Cube legacy 2.2.0 以上

# 使い方
## ダウンロード
本モジュールを同梱した、XOOPS X(ten) CorePackがありますので、サイトを始めから構築する場合はXOOPS Cube Legacy2.2のインストールから一括して可能なCorePackからの導入をお勧めします。
* https://github.com/XoopsX/legacy (CorePack)

## インストール
通常の、xoops_trust_path式の複製対応モジュールと同様です。
但し、次のディレクトリに書き込み(ファイル作成)権限 (777 とか 707 など) が必要です。管理画面で変更した場合には、変更したディレクトリに書き込み権限を与えて下さい。
* xoops_trust_path/uploads/xupdate

## 一般設定

* 圧縮ファイルダウンロード・展開用フォルダ
 * アーカイブをダウンロードして展開するために使用するフォルダ。(xoops_trust_path)配下のディレクトリを指定、最初と最後の"/"（スラッシュ）は含めません。通常は、uploads/xupdate　のままでご使用ください。
このフォルダには、707,777 などの書き込み権限を与えてください。

* 使用するFTPライブラリ
  * サーバーにファイル群をアップロードする際に使用するFTPライブラリの選択。カスタムFTP（標準）を選択して問題が解決できない場合、
PHP_FTP（FTP over SSL用）や、カスタムSSH_FTPをお試しください。
    * カスタムFTP（標準）
      * PHPにFTP関数が実装されていない場合は、まずはこのカスタムFTPを選択します。
    * PHP_FTP（FTPS用）
      * PHPにFTP関数が実装されている場合は、こちらも選択できます。　サーバーがFTPSでの転送をサポートする場合は、これを選択して、次項でSSL(FTPS)を設定します。
    * カスタムSSH_FTP
      * サーバーがSSHの使用をサポートしている場合、標準のカスタムFTPよりもこちらの方が高速に動作する場合があります。SSH接続のポート番号が必要です。
    * カスタムSSH2（鍵認証用）
      * サーバーがSSHの鍵認証をサポートしている場合、標準のカスタムFTPよりもこちらの方が高速に動作する場合があります。SSH接続のポート番号と、SSH鍵認証接続場合にprivate_keyが必要です。

* SSL(FTPS)を使用する
 * 前項で「PHP_FTP（FTPS用）」を指定した場合に、SSL(FTPS)が使用可能です。

* FTPログインID／FTPログインパスワード
 * ご使用のサーバーの、FTPログインIDとパスワードを入力します。

* SSH接続のポート
 * SSH_FTP接続、または鍵認証接続の場合にSSHポート番号を指定します。defaultは22。

* SSH鍵認証接続のprivate_key
 * SSH鍵認証接続場合にprivate_keyが必要です。セキュリティ上、private_keyをサーバーに保存しておくのは危険なので、作業開始前に入力し、作業完了後に消去しておくことを強くお勧めします。

* デバグ出力を表示する
 * 問題が解決しない場合は、ここを「はい」とすることで、追加デバグ情報が表示されます。

* テーマURL
 * CMS Theme Finder のURLです。通常は変更不要です。

* アドオンストアURL
 * アドオンストアデータ取得用の URL です。通常は変更不要です。

* 非表示にされたストアも表示する
 *アドオンストアで、非表示に設定されているストアも表示します。通常、非表示にされているストアには、開発者用のストアが含まれています。

* ストアデータ取得時の同時接続数
 * ストア一覧のデータを取得する場合の HTTP 同時接続数を設定します。環境によりデータ取得に失敗する場合はこの値を減らして調整します。（デフォルト： 50）

* curl_multi_select を使用しない 
 * 環境により同時接続数を 2 以上にするとデータ取得に失敗する場合があります。その場合に限り「はい」を選択してください。

* cURL: SSLホスト認証をしない
 * cURL 利用時に SSL接続時のサーバー認証がエラーになる場合に「はい」を選択してください。

# インストール操作
操作の流れに従っていればわかると思いますが、以下のサイトなどを参考にしてみてください。
* http://www.xugj.org/modules/xpwiki/?ModuleManuals%2FX-update
