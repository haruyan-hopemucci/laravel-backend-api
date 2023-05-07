# Laravel-backend-api

## 目的

### Laravelを使ったRESTfulAPIを作る。

私自身がほとんどLaravelを触ってことと、OAuth2に対する理解を深めるため。

### OAuth2による認可を実装し、認可したアカウントに対してCRUDが可能な何らかの機能を実装する。

- アカウントの新規作成。ユーザープロファイルが作成される。
- ログイン/ログアウト
- ユーザープロファイルの読み込み（自アカウントしかできない）
- ユーザープロファイルの変更（何らかのバリデーションも実装）
- ユーザーの削除（アカウントごと削除する）

## 参考ページ

https://www.twilio.com/ja/blog/build-secure-api-php-laravel-passport-jp

以降、詰まった差分のみ記載していく。

## 記載差分

### Laravel Passportのインストールと設定

```
$ composer require laravel/passport
```
実行時にエラー。Laravelインストール時に導入したモジュールとPassportのモジュールのバージョンミスマッチが起こっている模様。
エラーメッセージの指示通り、ミスマッチしているモジュールのアップデートも行う`-W`オプション込みで実行したら正常終了した。

```
$ composer require -W laravel/passport
```

で、マイグレーションを進めていくと、元バージョンのLaravelにはSanctumというSPA向け簡易認証モジュールがすでに組み込まれているらしい？
Passportと競合しないか？

どうも初期インストールファイルをチェックしてみるとすでにSanctumによる認証ができる状態になっているようなので、以降は以下の記事を参考にSanctumによる認証をやってみる。

Passportによる認証はLaravelのバージョンを落とした状態で改めてやってみることにする。

https://www.twilio.com/ja/blog/build-restful-api-php-laravel-sanctum-jp

## 記載差分

### APIを作成する

`register`apiを実装する記事で、`app/Http/Controllers/AuthController.php`に記載するuseに不足がある。

```php
use App\Models\User;
```

と思ったら次の`login`apiの実装に記載されていた。register関数でUserが使用されているのに、これはちょっとよくない。

最後、`APIをテストする前に、app/Providers/RouteServiceProvider.phpの以下のコメントを解除してください。`という内容は、当該ファイルにはそのような記載がなかったのでパス。

### APIをテストする

試しに/registerしてみたら、Internal Server Errorが帰ってきた。

どうやらSQLiteのデータベースファイル名は絶対パスで.envに書かないといけないらしい。

絶対パスに書き直したらステータス200とアクセストークンが返ってきた。

/api/meにGETでアクセスしたら、Method Not Allowedが返ってきた。何でや。

routes/api.phpに/api/meがpostで定義されていたので、getに書き換える。この場合はgetの方が適切だと思われる。

```php
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
```

これでBearer入りのリクエストが期待通り取得できた。

ただし、不正なBearerをセットしたヘッダでリクエストすると、Authorication Requred ではなくroute[login]が見つからないというエラーが返ってくる。認証が必要なエンドポイントを非認証状態で叩くと`/login`にリダイレクトされるようにSanctumのデフォルトが設定しているらしい。ちょっとやだな。

routes/web.phpでloginエンドポイントを作って別ページに飛ばす実装を紹介している記事があった。

https://saunabouya.com/2022/02/04/laravel8-sanctum-login-not-defined/

当面はこれで行くけど、本来は401を返したいね。

401を返す方法は、エラーハンドリングを行う`app/Exceptions/Handler.php`の認証に関するメソッドをオーバーライドする方法がある。こちらの方がスマートか。

https://qiita.com/fuubit/items/fea41e173fa1bdf70736
