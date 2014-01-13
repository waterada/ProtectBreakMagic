<?php
# //26.
# require_once(dirname(__FILE__).'/ProtectBreakMagic.php'); //これはうまくいく

# //28.
# require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Lib/ProtectBreakMagic.php'); //これはうまくいかない。
# debug(dirname(dirname(dirname(dirname(__FILE__)))).'/Lib/ProtectBreakMagic.php'); // /home/travis/build/waterada/cakephp/app/Lib/ProtectBreakMagic.php このパスは正しい

//なぜ自身と同じ場所にあればOKで、離れているとNG?

# //29. １段階だけあげたところに置いてみる
# require_once(dirname(dirname(__FILE__)).'/ProtectBreakMagic.php'); //これはうまくいく
# debug(dirname(dirname(__FILE__)).'/ProtectBreakMagic.php'); // /home/travis/build/waterada/cakephp/app/Test/Case/ProtectBreakMagic.php

//30. ３段階だけあげたところに置いてみる
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/ProtectBreakMagic.php');
debug(dirname(dirname(dirname(dirname(__FILE__)))).'/ProtectBreakMagic.php');



//App::uses('ProtectBreakMagic', 'Lib');
//App::load('ProtectBreakMagic');

/**
 * Class ProtectBreakMagicTest
 *
 * todo: trait
 *
 *
 * @property object $Mock  //You can hide warnings about protected accesses on PHPStorm when you define it as 'object' type.
 * @property object $MockExtend
 */
class ProtectBreakMagicTest extends CakeTestCase {

    public function provideForMocks() {
        return array(
            array(new ProtectBreakMagic(new MockProtectBreakMagic()), "通常のパターン"),
            array(new ProtectBreakMagic(new MockProtectBreakMagic_extend()), "すべて継承元を参照するパターン"),
            array(new ProtectBreakMagic(new MockProtectBreakMagic_magic()), "magicメソッド持ってるパターン"),
        );
    }

    /**
     * procated なプロパティにアクセスできる
     *
     * @dataProvider provideForMocks
     * @param object $mock
     * @param string $message
     */
    public function test_can_access_protected_property($mock, $message) {
        $this->assertTrue(property_exists($mock->object, "protected_property"), $message); //createしてないことを確定させるため、念のために存在チェック
        $mock->protected_property = "protected";
        $this->assertEquals("protected", $mock->protected_property, $message);
    }

    /**
     * procated なメソッドにアクセスできる
     *
     * @dataProvider provideForMocks
     * @param object $mock
     * @param string $message
     */
    public function test_can_call_protected_method($mock, $message) {
        $this->assertEquals("+#-protected", $mock->protectedMethod("protected"), $message);
    }

    /**
     * public なプロパティにアクセスできる
     *
     * @dataProvider provideForMocks
     * @param object $mock
     * @param string $message
     */
    public function test_can_access_public_property($mock, $message) {
        $this->assertTrue(property_exists($mock->object, "public_property"), $message); //createしてないことを確定させるため、念のために存在チェック
        $mock->public_property = "public";
        $this->assertEquals("public", $mock->public_property, $message);
    }

    /**
     * public なメソッドにアクセスできる
     *
     * @dataProvider provideForMocks
     * @param object $mock
     * @param string $message
     */
    public function test_can_call_public_method($mock, $message) {
        $this->assertEquals("+#-public", $mock->publicMethod("public"), $message);
    }

    /**
     * 存在しないプロパティを新規に作成できる
     *
     * @dataProvider provideForMocks
     * @param object $mock
     * @param string $message
     */
    public function test_can_create_new_property($mock, $message) {
        $this->assertFalse(property_exists($mock->object, "new_property"), $message); //createすることを確定させるため、念のために存在チェック
        $mock->protected_property = "protected";
        $mock->public_property = "public";
        $mock->new_property = "new";
        $this->assertEquals("protected", $mock->protected_property, $message); //no effect
        $this->assertEquals("public", $mock->public_property, $message); //no effect
        $this->assertEquals("new", $mock->new_property, $message);
    }

