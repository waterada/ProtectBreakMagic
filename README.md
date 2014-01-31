ProtectBreakMagic
=================

概要
----

ユニットテストの際に `protected` なメソッドやプロパティをすべて `public` に変えるための `CakePHP` プラグインです。
ユニットテスト上から生産性の無い継承を排除し、テストコードの可読性に高めます。


何が嬉しいの？
--------------

`protected` なメソッドにユニットテストを書きたい、
`protected` なプロパティにユニットテストでアクセスしたいという場合、
通常なら元クラスを継承して、
テストしたい `protected` なメソッドをオーバーライドして `public` に変えるという手法が一般的でした。

例) 従来の protected なメソッドを public に変えるための継承：

	class HogeMock extends Hoge {
	    /**
	     * protected なメソッドを public にする
	     */
	    public function getFugaById($id) {
	        return parent::getFugaById($id);
	    }
	}

しかしながら、この手法で書いた十数行のソースコードには何の生産性も無いもので、
時には数十もの無意味なオーバーライドが並び、
本来重要となるテストロジックをまぎれさせることもありました。
しかも、引数の変更などがあれば、この無意味なオーバーライドも併せてメンテしなければなりませんでした。

ですが、 `ProtectBreakMagic` を使えば１行ですべて完了です！
無駄な継承も無駄なオーバーライドも要りません。
今後、もしもユニットテストのコード上に継承が存在すればそれは、
何か意味のある処理がなされているに違いないのです！


使い方
------

テストクラスの冒頭で下記のように宣言します。

	App::uses('ProtectBreakMagic', 'ProtectBreakMagic.Lib/TestLib');

クラスのインスタンス（オブジェクト）を `new ProtectBreakMagic()` で囲みます。

	//元のコード
	$this->Hoge = new Hoge();

上記を下記のように変更してください

	//変更後のコード
	$this->Hoge = new ProtectBreakMagic(new Hoge());

以上で `$this->Hoge` は `Hoge` クラスのすべての `public/protected` なメソッド、プロパティにアクセスできるようになります。

	//本来なら protected でテストできないメソッドですが、`ProtectBreakMagic` を使っているのでテスト可能です。
	$actual = $this->Hoge->getFugaById(1);
	
	//protected なプロパティにもアクセス可能です。
	$this->assertEquals(1, $this->Hoge->_id);

なお、`private` なメソッド、プロパティにはこれを使ってもアクセスできません（できるべきではありません）。


インストール方法
----------------

(1) Plugin を配置する

`CakePHP` の `Plugin` ディレクトリの下に `ProtectBreakMagic` ディレクトリを置いてください。


(2) Plugin をロードする

`bootstrap.php` で `ProtectBreakMagic` を読み込みます。

	CakePlugin::load('ProtectBreakMagic');

以上でインストール完了です。


どうして `ProtectBreakMagic` という名前なの？
---------------------------------------------

`ProtectBreakMagic` は magic メソッドを使って `protected` を壊す（break）ことでこれを実現していますので、こういう名前にしています。
個人的な趣味で RPG の呪文っぽくしています。