    /**
     * private なプロパティにセットできない
     *
     * @expectedException LogicException
     * @expectedExceptionMessage should not set-access
     */
    public function test_cannot_set_private_property() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic());
        $mock->private_property = "private";
    }

    /**
     * private なプロパティをゲットできない
     *
     * @expectedException LogicException
     * @expectedExceptionMessage should not get-access
     */
    public function test_cannot_get_private_property() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic());
        $this->assertEquals(null, $mock->private_property);
    }

    /**
     * private なメソッドにアクセスできない
     *
     * @expectedException LogicException
     * @expectedExceptionMessage should not call
     */
    public function test_cannot_call_private_method() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic());
        $mock->privateMethod("private");
    }

    /**
     * 親クラスで private だったが子クラスで protected になっていてもアクセスできる
     */
    public function test_can_access_even_if_changing_to_protected() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic_toProtected());

        $this->assertTrue(property_exists($mock->object, "private_property")); //createしてないことを確定させるため、念のために存在チェック
        $mock->private_property = "to_protected";
        $this->assertEquals("to_protected", $mock->private_property);

        $this->assertEquals("++##--to_protected", $mock->privateMethod("to_protected"));
    }

    /**
     * 子クラスで override されているプロパティでも親クラスのメソッドから参照される
     */
    public function test_can_be_accessed_even_if_overridden_property() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic_overrideProperty());
        $mock->protected_property = "overridden_property";
        $this->assertEquals("overridden_property", $mock->getProtectedProperty());
    }

    /**
     * magicメソッドを実装している場合でも正常に呼ばれる
     */
    public function test_magic_methods_is_called__set() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic_magic());
        $mock->public_property = "public";
        $mock->protected_property = "protected";

        $this->assertEquals(0, $mock->counts['__set']);
        $mock->new_property = "new";
        $this->assertEquals(1, $mock->counts['__set']);
    }

    public function test_magic_methods_is_called__get() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic_magic());

        $this->assertSame('', $mock->public_property);
        $this->assertSame('', $mock->protected_property);

        $this->assertEquals(0, $mock->counts['__get']);
        $this->assertSame(null, $mock->new_property);
        $this->assertEquals(1, $mock->counts['__get']);
    }

    public function test_magic_methods_is_called__call() {
        /** @var object $mock */
        $mock = new ProtectBreakMagic(new MockProtectBreakMagic_magic());
        $this->assertEquals("+#-public", $mock->publicMethod("public"));
        $this->assertEquals("+#-protected", $mock->protectedMethod("protected"));

        $this->assertEquals(0, $mock->counts['__call']);
        $this->assertEquals("new", $mock->newMethod("new"));
        $this->assertEquals(1, $mock->counts['__call']);
    }
}


class MockProtectBreakMagic {
    public $public_property = "";
    protected $protected_property = "";
    private $private_property = "";

    public function publicMethod($arg) {
        return $this->privateMethod($arg);
    }

    protected function protectedMethod($arg) {
        return $this->privateMethod($arg);
    }

    private function privateMethod($arg) {
        $this->public_property = "+";
        $this->protected_property = "#";
        $this->private_property = "-";
        return $this->public_property . $this->protected_property . $this->private_property . $arg;
    }

    public function getProtectedProperty() {
        return $this->protected_property;
    }
}

/**
 * すべてを継承
 */
class MockProtectBreakMagic_extend extends MockProtectBreakMagic {
}

/**
 * 同じ名前で protected になっている
 */
class MockProtectBreakMagic_toProtected extends MockProtectBreakMagic {
    protected $private_property = "";

    protected function privateMethod($arg) {
        $this->public_property = "++";
        $this->protected_property = "##";
        $this->private_property = "--";
        return $this->public_property . $this->protected_property . $this->private_property . $arg;
    }
}

/**
 * プロパティをoverride
 */
class MockProtectBreakMagic_overrideProperty extends MockProtectBreakMagic {
    protected $protected_property = "";
}

/**
 * すべてを継承
 */
class MockProtectBreakMagic_magic extends MockProtectBreakMagic {
    public $counts = array(
        '__call' => 0,
        '__get'  => 0,
        '__set'  => 0,
    );

    public function __call($method, $arguments) {
        $this->counts['__call']++;
        return $arguments[0];
    }

    public function __get($name) {
        $this->counts['__get']++;
        return null;
    }

    public function __set($name, $value) {
        $this->counts['__set']++;
        $this->$name = $value;
    }
}
